<?php

namespace App\Filament\Widgets;

use App\Models\Department;
use App\Models\File;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeOverview extends BaseWidget
{

    protected function getStats(): array
    {
        $fileData = File::whereMonth('created_at', now()->month)->count();
        $departmentData = Department::whereMonth('created_at', now()->month)->count();
        $employeeData = User::whereMonth('created_at', now()->month)->count();

        return [
            Stat::make('Files', $fileData)
                ->description('Files uploaded this month')
                ->descriptionIcon('heroicon-o-folder')
                ->color('success'),

            Stat::make('Departments', $departmentData)
                ->description('Departments created this month')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Employees', $employeeData)
                ->description('Employees joined this month')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('info'),
        ];
    }

}
