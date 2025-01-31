<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEmployees extends BaseWidget
{

    protected static ?int $sort = 10;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->latest()
                    ->limit(8)

            )
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->height('40px')
                    ->size('3em')
                    ->default(
                        fn($record) => 'https://ui-avatars.com/api/?name='.substr($record->name,0,2) .'&color=7F9CF5&background=EBF4FF&bold=true'
                    )
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-o-envelope')
                    ->iconColor('info')
                    ->url(fn($record) => "mailto:{$record->email}")
                    ->searchable()
                    ->sortable(),

            ]);
    }
}
