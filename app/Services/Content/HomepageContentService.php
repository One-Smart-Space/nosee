<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Contracts\Content\DataRepository;
use App\Contracts\Content\EventRepository;
use App\Contracts\Content\NewsRepository;
use App\Contracts\Content\PublicationRepository;

final class HomepageContentService
{
    public function __construct(
        private readonly FileContentLoader $loader,
        private readonly DataRepository $data,
        private readonly PublicationRepository $publications,
        private readonly NewsRepository $news,
        private readonly EventRepository $events,
    ) {
    }

    /** @return array<string, mixed> */
    public function get(): array
    {
        // Combine editorial homepage content with repository-selected catalogue records.
        return [
            ...$this->loader->loadFile('homepage.php'),
            'monitoring' => $this->data->homepageMonitoring(),
            'research_outputs' => $this->publications->featured(3),
            'trending_news' => $this->news->homepageTrending(4),
            'upcoming_events' => $this->events->featuredUpcoming(3),
        ];
    }
}
