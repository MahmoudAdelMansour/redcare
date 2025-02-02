<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftsResource\Pages;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ShiftsResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $slug = 'shifts';

    protected static ?string $navigationGroup = 'Employee Management';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Basic Info')
            ->schema([
                TextInput::make('name')
                ->columnSpanFull()
                ,

                TimePicker::make('start_time'),

                TimePicker::make('end_time'),
            ])->columns(2),


                Section::make('Employees')
                    ->columns(1)
                    ->icon('heroicon-m-user-group')

                    ->heading( fn(? Shift $record): string => 'Employees '. $record?->users->count() ?? '-')
                    ->schema([
                        Select::make('users')
                            ->multiple()
                            ->preload()
                            ->loadStateFromRelationshipsUsing(
                                fn(Select $select, Shift $record, $state) => filled($state) ?:
                                    $select->state($record->users->pluck('id'))
                            )
                            ->options(
                                Department::all()
                                    ->mapWithKeys(fn($department) => [
                                        $department->name =>
                                            $department->users->mapWithKeys(fn($user) => [
                                                $user->id =>
                                                    '<img src='.$user->avatar().' class="rounded-full w-6 h-6 mr-2 inline-block" />'.
                                                    "<span class='inline-block ml-2 mr-2'>&nbsp;{$user->name}</span>".
                                                    "<span class='inline-block text-xs text-gray-500'>&nbsp;{$department->name}</span>"

                                            ])
                                    ])
                            )
                            ->allowHtml()
                            ->searchable(['name', 'email'])

                            ->saveRelationshipsUsing(function ($record, $state) {
                                // Assume $state contains the selected user IDs
                                $selectedIds = $state; // Directly use the state as selectedIds
                                // Add users in chunks
                                User::whereIn('id', $selectedIds)
                                    ->chunkById(1000, function ($users) use ($record) {
                                        $ids = $users->pluck('id'); // Get the IDs in the current chunk
                                        User::whereIn('id', $ids)
                                            ->update(['shift_id' => $record->id]); // Batch update for the chunk
                                    });

                                User::where('shift_id', $record->id)
                                    ->whereNotIn('id', $selectedIds)
                                    ->chunkById(1000, function ($users) {
                                        $ids = $users->pluck('id'); // Get the IDs in the current chunk
                                        User::whereIn('id', $ids)
                                            ->update(['shift_id' => null]); // Batch update for the chunk
                                    });

                            }),
                    ]),
                Toggle::make('status')
                ->columnSpanFull()
                ,

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Shift $record): string => $record?->created_at?->diffForHumans() ?? '-')

                ,

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Shift $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('start_time')
                    ->time('h:i A')
                ,

                TextColumn::make('end_time')
                    ->time('h:i A'),

                ImageColumn::make('users.avatar')
                    ->circular()
                    ->stacked(),


                ToggleColumn::make('status')
                ->visible(fn() => auth()->user()->role == 'admin')
                ,
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    ViewAction::make()
                ])
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShifts::route('/create'),
            'edit' => Pages\EditShifts::route('/{record}/edit'),

        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['users']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'department.name'];
    }



    public static function canCreate(): bool
    {
        if(auth()->user()->role == 'employee') {
            return false;
        }
        return true;
    }
    public static function canEdit(Model $record): bool
    {
        if(auth()->user()->role == 'employee') {
            return false;
        }
        return true;
    }
    public static function canDelete(Model $record): bool
    {
        if(auth()->user()->role == 'employee') {
            return false;
        }
        return true;
    }
    public static function canView(Model $record): bool
    {
        return true;
    }
}
