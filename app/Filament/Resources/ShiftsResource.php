<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShiftsResource\Pages;
use App\Models\Department;
use App\Models\Shift;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
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
                                User::all()->pluck('name', 'id')
                            )
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
                            })
                    ]),
                Toggle::make('status'),

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Shift $record): string => $record?->created_at?->diffForHumans() ?? '-'),

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


                ToggleColumn::make('status'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
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



    public static function canAccess(): bool
    {
        if(auth()->user()->role == 'employee') {
            return false;
        }
        return true;
    }
}
