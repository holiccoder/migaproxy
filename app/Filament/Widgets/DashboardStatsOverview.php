<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $activeSubscriptions = Subscription::query()
            ->active()
            ->count();
        $totalUsers = User::query()->count();
        $totalOrders = Order::query()->count();
        $totalPaidAmount = Order::query()
            ->where('status', Order::STATUS_PAID)
            ->sum('total');

        return [
            Stat::make('Active Subscriptions', number_format($activeSubscriptions))
                ->description('Currently active plans')
                ->icon('heroicon-o-sparkles'),
            Stat::make('Total Users', number_format($totalUsers))
                ->description('Registered users')
                ->icon('heroicon-o-users'),
            Stat::make('Total Orders', number_format($totalOrders))
                ->description('All orders')
                ->icon('heroicon-o-shopping-bag'),
            Stat::make('Total Payment Amount', '$'.number_format($totalPaidAmount / 100, 2))
                ->description('Paid orders total')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}
