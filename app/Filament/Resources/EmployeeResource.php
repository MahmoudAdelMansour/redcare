<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Models\File;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EmployeeResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = "Employees";
    protected static ?string $slug = 'employees';

    protected static ?string $navigationGroup = 'Employee Management';


    public static function form(Form $form): Form
    {
        $user = auth()->user();
//dd($user->role);
        return $form
            ->schema([
                Section::make('Basic Info')
                    ->schema([
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('email')
                            ->required(),
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->required(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\CreateRecord)
                            ->dehydrated(fn ($state) => filled($state)),
                        FileUpload::make('avatar')
                            ->label('Document Image')
                            ->disk('public')
                            ->openable()
                            ->columnSpanFull(),

                    ]),
                Section::make('Job Info')
                    ->schema([

                        TextInput::make('job_title'),

                        TextInput::make('job_description'),

                        TextInput::make('employee_id')
                            ->visible(fn () => $user && ($user->role !== 'employee')),
                        TextInput::make('extension_number'),

                        Select::make('department_id')
                            ->relationship('department', 'name')
                            ->visible(fn () => $user && ($user->role !== 'employee')),

                        Select::make('status')
                            ->options(User::STATUS_ACTIVE)
                            ->visible(fn () => $user && ($user->role !== 'employee'))
                        ,
                        Select::make('shift_id')
                            ->relationship('shift', 'name')
                            ->preload()
                            ->required()
                            ->label('Shift')
                            ->searchable()
                            ->visible(fn () => $user && ($user->role !== 'employee'))
                        ->columnSpanFull()
                        ,


                        ])
                     ->columns(2),
                Section::make('System Info')
                    ->schema([

                        Select::make('role')
                            ->options(
                                User::ROLES
                            )
                            ->visible(fn () => $user && ($user->role !== 'employee')),
                        ]),



                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?User $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?User $record): string => $record?->updated_at?->diffForHumans() ?? '-'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label('Avatar')

                    ->circular(),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    //job description
                ->description(
                    //str limit 70
                    fn (User $record) => Str::limit($record->job_description, 70)
                    )
                ,

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state):string => match ($state) {
                        'Active' => 'success',
                        'active' => 'success',
                        'On Leave' => 'warning',
                        'Resigned' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('role')
                ->state(
                    fn (User $record) =>  User::ROLES[$record->role ?? 'undefined']
                ),
                TextColumn::make('department.name')
                ->weight(FontWeight::Bold)
                ->badge()
                ->color('info')
                ,
                SelectColumn::make('shift_id')
                    ->label('Shift')
                    ->options(
                        \App\Models\Shift::all()->pluck('name', 'id')
                    )
                    ->visible(fn () => auth()->user()->role !== 'employee'),
                TextColumn::make('job_title'),
                TextColumn::make('employee_id'),



                TextColumn::make('extension_number'),


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
