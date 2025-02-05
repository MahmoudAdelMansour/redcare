<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoliciesResource\Pages;
use App\Filament\Resources\PoliciesResource\RelationManagers;
use App\Models\Department;
use App\Models\Policies;
use App\Models\Shift;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;


class PoliciesResource extends Resource
{
    protected static ?string $model = Policies::class;

    protected static ?string $slug = 'policies';

    protected static ?string $navigationGroup = 'Employee Management';
    protected static ?string $recordTitleAttribute = 'policy_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Policy Data')
                    ->schema([
                    TextInput::make('policy_name'),
                    TextInput::make('policy_number')
                        ->numeric(),
                    Textarea::make('description'),
                    ])
                ->icon('heroicon-o-information-circle')
                ,
                Section::make('Attachments')
                    ->schema([
                        TextInput::make('link')
                            ->label('Attachment Link'),
                        FileUpload::make('attachment')
                            ->label('Attachment File')
                            ->disk('public')
                            ->openable()
                        ,
                        ])
                    ->icon('heroicon-o-folder-open')
                ,
                Section::make('Policy Details')
                    ->schema([
                        TextInput::make('purpose'),
                        TextInput::make('version'),
                        Textarea::make('details'),
                        Textarea::make('compliance'),
                    ])
                    ->icon('heroicon-o-document-text'),
                Section::make('Assignation')
                    ->visible(fn() => auth()->user()->role == 'admin')
            ->schema([
                Section::make('Policy Officer')
                    ->columns(1)
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->preload()
                            ->native(false)
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
                                    ]))
                            ->allowHtml()
                        ->required()
                        ,
                    ])
                    ->visible(
                        fn() => auth()->user()->role == 'admin'
                    )
                ,
                Section::make('Policy Departments')
                    ->columns(1)
                    ->icon('heroicon-m-building-office')
                    ->schema([
                        Select::make('departments')
                            ->label('Departments')
                            ->relationship('departments', 'name')
                            ->multiple()
                            ->preload()
                            ->visible(fn() => auth()->user()->role == 'admin')
                        ->required()
                    ])

                ,
                    ])
                ->icon('heroicon-o-users')
                ->columns(2)
                ,
                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'active' => 'active',
                                'disabled' => 'Disabled',
                                'under_review' => 'Under review',
                            ]),

                        Select::make('approval')
                            ->options([
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ]),
                        ])
                    ->icon('heroicon-o-check-circle')
                    ->columns(2)
                ,


                Textarea::make('notes')
                ->columnSpanFull()
                ,



                Placeholder::make('created_at')
                    ->label('Created Date')
                    ->content(fn(?Policies $record): string => $record?->created_at?->diffForHumans() ?? '-'),

                Placeholder::make('updated_at')
                    ->label('Last Modified Date')
                    ->content(fn(?Policies $record): string => $record?->updated_at?->diffForHumans() ?? '-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('policy_name'),
                TextColumn::make('policy_number')
                ->default('N/A')
                ,
                TextColumn::make('description')
                ->words(4),
                ImageColumn::make('user.avatar')
                    ->circular()
                ->tooltip(fn($record) => $record->user->name)
                ,
                ImageColumn::make('departments.avatar')
                    ->label('Departments')
                    ->circular()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->stacked()
                    ->tooltip(fn($record) => $record->departments->pluck('name')->join(', '))
                   ->default('https://ui-avatars.com/api/?background=282828&color=ffff&name=NA')
                ,
                ImageColumn::make('attachment')
                    ->stacked()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        return $record->attachment ? 'https://imgs.search.brave.com/XnZs0OSUPTIm7GR1SoOVW666qHxXrPiMyhAGatwQ444/rs:fit:500:0:0:0/g:ce/aHR0cDovL3d3dy5j/bGtlci5jb20vY2xp/cGFydHMvZi9ILzUv/YS9RL3kvdGV4dC1m/aWxlLWljb24tdGgu/cG5n'
                            :
                            'https://ui-avatars.com/api/?background=282828&color=ffff&name=NA';
                    })
                    ->url(fn($record) => asset('storage/'.$record->attachment))
                    ->openUrlInNewTab()
                    ->extraAttributes([
                        'download'
                    ])

                ,
                TextColumn::make('link')
                    ->label('Link')
                    ->icon('heroicon-o-link')
                    ->url(fn($record) => $record->link)
                    ->formatStateUsing(fn($state) => 'Open Link') // Change visible text
                    ->color('primary')
                ,

                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'active' => 'Active',
                        'disabled' => 'Disabled',
                        'under_review' => 'Under review',
                    ])
                    ->placeholder('Not Set')
                    ->disabled(
                        fn(Model $record) => auth()->user()->role == 'employee' || $record->user_id !== auth()->id()
                    )
                ,
                Tables\Columns\SelectColumn::make('approval')
                    ->options([
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->placeholder('Not Set')
                    ->disabled(
                        fn(Model $record) => auth()->user()->role == 'employee' || $record->user_id !== auth()->id()
                    )
                ,
                TextColumn::make('created_at')
                ->visible(fn() => auth()->user()->role == 'admin')
                ,

            ])
            ->filters([
                Filter::make('is_active')
                    ->query(fn (Builder $query):Builder => $query->where('status', true))
                ->label('Active Policies'),
                // Has Attachment
                Filter::make('has_attachment')
                    ->query(fn (Builder $query):Builder => $query->whereNotNull('attachment'))
                ->label('Has Attachment'),

                //Has Link
                Filter::make('has_link')
                    ->query(fn (Builder $query):Builder => $query->whereNotNull('link'))
                ->label('Has Link'),


            ])
            ->searchable()
            ->actions([
                Tables\Actions\ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                    Tables\Actions\ViewAction::make()
                ])

            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPolicies::route('/'),
            'create' => Pages\CreatePolicies::route('/create'),
            'edit' => Pages\EditPolicies::route('/{record}/edit'),
//            'view' => Pages\ViewPolicies::route('/{record}'),
        ];
    }
    public static function getGloballySearchableAttributes(): array
    {
    return [
        'policy_name',
        'policy_number',
        'description',
        'purpose',
        'version',
        'details',
    ];
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
        if (auth()->user()->role == 'manager') {
            return $record->user_id == auth()->id();
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
    public static function getEloquentQuery(): Builder
    {
//        if( auth()->user()->role == 'employee') {
//            return parent::getEloquentQuery()
//                ->whereHas('departments', function ($query) {
//                    $query->where('department_id', auth()->user()->department_id);
//                })
//                ->withoutGlobalScopes([
//                    SoftDeletingScope::class,
//                ]);
//        } elseif (auth()->user()->role == 'manager') {
//            return parent::getEloquentQuery()
//                ->where('user_id', auth()->id())
//                ->withoutGlobalScopes([
//                    SoftDeletingScope::class,
//                ]);
//        }

        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }




}
