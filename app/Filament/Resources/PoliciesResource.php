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
                    TextInput::make('police_number')
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
            ->schema([
                Section::make('Policy Officer')
                    ->columns(1)
                    ->icon('heroicon-m-user-group')
                    ->schema([
                        Select::make('user_id')
                            ->label('Users')
                            ->relationship('user', 'name')
                    ]),
                Section::make('Policy Officer')
                    ->columns(1)
                    ->icon('heroicon-m-building-office')
                    ->schema([
                        Select::make('departments')
                            ->label('Departments')
                            ->relationship('departments', 'name')
                            ->multiple()
                            ->preload()
                    ]),
                    ])
                ->icon('heroicon-o-users')
                ->columns(2)
                ,
                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'activity' => 'Activity',
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
                TextColumn::make('police_number'),
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
                ->default('https://icon-library.com/images/none-icon/none-icon-0.jpg')
                ,
                ImageColumn::make('attachment')
                    ->stacked()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        return $record->attachment ? 'https://imgs.search.brave.com/XnZs0OSUPTIm7GR1SoOVW666qHxXrPiMyhAGatwQ444/rs:fit:500:0:0:0/g:ce/aHR0cDovL3d3dy5j/bGtlci5jb20vY2xp/cGFydHMvZi9ILzUv/YS9RL3kvdGV4dC1m/aWxlLWljb24tdGgu/cG5n'
                            :
                            'https://icon-library.com/images/none-icon/none-icon-0.jpg';
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

                Tables\Columns\ToggleColumn::make('status'),
                Tables\Columns\ToggleColumn::make('approval'),
                TextColumn::make('created_at'),

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


}
