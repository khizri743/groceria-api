<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Order;
use App\Models\User;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '$' . Order::sum('total_amount'))
                ->description('Total money earned')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Total Orders', Order::count())
                ->description('Orders placed so far')
                ->color('primary'),

            Stat::make('Registered Customers', User::where('role', 'customer')->count())
                ->description('Total app users')
                ->color('warning'),
        ];
    }
}