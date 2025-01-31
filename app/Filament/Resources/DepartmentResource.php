<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use App\Models\User;
use Filament\Forms\Components\FileUpload;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Grid;
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
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $slug = 'departments';

    protected static ?string $navigationGroup = 'Employee Management';

    public static function form(Form $form): Form
    {

        return $form
            ->schema([
                Section::make('Basic Data')
            ->schema([
                TextInput::make('name'),
                TextInput::make('description'),
                FileUpload::make('avatar')
                    ->label('Department Image')
                    ->disk('public')
                    ->directory('departments')
                    ->openable()
                    ->columnSpanFull()
                ,
            ]),
                Section::make('Additional Data')
                    ->schema([
                        ///        'code', 'user_id', 'goals', 'main_responsibilities'
                        TextInput::make('code'),
                        Select::make('user_id')
                            ->options(
                                User::all()->pluck('name', 'id')
                            )
                            ->label('Head of Department')
                            ->searchable()
                            ->placeholder('Select Head of Department')
                            ->nullable(),

                        Textarea::make('goals')
                            ->label('Goals')
                        ,
                        Textarea::make('main_responsibilities')
                            ->label('Main Responsibilities')
                        ,
                        ]),

                // Assign users
                Section::make('Employees')
                    ->columns(1)
                    ->icon('heroicon-m-user-group')

                    ->heading( fn(?Department $record): string => 'Employees '. $record?->users->count() ?? '-')
                    ->schema([
                        Select::make('users')
                            ->multiple()
                            ->preload()
                            ->loadStateFromRelationshipsUsing(
                                fn(Select $select, Department $record, $state) => filled($state) ?: $select->state($record->users->pluck('id'))
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
                                            ->update(['department_id' => $record->id]); // Batch update for the chunk
                                    });

                                User::where('department_id', $record->id)
                                    ->whereNotIn('id', $selectedIds)
                                    ->chunkById(1000, function ($users) {
                                        $ids = $users->pluck('id');
                                        User::whereIn('id', $ids)
                                            ->update(['department_id' => null]);
                                    });
                            })
                    ]),

                // Assign shifts

                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Department $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Department $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Grid::make()
                    ->columns(1)

                    ->schema([

                        ImageColumn::make('avatar')
                            ->size(30)
                            ->width('100%')
                            ->height('150px')
                            ->extraImgAttributes(
                                [
                                    'style' => 'object-fit: cover; border-radius: 10px;',
                                ]
                            )
                            ->alignment('center')
                        ,
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->weight(FontWeight::SemiBold)
                            ->size(TextColumnSize::Large)
                            ->alignment('center')


                        ,
                        ImageColumn::make('users.avatar')
                            ->circular()
                            ->stacked()
                            ->limit(4)
                            ->alignment('center')
                            ->alignment('center')

                    ]),
                        TextColumn::make('description')
                            ->alignment('center')
                        ,



            ])
            ->searchable(true)
            ->contentGrid(['md' => 2 , 'xl' => 3])

            ->paginationPageOptions([9,18,27])
            ->defaultSort('id', 'desc')
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


            ],position: ActionsPosition::BeforeColumns)
            ->bulkActions([

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDepartments::route('/'),
            'create' => Pages\CreateDepartment::route('/create'),
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function canAccess(): bool
    {
        if(auth()->user()->role == 'employee') {
            return false;
        }
        return true;
    }

}
