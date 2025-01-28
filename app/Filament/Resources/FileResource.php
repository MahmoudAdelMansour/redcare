<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FileResource\Pages;
use App\Filament\Resources\FileResource\RelationManagers;
use App\Models\File;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextColumn\TextColumnSize;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $user = auth()->user();

        return $form
            ->schema([
                Hidden::make('user_id')
                    ->default($user->id),

                FileUpload::make('up_file')
                    ->label('Document Image')
                    ->disk('public')
                    ->openable(),

                TextInput::make('title'),

                TextInput::make('description'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Grid::make()
                    ->columns(1)

                    ->schema([
                            TextColumn::make('user.name')
                                ->color('primary')
                                ->prefix('Uploaded By ')
                        ->alignment('center'),
                        ImageColumn::make('up_file')
                            ->height('200px')
                            ->width('233px')
                            ->getStateUsing(function ($record) {
                                $filePath = $record->up_file;

                                $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                $extension = pathinfo($filePath, PATHINFO_EXTENSION);

                                if (in_array(strtolower($extension), $imageExtensions)) {
                                    return $filePath;
                                }

                                return 'https://imgs.search.brave.com/sop3FFpXsaNyHE_cr3mMs9bkvhuN4y_U0P6Zytjq70U/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudmV4ZWxzLmNv/bS9tZWRpYS91c2Vy/cy8zLzEyODQ1Ny9p/c29sYXRlZC9wcmV2/aWV3LzQ2M2Q2MzU4/N2QwNTA1ODEyYjgz/Mjc5Yzk5ZjJmZTI3/LXByZXNjcmlwdGlv/bi1mb2xkZXItaWNv/bi5wbmc';
                            }),
                        TextColumn::make('title')
                            ->weight(FontWeight::SemiBold)
                            ->size(TextColumnSize::Large)
                            ->alignment('center')
                        ,
                        TextColumn::make('description')
                            ->html()
                            ->alignment('center')
                        ,

                        TextColumn::make('created_at')
                        ->datetime('h:i:s A')
                        ->color('info')
                            ->alignment('center')
                        ,

                    ])

                ,

            ])
                ->searchable(true)
            ->contentGrid(['md' => 2 , 'xl' => 3])

            ->paginationPageOptions([9,18,27])
            ->defaultSort('id', 'desc')
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->badge()
                ->badgeColor('primary')
                ->color('info'),
                // Download ACtion ( url with download )
                Tables\Actions\Action::make('Download')
                ->url(
                    fn($record) => asset('storage/' . $record->up_file)
                )
                ->icon('heroicon-o-arrow-down')
                ->color('success')
                ->openUrlInNewTab()
                ->extraAttributes([
                    'style' => 'display:flex;justify-content:center;align-items:center;position:absolute;right:15px;  ',
                    'download' => 'download',
                ])

            ])

            ->bulkActions([

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
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
        ];
    }

    public static function getLabel(): ?string
    {
        $user = auth()->user();
        if ($user && $user->role == 'employee') {
            return ('My File');
        }
        return ('Files');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();

        if ($user->role === 'employee') {
            return static::getModel()::query()->where('user_id', $user->id);
        } else {
            return static::getModel()::query();

        }
    }

}
