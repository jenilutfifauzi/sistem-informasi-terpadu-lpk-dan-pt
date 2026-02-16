<?php

namespace App\Filament\Exports;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UserExport implements FromQuery, WithHeadings, WithMapping
{
    protected Builder $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        // Eager load roles for export
        return $this->query->with('roles');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Entity',
            'Roles',
            'Created At',
            'Updated At',
        ];
        // Note: password and remember_token excluded per security requirements (FR-009)
    }

    public function map($user): array
    {
        // Get user roles as comma-separated string
        $roles = $user->roles->pluck('name')->implode(', ');

        return [
            $user->id,
            $user->name,
            $user->email,
            $user->entity?->value ?? $user->entity,
            $roles ?: 'No Roles',
            $user->created_at?->format('Y-m-d H:i:s'),
            $user->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
