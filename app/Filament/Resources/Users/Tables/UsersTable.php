<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\EntityType;
use App\Filament\Exports\UserExport;
use Filament\Actions;
use Filament\Forms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email Address')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('entity')
                    ->label('Entity')
                    ->badge()
                    ->color(fn (EntityType $state) => $state->color())
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Deleted')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('entity')
                    ->options(EntityType::options())
                    ->searchable(),
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable(),
                TrashedFilter::make(),
            ])
            ->headerActions([
                Actions\Action::make('export')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($livewire) {
                        $query = $livewire->getFilteredTableQuery();
                        $export = new UserExport($query);

                        activity()
                            ->causedBy(auth()->user())
                            ->withProperties([
                                'export_type' => 'user',
                                'format' => 'csv',
                                'record_count' => $query->count(),
                            ])
                            ->log('Data exported');

                        return Excel::download(
                            $export,
                            'users-'.now()->format('Y-m-d').'.csv',
                            \Maatwebsite\Excel\Excel::CSV
                        );
                    }),
            ]);
    }
}
