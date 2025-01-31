<?php

namespace App\Filament\Resources\PoliciesResource\Widgets;

use App\Models\Department;
use Filament\Widgets\ChartWidget;

class PolicyDepartmentChart extends ChartWidget
{
    protected static ?int $sort = 9;
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        // Doughnut chart data for each department containing the number of policies
        /*
         example
            return [
        'datasets' => [
            [
                'label' => 'Blog posts created',
                'data' => [0, 10, 5, 2, 21, 32, 45, 74, 65, 45, 77, 89],
                'backgroundColor' => '#36A2EB',
                'borderColor' => '#9BD0F5',
            ],
        ],
        'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
    ];
         * */
        $data = Department::withCount('policies')->get();
        return [
            'datasets' => [
                [
                    'label' => 'Policies',
                    'data' => $data->pluck('policies_count'),
                    'backgroundColor' =>
                        $data->pluck('policies_count')->map(fn($count) =>
                        $count > 5 ? '#E79D5C' : '#3B82F5')->toArray(),

                    'borderColor' => '#9BD0F5',
                ],
            ],
            'labels' => $data->pluck('name'),


        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
