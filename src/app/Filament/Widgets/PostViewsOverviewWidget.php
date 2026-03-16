<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsApiService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PostViewsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $analytics = app(AnalyticsApiService::class);

        $week  = $analytics->trending('7d', 50);
        $month = $analytics->trending('30d', 50);

        $viewsWeek  = array_sum(array_column($week['posts']  ?? [], 'views'));
        $viewsMonth = array_sum(array_column($month['posts'] ?? [], 'views'));

        $activePostsWeek = count($week['posts'] ?? []);

        return [
            Stat::make('Views — last 7 days', number_format($viewsWeek))
                ->description('Unique posts: ' . $activePostsWeek)
                ->color('primary')
                ->icon('heroicon-o-eye'),

            Stat::make('Views — last 30 days', number_format($viewsMonth))
                ->color('success')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Active posts (7d)', $activePostsWeek)
                ->description('Posts with at least 1 view')
                ->color('info')
                ->icon('heroicon-o-document-text'),
        ];
    }
}
