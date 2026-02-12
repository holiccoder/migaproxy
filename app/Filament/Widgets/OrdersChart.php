<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Orders (Last 7 Days)';

    protected function getData(): array
    {
        $startDate = now()->startOfDay()->subDays(6);
        $endDate = now()->endOfDay();

        $ordersByDate = Order::query()
            ->selectRaw('DATE(created_at) as order_date, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('order_date')
            ->pluck('total', 'order_date');

        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateKey = $date->toDateString();
            $labels[] = Carbon::parse($dateKey)->format('M d');
            $data[] = (int) ($ordersByDate[$dateKey] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Orders',
                    'data' => $data,
                    'borderColor' => '#0ea5e9',
                    'backgroundColor' => 'rgba(14, 165, 233, 0.2)',
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
