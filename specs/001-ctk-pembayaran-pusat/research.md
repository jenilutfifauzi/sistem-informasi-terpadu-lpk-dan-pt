# Research: Pembayaran ke Pusat

**Feature**: 001-ctk-pembayaran-pusat  
**Date**: 2026-04-05

## Summary

No NEEDS CLARIFICATION items - all technical context is clear from existing codebase patterns.

## Pattern Analysis

### 1. Entity Scope Implementation

**Decision**: Use global scope pattern from Asset model

**Rationale**: 
- Proven pattern already working in production
- Automatic filtering by user's entity
- Pimpinan role bypass built-in
- Console command safety (skip scope in artisan)

**Code Reference**: `app/Models/Asset.php` lines 24-42

```php
protected static function booted(): void
{
    static::addGlobalScope('entity', function (Builder $builder) {
        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }
        $user = Auth::user();
        if ($user && isset($user->entity) && ! $user->hasRole('Pimpinan')) {
            $builder->where('entity', $user->entity);
        }
    });
}
```

### 2. File Upload Implementation

**Decision**: Use Filament FileUpload with public disk

**Rationale**:
- CTKPayment already uses this pattern for payment_proof_path
- Public disk enables direct URL access for preview
- Built-in validation for file types and size

**Code Reference**: `app/Filament/Resources/CTKS/Schemas/PaymentSection.php`

```php
FileUpload::make('payment_proof_path')
    ->label('Bukti Pembayaran')
    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
    ->maxSize(10240) // 10MB
    ->disk('public')
    ->directory('payment-proofs')
    ->downloadable()
    ->previewable()
```

### 3. Audit Logging

**Decision**: Use Spatie Activity Log trait

**Rationale**:
- Already configured in project
- Automatic logging of all changes
- Consistent with Asset model pattern

**Code Reference**: `app/Models/Asset.php`

```php
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

public function getActivitylogOptions(): LogOptions
{
    return LogOptions::defaults()
        ->logAll()
        ->logOnlyDirty()
        ->dontSubmitEmptyLogs();
}
```

### 4. Filament Resource Structure

**Decision**: Follow Asset resource organization

**Rationale**:
- Established pattern in codebase
- Separation of concerns (Pages/Schemas/Tables/Widgets)
- Reusable form and table configurations

**Directory Pattern**:
```
app/Filament/Resources/PembayaranPusat/
├── PembayaranPusatResource.php
├── Pages/
├── Schemas/
├── Tables/
└── Widgets/
```

### 5. Navigation Ordering

**Decision**: Place after ASET menu using `navigationSort`

**Rationale**:
- Spec requirement: "Menu ditempatkan setelah menu ASET"
- Use Filament's `$navigationSort` property

**Implementation**:
```php
// In PembayaranPusatResource.php
protected static ?int $navigationSort = 3; // After ASET (sort 2)
```

## Technology Decisions

| Component | Choice | Alternative Considered |
|-----------|--------|----------------------|
| Database | New table `pembayaran_pusat` | Extend ctk_payments - rejected for separation of concerns |
| Entity column | Redundant in table | Join to CTK - rejected for query performance |
| Soft deletes | Yes (SoftDeletes trait) | Hard delete - rejected for audit compliance |
| Activity log | Spatie LogsActivity | Manual logging - rejected for consistency |
| File storage | Public disk | Private disk - rejected for easier preview |

## Dependencies

All dependencies already installed and configured:
- `spatie/laravel-activitylog` - Activity logging
- `filament/filament` v4 - Admin panel
- `livewire/livewire` v3 - Reactive components

## No External Integrations

This feature is self-contained within the admin panel. No external API contracts needed.
