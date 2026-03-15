<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Throwable;

class UserRegistrationsChart extends ChartWidget
{
    private const FILTER_LAST_7_DAYS = 'last_7_days';

    private const FILTER_LAST_30_DAYS = 'last_30_days';

    private const FILTER_CUSTOM = 'custom';

    protected ?string $heading = 'User Registrations';

    protected string $view = 'filament.widgets.inline-chart-widget';

    public ?string $filter = self::FILTER_LAST_7_DAYS;

    public ?string $startDate = null;

    public ?string $endDate = null;

    protected function getFilters(): ?array
    {
        return [
            self::FILTER_LAST_7_DAYS => 'Last 7 Days',
            self::FILTER_LAST_30_DAYS => 'Last 30 Days',
            self::FILTER_CUSTOM => 'Custom',
        ];
    }

    public function mount(): void
    {
        $this->startDate = now()->subDays(6)->toDateString();
        $this->endDate = now()->toDateString();

        parent::mount();
    }

    protected function getData(): array
    {
        [$startDate, $endDate] = $this->resolveDateRange();

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

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(): array
    {
        if ($this->filter === self::FILTER_LAST_30_DAYS) {
            return [
                now()->startOfDay()->subDays(29),
                now()->endOfDay(),
            ];
        }

        if ($this->filter === self::FILTER_CUSTOM) {
            $startDate = $this->parseDate($this->startDate);
            $endDate = $this->parseDate($this->endDate);

            if (($startDate instanceof Carbon) && ($endDate instanceof Carbon)) {
                if ($startDate->gt($endDate)) {
                    [$startDate, $endDate] = [$endDate, $startDate];
                }

                return [
                    $startDate->copy()->startOfDay(),
                    $endDate->copy()->endOfDay(),
                ];
            }
        }

        return [
            now()->startOfDay()->subDays(6),
            now()->endOfDay(),
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }
}
