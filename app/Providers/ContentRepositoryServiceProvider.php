<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Content\DataRepository;
use App\Contracts\Content\EventRepository;
use App\Contracts\Content\NewsRepository;
use App\Contracts\Content\OutreachRepository;
use App\Contracts\Content\ProductRepository;
use App\Contracts\Content\PublicationRepository;
use App\Contracts\Content\ResearchRepository;
use App\Repositories\Content\FileDataRepository;
use App\Repositories\Content\FileEventRepository;
use App\Repositories\Content\FileNewsRepository;
use App\Repositories\Content\FileOutreachRepository;
use App\Repositories\Content\FileProductRepository;
use App\Repositories\Content\FilePublicationRepository;
use App\Repositories\Content\FileResearchRepository;
use App\Services\Content\ContentPathValidator;
use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\FileContentLoader;
use Illuminate\Support\ServiceProvider;

final class ContentRepositoryServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    private const REPOSITORIES = [
        ResearchRepository::class => FileResearchRepository::class,
        DataRepository::class => FileDataRepository::class,
        ProductRepository::class => FileProductRepository::class,
        PublicationRepository::class => FilePublicationRepository::class,
        EventRepository::class => FileEventRepository::class,
        NewsRepository::class => FileNewsRepository::class,
        OutreachRepository::class => FileOutreachRepository::class,
    ];

    public function register(): void
    {
        $this->app->singleton(ContentSchemaRegistry::class);
        $this->app->singleton(ContentPathValidator::class);
        $this->app->singleton(
            FileContentLoader::class,
            fn (): FileContentLoader => new FileContentLoader(
                $this->app->basePath('content'),
                $this->app->make(ContentPathValidator::class),
            ),
        );

        foreach (self::REPOSITORIES as $contract => $repository) {
            $this->app->bind($contract, $repository);
        }
    }
}
