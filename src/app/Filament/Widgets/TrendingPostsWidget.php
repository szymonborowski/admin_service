<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsApiService;
use Filament\Widgets\Widget;

class TrendingPostsWidget extends Widget
{
    protected static ?int $sort = 2;
    protected static string $view = 'filament.widgets.trending-posts';
    protected int | string | array $columnSpan = 'full';

    public string $period = '30d';

    public function getPosts(): array
    {
        $analytics = app(AnalyticsApiService::class);
        $data = $analytics->trending($this->period, 20);

        return $data['posts'] ?? [];
    }
}
