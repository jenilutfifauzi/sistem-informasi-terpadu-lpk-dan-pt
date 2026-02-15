# Quickstart Guide: CTK Index Action Buttons

**Feature**: 006-ctk-view-actions  
**Target**: Developers implementing or testing this feature  
**Prerequisites**: Local SIT_LPK development environment running

## Development Setup

### 1. Branch & Dependencies

```bash
# Ensure you're on the feature branch
git checkout 006-ctk-view-actions

# No new dependencies - using existing Filament 4 + Livewire 3
# Verify dependencies are up to date
composer install
npm install
```

### 2. Database Setup

**No migrations required** - this feature uses existing schema.

Ensure you have test data:
```bash
# Seed database if empty
php artisan db:seed

# If you need fresh test data for CTKs
php artisan db:seed --class=CTKSeeder
```

### 3. Test User Setup

Create test users with different roles:

```bash
# Via Tinker
php artisan tinker

# Create Admin LPK user
$adminLpk = User::create([
    'name' => 'Admin LPK Test',
    'email' => 'admin.lpk@test.com',
    'password' => bcrypt('password'),
    'entity' => App\Enums\EntityType::LPK,
]);
$adminLpk->assignRole('Admin LPK');

# Create Admin PT user
$adminPt = User::create([
    'name' => 'Admin PT Test',
    'email' => 'admin.pt@test.com',
    'password' => bcrypt('password'),
    'entity' => App\Enums\EntityType::PT,
]);
$adminPt->assignRole('Admin PT');

# Create Pimpinan user (read-only)
$pimpinan = User::create([
    'name' => 'Pimpinan Test',
    'email' => 'pimpinan@test.com',
    'password' => bcrypt('password'),
]);
$pimpinan->assignRole('Pimpinan');

exit
```

### 4. Development Server

```bash
# Start Laravel dev server
php artisan serve

# In another terminal, build assets (or watch)
npm run dev
# OR for production build:
# npm run build
```

Access admin panel: http://localhost:8000/admin

---

## Feature Testing Guide

### Manual Testing Checklist

#### Test 1: View Action Visibility
1. Login as any role
2. Navigate to http://localhost:8000/admin/c-t-k-s
3. ✅ **Expected**: Each CTK row shows a "View" button (eye icon)
4. Click "View" on any row
5. ✅ **Expected**: Navigate to CTK detail page showing full information

---

#### Test 2: Kelola Progress - Admin LPK
1. Login as `admin.lpk@test.com` / `password`
2. Navigate to CTK index
3. Find a CTK with `current_entity=LPK` and `current_stage` between 1-5
4. ✅ **Expected**: "Kelola Progress" button is visible
5. Find a CTK with `current_entity=PT` (stages 6-15)
6. ✅ **Expected**: "Kelola Progress" button is HIDDEN (no permission)
7. Click "Kelola Progress" on LPK CTK
8. ✅ **Expected**: Modal opens with stage selection form
9. Select next stage (e.g., 1 → 2)
10. Fill required fields (e.g., MCU status = FIT for stage 1)
11. Submit
12. ✅ **Expected**: 
    - Success notification appears
    - Modal closes
    - Table auto-refreshes
    - CTK now shows updated stage and progress

---

#### Test 3: Kelola Progress - Admin PT
1. Login as `admin.pt@test.com` / `password`
2. Navigate to CTK index
3. Find a CTK with `current_entity=PT` and `current_stage` between 6-15
4. ✅ **Expected**: "Kelola Progress" button is visible
5. Find a CTK with `current_entity=LPK` (stages 1-5)
6. ✅ **Expected**: "Kelola Progress" button is HIDDEN (different entity)
7. Click "Kelola Progress" on PT CTK
8. Select next stage
9. Submit
10. ✅ **Expected**: Update succeeds and table refreshes

---

#### Test 4: Pimpinan (Read-Only)
1. Login as `pimpinan@test.com` / `password`
2. Navigate to CTK index
3. Check any CTK row
4. ✅ **Expected**: 
    - "View" button is visible
    - "Kelola Progress" button is HIDDEN (read-only role)
5. Click "View"
6. ✅ **Expected**: Can view details but no edit actions available

---

#### Test 5: Business Rule Validation
1. Login as Admin LPK
2. Find CTK at stage 1 (MCU) with `mcu_status=UNFIT`
3. Click "Kelola Progress"
4. Try to advance to stage 2
5. ✅ **Expected**: Error notification - "Prerequisit belum terpenuhi" (MCU must be FIT)

6. Find CTK at stage 15 (Terbang - final stage)
7. ✅ **Expected**: "Kelola Progress" button is HIDDEN or disabled (immutable)

8. Find CTK at stage 5 (Belajar LPK)
9. Try to select stage 7 (skip stage 6)
10. ✅ **Expected**: Error notification - cannot skip stages

---

#### Test 6: Activity Logging
1. Update any CTK stage via "Kelola Progress"
2. Query activity log:
```bash
php artisan tinker

# Check latest activity for a specific CTK
$ctk = App\Models\CTK::find(1);
$ctk->activities()->latest()->first();

# Should show:
# - description: "CTK stage updated via Kelola Progress action"
# - properties: {"old_stage": 2, "new_stage": 3}
# - causer: User who made the change
# - created_at: Timestamp

exit
```
3. ✅ **Expected**: Activity log entry exists with correct data

---

#### Test 7: Bulk Actions (Priority P3 - Optional)
1. Login as Admin LPK
2. Navigate to CTK index filtered to LPK CTKs
3. Select multiple CTK checkboxes (2-3 CTKs at same stage)
4. ✅ **Expected**: Bulk actions toolbar appears at top of table
5. Click "Kelola Progress" bulk action
6. ✅ **Expected**: Modal opens
7. Select new stage
8. Submit
9. ✅ **Expected**: 
    - All selected CTKs update (if validation passes)
    - Notification shows "X CTK berhasil diperbarui"
    - Table refreshes showing updated CTKs

---

### Automated Testing

Run PHPUnit tests:

```bash
# Run all tests
php artisan test --compact

# Run only CTK action tests
php artisan test --filter CTKTableActionsTest
php artisan test --filter CTKProgressManagementTest
```

**Expected test coverage**:
- ✅ Admin LPK can see Kelola Progress for LPK CTKs
- ✅ Admin LPK cannot see Kelola Progress for PT CTKs
- ✅ Admin PT can see Kelola Progress for PT CTKs
- ✅ Admin PT cannot see Kelola Progress for LPK CTKs
- ✅ Pimpinan can see View but not Kelola Progress
- ✅ Super Admin can see all actions for all CTKs
- ✅ Updating CTK stage creates activity log entry
- ✅ Cannot advance to final stage (Terbang) from non-final stages
- ✅ Cannot update CTK already in final stage
- ✅ Cannot skip stages (e.g., 3 → 5)
- ✅ Business validation prevents advancement without prerequisites

---

## Code Implementation Order

Follow this sequence for development:

### Step 1: Add View Action (Easiest, P1)
**File**: `app/Filament/Resources/CTKS/Tables/CTKSTable.php`

```php
use Filament\Actions\ViewAction;

public static function configure(Table $table): Table
{
    return $table
        ->columns([...])
        ->actions([
            ViewAction::make()
                ->url(fn ($record) => CTKResource::getUrl('view', ['record' => $record])),
        ])
        // ... existing filters, etc.
}
```

**Test**: Verify "View" button appears and navigates correctly.

---

### Step 2: Add Business Validation Method (P1 prerequisite)
**File**: `app/Models/CTK.php`

```php
public function canAdvanceToStage(int $targetStage): bool
{
    // See data-model.md for full implementation
    if ($this->current_stage === 15) {
        return false; // Immutable final stage
    }
    
    if ($targetStage <= $this->current_stage || $targetStage > $this->current_stage + 1) {
        return false; // Cannot go backward or skip
    }
    
    // Stage-specific validation (start simple, expand later)
    return true;
}
```

**Test**: Unit tests for validation logic.

---

### Step 3: Add Kelola Progress Action (Core, P1)
**File**: `app/Filament/Resources/CTKS/Tables/CTKSTable.php`

```php
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

->actions([
    ViewAction::make()->url(...),
    
    Action::make('kelola_progress')
        ->label('Kelola Progress')
        ->icon('heroicon-o-arrow-path')
        ->modalHeading('Kelola Progress CTK')
        ->form([
            Select::make('current_stage')
                ->label('Tahap')
                ->options([
                    1 => 'MCU',
                    2 => 'Pembayaran 1',
                    // ... all 15 stages
                ])
                ->default(fn ($record) => $record->current_stage)
                ->required(),
        ])
        ->action(function (CTK $record, array $data) {
            if (!$record->canAdvanceToStage($data['current_stage'])) {
                Notification::make()
                    ->danger()
                    ->title('Gagal')
                    ->body('Prerequisit belum terpenuhi')
                    ->send();
                return;
            }
            
            $oldStage = $record->current_stage;
            $record->update(['current_stage' => $data['current_stage']]);
            
            activity()
                ->performedOn($record)
                ->causedBy(Auth::user())
                ->withProperties(['old_stage' => $oldStage, 'new_stage' => $data['current_stage']])
                ->log('CTK stage updated');
            
            Notification::make()
                ->success()
                ->title('Berhasil')
                ->body('Progress CTK diperbarui')
                ->send();
        })
        ->visible(function ($record) {
            $user = Auth::user();
            
            if ($user->hasRole('super_admin')) return true;
            if ($user->hasRole('Pimpinan')) return false;
            
            if ($user->hasRole('Admin LPK') && $user->entity === EntityType::LPK) {
                return $record->current_entity === EntityType::LPK 
                    && $record->current_stage >= 1 
                    && $record->current_stage <= 5;
            }
            
            if ($user->hasAnyRole(['Admin PT', 'HR PT', 'Legal PT', 'Keuangan PT']) 
                && $user->entity === EntityType::PT) {
                return $record->current_entity === EntityType::PT 
                    && $record->current_stage >= 6 
                    && $record->current_stage <= 15;
            }
            
            return false;
        }),
])
```

**Test**: Livewire tests for action visibility and execution.

---

### Step 4: Write PHPUnit Tests (Mandatory, P1)
**File**: `tests/Feature/CTKTableActionsTest.php`

See research.md for test patterns using database transactions.

---

### Step 5: Add Bulk Actions (Optional, P3)
**File**: Same as Step 3, add to `->bulkActions()` array

---

## Common Issues & Solutions

### Issue: "Action not visible"
**Solution**: Check user role, CTK entity, CTK stage. Use `dd($user->roles, $record->current_entity)` in `visible()` callback.

### Issue: "Table not refreshing after update"
**Solution**: Ensure action uses `Action::make()` (not custom Livewire component). Livewire auto-refresh should work. Check browser console for JS errors.

### Issue: "Activity log not created"
**Solution**: Verify Spatie Activity Log is configured. Check `config/activitylog.php` and ensure model uses `LogsActivity` trait (CTK should already have this).

### Issue: "Tests failing with permission denied"
**Solution**: Ensure test user has correct role assigned via `assignRole()` before testing actions.

### Issue: "Cannot advance stage"
**Solution**: Check `canAdvanceToStage()` validation. Add temporary `logger()->info()` to see which validation rule is failing.

---

## Performance Notes

- Action visibility checks run on **every row** - keep `visible()` logic fast (no N+1 queries)
- Table already includes status, progress columns - actions don't add query overhead
- Activity logging is async-capable (can queue if needed for high volume) - start synchronous, optimize if needed

---

## Ready for Implementation

After following this quickstart:
1. ✅ Development environment running
2. ✅ Test users created
3. ✅ Manual test scenarios understood
4. ✅ Implementation order clear
5. ✅ Tests written and passing

**Next**: Run `/speckit.tasks` to generate detailed task breakdown for implementation.

---

## Support Resources

- **Filament Docs**: https://filamentphp.com/docs/4.x/actions/overview
- **Livewire Docs**: https://livewire.laravel.com/docs/
- **Spatie Activity Log**: https://spatie.be/docs/laravel-activitylog/
- **Project Constitution**: `.specify/memory/constitution.md`
- **Laravel Boost Guidelines**: `.github/copilot-instructions.md`
