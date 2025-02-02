<?php

namespace App\Filament\Resources\EmployeeResource\Widgets;

use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class UserOverview extends BaseWidget
{
    protected function getStats(): array
    {
        /*
         3 stats
        1-user name / role
        2- user shift
        3- user department
         * */
        $userShift = auth()->user()->shift;
        $startTime = Carbon::parse($userShift->start_time)->format('h:i A');
        $endTime = Carbon::parse($userShift->end_time)->format('h:i A');
        return [
            Stat::make('Name', auth()->user()->name)
                ->description('Your Role is ' . auth()->user()->role)
                ->descriptionIcon('heroicon-o-user')
                ->color('success'),
            Stat::make('Shift',
                auth()->user()->shift->name
            )
                ->description(
                    "Starts at $startTime and ends at $endTime"
                )
                ->descriptionIcon('heroicon-o-clock')
                ->color('info'),
            Stat::make('Department', auth()->user()->department->name)
                ->description("With " . auth()->user()->department->users->count() . " Other employees")
                ->descriptionIcon('heroicon-o-building-office')
                ->color('warning'),
        ];
    }
}
