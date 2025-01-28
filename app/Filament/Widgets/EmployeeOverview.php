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
        // chart files per day this month [1,5,6,3,4,10] and count in array $fileDataChart['count'] && $fileDataChart['chart']
        $fileDataChart = File::whereMonth('created_at', now()->month)->get()->groupBy(function($date) {
            return $date->created_at->format('d');
        });
        $departmentDataChart = Department::whereMonth('created_at', now()->month)->get()->groupBy(function($date) {
            return $date->created_at->format('d');
        });
        $employeeDataChart = User::whereMonth('created_at', now()->month)->get()
            ->groupBy(function($date) {
            return $date->created_at->format('d');
        });
        // print number of file for each date like [233,515,344]
        $fileDataChartNumberArray = array_map('count', $fileDataChart->toArray());
        $departmentDataChartNumberArray = array_map('count', $departmentDataChart->toArray());
        $employeeDataChartNumberArray = array_map('count', $employeeDataChart->toArray());


        return [
            Stat::make('Files', $fileDataChart->count())
                ->description('Files uploaded this month')
                ->descriptionIcon('heroicon-o-folder')
                ->color('success')
            ->chart($fileDataChartNumberArray)

            ,

            Stat::make('Departments', $departmentDataChart->count())
                ->description('Departments created this month')
                ->descriptionIcon('heroicon-o-building-office')
                ->color('primary')
            ->chart($departmentDataChartNumberArray),


            Stat::make('Employees', $employeeDataChart->count())
                ->description('Employees joined this month')
                ->descriptionIcon('heroicon-o-building-office-2')
                ->color('info')
            ->chart($employeeDataChartNumberArray),
        ];
    }

}
