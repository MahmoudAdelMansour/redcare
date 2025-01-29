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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PoliciesResource extends Resource
{
    protected static ?string $model = Policies::class;

    protected static ?string $slug = 'policies';

    protected static ?string $navigationGroup = 'Employee Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('policy_name'),
                TextInput::make('police_number')
                    ->numeric(),
                Textarea::make('description'),
                TextInput::make('purpose'),
                TextInput::make('version'),
                Textarea::make('details'),
                TextInput::make('link')
                    ->label('Attachment Link'),
                FileUpload::make('attachment')
                    ->label('Attachment File')
                    ->disk('public')
                    ->openable()
                    ,
                Select::make('status')
                    ->options([
                        'activity' => 'Activity',
                        'disabled' => 'Disabled',
                        'under_review' => 'Under review',
                    ]),
                Textarea::make('compliance'),
                Select::make('approval')
                ->options([
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]),
                Textarea::make('notes'),
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
                TextColumn::make('description'),
                TextColumn::make('purpose'),
                TextColumn::make('version'),
                TextColumn::make('details'),
                TextColumn::make('link'),
                ImageColumn::make('attachment')
                    ->width('80%')
                    ->height('80%')
                    ->extraImgAttributes(
                        [
                            'style' => 'object-fit: cover; border-radius: 10px;',
                        ]
                    )
                    ->stacked()
                    ->alignment('center')
                    ->getStateUsing(function ($record) {
                        $filePath = $record->attachment;
                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                        if (in_array(strtolower($extension), $imageExtensions)) {
                            return $filePath;
                        }
                        return 'https://imgs.search.brave.com/sop3FFpXsaNyHE_cr3mMs9bkvhuN4y_U0P6Zytjq70U/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudmV4ZWxzLmNv/bS9tZWRpYS91c2Vy/cy8zLzEyODQ1Ny9p/c29sYXRlZC9wcmV2/aWV3LzQ2M2Q2MzU4/N2QwNTA1ODEyYjgz/Mjc5Yzk5ZjJmZTI3/LXByZXNjcmlwdGlv/bi1mb2xkZXItaWNv/bi5wbmc';
                    }),
                TextColumn::make('status'),
                TextColumn::make('compliance'),
                TextColumn::make('approval'),
                TextColumn::make('notes'),
                TextColumn::make('user.name')
                    ->label('User'),

                ImageColumn::make('departments.avatar')
                    ->label('Departments')
                    ->sortable()
                    ->circular()
                ->stacked()
                ,
                TextColumn::make('created_at'),
                TextColumn::make('updated_at'),
            ])
            ->filters([
                //
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
}
