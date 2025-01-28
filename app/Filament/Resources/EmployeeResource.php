<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = "Employees";
    protected static ?string $slug = 'employees';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        $user = auth()->user();
//dd($user->role);
        return $form
            ->schema([
                TextInput::make('name')
                    ->required(),

                TextInput::make('email')
                    ->required(),

                DatePicker::make('email_verified_at')
                    ->label('Email Verified Date'),

                TextInput::make('password')
                    ->label(__('label.password'))
                    ->password()
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                    ->dehydrated(fn ($state) => filled($state)),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?User $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?User $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

                TextInput::make('job_title'),

                TextInput::make('job_description'),

                TextInput::make('employee_id')
                    ->visible(fn () => $user && ($user->role !== 'employee')),

                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->visible(fn () => $user && ($user->role !== 'employee')),

                TextInput::make('status'),

                TextInput::make('extension_number'),

                Select::make('role')
                    ->options([
                        'Admin' => 'Admin',
                        'manager' => 'Manager',
                        'employee' => 'Employee',
                    ])
                    ->visible(fn () => $user && ($user->role !== 'employee')),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email_verified_at')
                    ->label('Email Verified Date')
                    ->date(),

                TextColumn::make('job_title'),

                TextColumn::make('job_description'),

                TextColumn::make('employee_id'),

                TextColumn::make('department.name'),

                TextColumn::make('status'),

                TextColumn::make('extension_number'),

                TextColumn::make('role')
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        $user = auth()->user();
        if ($user && $user->role == 'employee') {
            return ('My Profile');
        }
        return ('employees');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        if ($user->role === 'employee') {
            return static::getModel()::query()->where('id', $user->id);
        } else {
            return static::getModel()::query();

        }
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
