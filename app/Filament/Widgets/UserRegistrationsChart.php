<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class UserRegistrationsChart extends ChartWidget
{
    protected ?string $heading = 'User Registrations (Last 7 Days)';

    protected function getData(): array
    {
        $startDate = now()->startOfDay()->subDays(6);
        $endDate = now()->endOfDay();

        $registrationsByDate = User::query()
            ->selectRaw('DATE(created_at) as registration_date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('registration_date')
            ->pluck('total', 'registration_date');

        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->toDateString();
            $labels[] = Carbon::parse($dateKey)->format('M d');
            $data[] = (int) ($registrationsByDate[$dateKey] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Registrations',
                    'data' => $data,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.2)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
