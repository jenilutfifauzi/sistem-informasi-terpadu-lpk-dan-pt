# Phase 0: Research - CTK Index Action Buttons

**Feature**: CTK Index Action Buttons  
**Date**: February 15, 2026  
**Purpose**: Research technical approaches for implementing table actions with role-based visibility and modal forms in Filament 4

## Research Areas

### 1. Filament 4 Table Actions

**Decision**: Use `Filament\Tables\Actions\Action` for table row actions  
**Rationale**: Filament 4 unified all actions under `Filament\Actions\Action` (not `Filament\Tables\Actions\Action` from v3). Table actions are defined in the `->actions()` method of the table configuration.

**Implementation Pattern**:
```php
use Filament\Actions\ViewAction;
use Filament\Actions\Action;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([
            ViewAction::make()
                ->url(fn ($record) => static::getUrl('view', ['record' => $record])),
            Action::make('kelola_progress')
                ->label('Kelola Progress')
                ->icon('heroicon-o-arrow-path')
                ->modalHeading('Kelola Progress CTK')
                ->form([...])
                ->action(function ($record, array $data) {
                    // Update logic
                })
                ->visible(fn ($record) => /* role check */),
        ]);
}
```

**Key Findings**:
- `ViewAction::make()` is a built-in action that navigates to the view page
- Custom actions use `Action::make('name')` with `->form()` and `->action()` callbacks
- `->visible()` callback receives the record and can check user roles/permissions
- `->modalHeading()`, `->modalSubmitActionLabel()` customize modal appearance
- Actions automatically refresh table after execution when using Livewire (no manual refresh needed)

**Alternatives Considered**:
- Custom action classes extending `Action` - More maintainable for complex logic but overkill for simple modal forms
- Separate page for progress management - Adds unnecessary navigation; rejected in favor of inline modal

---

### 2. Role-Based Action Visibility

**Decision**: Use `->visible()` callback with role and entity checks  
**Rationale**: Filament actions support `visible(fn ($record) => bool)` callback that can access current user, record state, and perform authorization checks.

**Implementation Pattern**:
```php
use Illuminate\Support\Facades\Auth;

Action::make('kelola_progress')
    ->visible(function ($record) {
        $user = Auth::user();
        
        // Super Admin sees all
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Pimpinan is read-only (no Kelola Progress)
        if ($user->hasRole('Pimpinan')) {
            return false;
        }
        
        // Admin LPK only for LPK stages (1-5)
        if ($user->hasRole('Admin LPK') && $user->entity === EntityType::LPK) {
            return $record->current_entity === EntityType::LPK 
                && $record->current_stage >= 1 
                && $record->current_stage <= 5;
        }
        
        // Admin PT, HR PT, Legal PT, Keuangan PT for PT stages (6-15)
        if ($user->hasAnyRole(['Admin PT', 'HR PT', 'Legal PT', 'Keuangan PT']) 
            && $user->entity === EntityType::PT) {
            return $record->current_entity === EntityType::PT 
                && $record->current_stage >= 6 
                && $record->current_stage <= 15;
        }
        
        return false;
    }),
```

**Key Findings**:
- `visible()` callback runs on each row, so keep logic performant
- Can access `$record` (CTK model), `$user` via Auth facade
- Mirrors existing `getEloquentQuery()` authorization logic for consistency
- Hidden actions don't render in HTML (security via obscurity + server-side validation)

**Alternatives Considered**:
- Policy-based authorization with `->authorize()` - More "Laravel-like" but requires CTK policy updates; `visible()` sufficient for UI hiding
- Check in action callback - Too late (shows button to unauthorized users); rejected

---

### 3. Modal Actions with Form Components

**Decision**: Use `->form([])` array with Filament form components  
**Rationale**: Filament actions support modal forms with full form component ecosystem (Select, TextInput, DatePicker, etc.). Forms auto-validate and pass data to `->action()` callback.

**Implementation Pattern**:
```php
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use App\Enums\CTKStatus;

Action::make('kelola_progress')
    ->form([
        Select::make('current_stage')
            ->label('Tahap Saat Ini')
            ->options([
                1 => 'MCU',
                2 => 'Pembayaran Tahap 1',
                3 => 'Soal/Berkas',
                // ... all 15 stages
            ])
            ->default(fn ($record) => $record->current_stage)
            ->required(),
        Select::make('mcu_status')
            ->label('Status MCU')
            ->options(MCUStatus::class)
            ->visible(fn (Get $get) => $get('current_stage') === 1)
            ->required(fn (Get $get) => $get('current_stage') === 1),
        Textarea::make('notes')
            ->label('Catatan')
            ->rows(3),
    ])
    ->action(function (CTK $record, array $data) {
        // Validate business rules
        if (!$record->canAdvanceToStage($data['current_stage'])) {
            Notification::make()
                ->danger()
                ->title('Tidak dapat memajukan tahap')
                ->body('Prerequisit belum terpenuhi')
                ->send();
            return;
        }
        
        // Update record
        $record->update([
            'current_stage' => $data['current_stage'],
            'mcu_status' => $data['mcu_status'] ?? null,
        ]);
        
        // Activity log (using Spatie)
        activity()
            ->performedOn($record)
            ->causedBy(Auth::user())
            ->withProperties(['old_stage' => $record->getOriginal('current_stage'), 'new_stage' => $data['current_stage']])
            ->log('Updated CTK stage');
        
        Notification::make()
            ->success()
            ->title('Progress berhasil diperbarui')
            ->send();
    }),
```

**Key Findings**:
- Form components support reactive `->visible()` based on other field values using `Get` closure
- `->action()` callback receives `$record` (model) and `$data` (validated form data)
- Filament notifications (`Notification::make()`) provide user feedback
- Form validation happens automatically before `->action()` executes
- Activity logging uses existing Spatie Activity Log package

**Alternatives Considered**:
- Separate FormRequest class - Overkill for action validation; Filament form rules sufficient
- Custom Livewire component - More complex; Filament modal actions handle 90% of use cases

---

### 4. Bulk Actions

**Decision**: Use `->bulkActions()` with `BulkAction::make()`  
**Rationale**: Filament provides built-in bulk action support with selection checkboxes and collection processing.

**Implementation Pattern**:
```php
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([...])
        ->bulkActions([
            BulkAction::make('bulk_kelola_progress')
                ->label('Kelola Progress')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('current_stage')
                        ->label('Tahap Baru')
                        ->options([...])
                        ->required(),
                ])
                ->action(function (Collection $records, array $data) {
                    $successCount = 0;
                    $failureCount = 0;
                    
                    foreach ($records as $record) {
                        if ($record->canAdvanceToStage($data['current_stage'])) {
                            $record->update(['current_stage' => $data['current_stage']]);
                            activity()->performedOn($record)->log('Bulk updated stage');
                            $successCount++;
                        } else {
                            $failureCount++;
                        }
                    }
                    
                    Notification::make()
                        ->success()
                        ->title("Berhasil: {$successCount} CTK diperbarui")
                        ->body($failureCount > 0 ? "Gagal: {$failureCount} CTK (prerequisit tidak terpenuhi)" : null)
                        ->send();
                })
                ->deselectRecordsAfterCompletion(),
        ]);
}
```

**Key Findings**:
- Bulk actions receive `Collection $records` instead of single `$record`
- Validation should be per-record since different CTKs may have different prerequisites
- `->deselectRecordsAfterCompletion()` clears selection after action
- Bulk actions respect `getEloquentQuery()` scoping (users only see CTKs they can access)

**Alternatives Considered**:
- All-or-nothing validation - Rejected; some CTKs may be ready to advance while others aren't
- Separate confirmation step - Could add but makes simple updates cumbersome

---

### 5. Table Refresh After Updates

**Decision**: No manual refresh needed; Livewire auto-updates  
**Rationale**: Filament tables are Livewire components. After action completes, Livewire automatically re-renders the table showing updated data.

**Key Findings**:
- Table columns that display `current_stage`, `completion_progress`, etc. will automatically show new values after action
- No need for `$this->dispatch('refresh')` or manual wire:key manipulation
- Notifications provide immediate user feedback while table refreshes in background
- If using custom Livewire components within actions, ensure they emit events for coordination

**Alternatives Considered**:
- Manual table refresh via JS/Alpine - Unnecessary; Livewire handles it
- Full page reload - Poor UX; rejected in favor of Livewire reactivity

---

## Best Practices for This Feature

### Testing Strategy

Per DATABASE_SAFETY.md, **DO NOT use RefreshDatabase trait**. Use database transactions:

```php
use Illuminate\Support\Facades\DB;

class CTKTableActionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        DB::rollBack();
        parent::tearDown();
    }

    /** @test */
    public function admin_lpk_can_see_kelola_progress_for_lpk_ctks()
    {
        $user = User::factory()->create(['entity' => EntityType::LPK]);
        $user->assignRole('Admin LPK');
        
        $ctk = CTK::factory()->create([
            'current_entity' => EntityType::LPK,
            'current_stage' => 3,
        ]);
        
        Livewire::actingAs($user)
            ->test(ListCTKS::class)
            ->assertTableActionVisible('kelola_progress', $ctk);
    }
}
```

### Authorization Pattern

Reuse existing authorization logic from `CTKResource::getEloquentQuery()`:
- Admin LPK: LPK stages 1-5
- Admin PT / HR PT / Legal PT / Keuangan PT: PT stages 6-15
- Pimpinan: View only (no Kelola Progress)
- Super Admin: All stages, all entities

### Activity Logging

Use Spatie Activity Log (already in composer.json):
```php
activity()
    ->performedOn($record)
    ->causedBy(Auth::user())
    ->withProperties([
        'old_stage' => $oldStage,
        'new_stage' => $newStage,
        'notes' => $data['notes'] ?? null,
    ])
    ->log('CTK stage updated via Kelola Progress action');
```

### Stage Validation

Implement business rule validation in CTK model:
```php
// app/Models/CTK.php
public function canAdvanceToStage(int $targetStage): bool
{
    // Example rules:
    // - Cannot skip stages
    // - MCU must be FIT before stage 2
    // - Payments must be complete before final stages
    // - Final stage (Terbang) is immutable
    
    if ($this->current_stage === 15) {
        return false; // Terbang is final, immutable
    }
    
    if ($targetStage < $this->current_stage) {
        return false; // Cannot go backward
    }
    
    if ($targetStage > $this->current_stage + 1) {
        return false; // Cannot skip stages
    }
    
    // Stage-specific validation...
    
    return true;
}
```

---

## Summary

**Recommended Implementation**:
1. Modify `app/Filament/Resources/CTKS/Tables/CTKSTable.php::configure()` to add `->actions()` array
2. Add `ViewAction::make()` for all users
3. Add `Action::make('kelola_progress')` with role-based `->visible()` callback
4. Create form components for stage selection and stage-specific fields (MCU status, etc.)
5. Implement `->action()` callback with business rule validation via `CTK::canAdvanceToStage()`
6. Log updates using Spatie Activity Log
7. Add bulk action `BulkAction::make('bulk_kelola_progress')` for P3 priority
8. Write PHPUnit tests with database transactions (NOT RefreshDatabase)

**No new dependencies required** - all functionality provided by existing Filament 4 + Livewire 3 + Spatie Activity Log.

**Estimated complexity**: Medium - leverages existing Filament patterns, no custom Livewire components needed.
