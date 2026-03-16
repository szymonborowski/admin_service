<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsApiService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;

class TrendingPostsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $heading = 'Trending Posts (last 30 days)';
    protected int | string | array $columnSpan = 'full';

    public string $period = '30d';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => null)
            ->columns([
                TextColumn::make('rank')
                    ->label('#')
                    ->state(static fn ($record, $rowLoop) => $rowLoop->iteration),
                TextColumn::make('post_uuid')
                    ->label('Post UUID')
                    ->limit(36)
                    ->tooltip(fn ($state) => $state),
                TextColumn::make('views')
                    ->label('Views')
                    ->sortable()
                    ->numeric()
                    ->alignEnd(),
                TextColumn::make('unique_views')
                    ->label('Unique')
                    ->numeric()
                    ->alignEnd(),
            ])
            ->records($this->getRecords())
            ->paginated(false);
    }

    private function getRecords(): SupportCollection
    {
        $analytics = app(AnalyticsApiService::class);
        $data = $analytics->trending($this->period, 20);

        return collect($data['posts'] ?? []);
    }
}
