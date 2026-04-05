# SIT_LPK Development Guidelines

Auto-generated from all feature plans. Last updated: 2026-01-13

## Active Technologies
- MySQL/MariaDB (karyawan_lpk table, relationship to future pelatihan table) (002-karyawan-lpk)
- PHP 8.4.5 + Laravel Framework v11, Filament v4, Livewire v3, Spatie Activity Log, Filament Shield (004-asset-management)
- MySQL/MariaDB (existing database) (004-asset-management)
- PHP 8.4.5 + Laravel 11, Filament 4, Livewire 3 (005-ctk-status-display)
- MySQL/MariaDB (existing CTK table, no schema changes) (005-ctk-status-display)
- PHP 8.2+ (production: PHP 8.4.5) + Laravel 11.28+, Filament 4.0+, Livewire 3, Spatie Activity Log 4.10 (006-ctk-view-actions)
- MySQL/MariaDB (existing CTK table, no schema changes required) (006-ctk-view-actions)
- PHP 8.4.5 + Laravel 11, Filament v4, Livewire v3 (007-ctk-edit-stages-separation)
- PHP 8.4.5 + Laravel 11, Filament v4, Livewire v3, Laravel Excel (maatwebsite/excel) (008-data-export-buttons)
- MySQL/MariaDB (tabel `karyawan_pt` baru, terpisah dari `karyawan_lpk`) (009-karyawan-pt-resource)
- PHP 8.4.x + Laravel 10+, Filament v4, Livewire v3, Spatie Activity Log (001-ctk-pembayaran-pusat)
- MySQL/MariaDB, File storage (public disk) untuk bukti transfer (001-ctk-pembayaran-pusat)

- PHP 8.4.5 (001-user-management-rbac)

## Project Structure

```text
src/
tests/
```

## Commands

# Add commands for PHP 8.4.5

## Code Style

PHP 8.4.5: Follow standard conventions

## Recent Changes
- 001-ctk-pembayaran-pusat: Added PHP 8.4.x + Laravel 10+, Filament v4, Livewire v3, Spatie Activity Log
- 009-karyawan-pt-resource: Added PHP 8.4.5
- 008-data-export-buttons: Added PHP 8.4.5 + Laravel 11, Filament v4, Livewire v3, Laravel Excel (maatwebsite/excel)


<!-- MANUAL ADDITIONS START -->
<!-- MANUAL ADDITIONS END -->
