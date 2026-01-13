<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\EntityType;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->description('Basic user account details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->minLength(8)
                            ->dehydrated(fn (?string $state) => filled($state))
                            ->dehydrateStateUsing(fn (?string $state) => filled($state) ? bcrypt($state) : null),
                    ])->columns(2),

                Section::make('Access Control')
                    ->description('Roles and entity assignment')
                    ->schema([
                        Select::make('entity')
                            ->label('Entity (PT/LPK)')
                            ->options(EntityType::options())
                            ->required()
                            ->native(false),
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->required()
                            ->label('Roles'),
                    ])->columns(2),
            ]);
    }
}
