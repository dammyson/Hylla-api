<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class MetricOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count()),
            Stat::make('Total Products', Product::count()),
            Stat::make('Users in Last Week', User::where('created_at', '>=', now()->subWeek())->count()),
            Stat::make('Users in Last Month', User::where('created_at', '>=', now()->subMonth())->count()),
        ];
    }
}
