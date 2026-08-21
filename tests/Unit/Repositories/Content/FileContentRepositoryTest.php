<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Content;

use App\Contracts\Content\DataRepository;
use App\Contracts\Content\EventRepository;
use App\Contracts\Content\NewsRepository;
use App\Contracts\Content\OutreachRepository;
use App\Contracts\Content\ProductRepository;
use App\Contracts\Content\PublicationRepository;
use App\Contracts\Content\ResearchRepository;
use App\Exceptions\ContentNotFoundException;
use App\Exceptions\InvalidContentFileException;
use App\Exceptions\InvalidContentPathException;
use App\Repositories\Content\FileDataRepository;
use App\Repositories\Content\FileEventRepository;
use App\Repositories\Content\FileNewsRepository;
use App\Repositories\Content\FileOutreachRepository;
use App\Repositories\Content\FileProductRepository;
use App\Repositories\Content\FilePublicationRepository;
use App\Repositories\Content\FileResearchRepository;
use App\Services\Content\FileContentLoader;
use FilesystemIterator;
use ParseError;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class FileContentRepositoryTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'nosee-content-'.bin2hex(random_bytes(8));
        mkdir($this->basePath);
    }

    protected function tearDown(): void
    {
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->basePath, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($this->basePath);

        parent::tearDown();
    }

    public function test_empty_and_missing_collections_are_handled_intentionally(): void
    {
        $this->createCollection('research/areas');
        $loader = new FileContentLoader($this->basePath);

        $this->assertSame([], (new FileResearchRepository($loader))->all());

        $repository = new FileNewsRepository($loader);

        $this->assertSame([], $repository->all());
        $this->assertNull($repository->find('missing-record'));
        $this->assertFalse($repository->exists('missing-record'));
    }

    public function test_records_are_loaded_in_filename_order_with_default_slugs(): void
    {
        $this->writeRecord('research/areas', 'space-weather', ['title' => 'Space Weather']);
        $this->writeRecord('research/areas', 'climate-science', ['title' => 'Climate Science']);
        file_put_contents(
            $this->createCollection('research/areas').DIRECTORY_SEPARATOR.'ignored.md',
            '# Ignored',
        );

        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        $this->assertSame([
            ['slug' => 'climate-science', 'title' => 'Climate Science'],
            ['slug' => 'space-weather', 'title' => 'Space Weather'],
        ], $repository->all());
    }

    public function test_homepage_monitoring_records_are_filtered_in_file_order(): void
    {
        $this->writeRecord('data/items', 'zeta-monitor', [
            'homepage_monitoring' => true,
        ]);
        $this->writeRecord('data/items', 'middle-record', [
            'homepage_monitoring' => false,
        ]);
        $this->writeRecord('data/items', 'alpha-monitor', [
            'homepage_monitoring' => true,
        ]);

        $records = (new FileDataRepository(new FileContentLoader($this->basePath)))->homepageMonitoring();

        $this->assertSame(
            ['alpha-monitor', 'zeta-monitor'],
            array_column($records, 'slug'),
        );
    }

    public function test_featured_publications_are_filtered_sorted_and_limited(): void
    {
        $this->writeRecord('publications', 'older-featured', [
            'publication_date' => '2025-03-01',
            'featured' => true,
        ]);
        $this->writeRecord('publications', 'newer-featured', [
            'publication_date' => '2026-04-01',
            'featured' => true,
        ]);
        $this->writeRecord('publications', 'newest-featured', [
            'publication_date' => '2026-06-01',
            'featured' => true,
        ]);
        $this->writeRecord('publications', 'excluded-publication', [
            'publication_date' => '2026-07-01',
            'featured' => false,
        ]);

        $repository = new FilePublicationRepository(new FileContentLoader($this->basePath));

        $this->assertSame(
            ['newest-featured', 'newer-featured'],
            array_column($repository->featured(2), 'slug'),
        );
        $this->assertSame([], $repository->featured(0));
    }

    public function test_homepage_trending_keeps_featured_first_then_uses_date_and_slug_order(): void
    {
        $this->writeRecord('news', 'older-featured', [
            'published_at' => '2025-01-01T09:00:00+00:00',
            'featured' => true,
        ]);
        $this->writeRecord('news', 'newest-story', [
            'published_at' => '2026-08-01T09:00:00+00:00',
            'featured' => false,
        ]);
        $this->writeRecord('news', 'beta-tied-story', [
            'published_at' => '2026-07-01T09:00:00+00:00',
            'featured' => false,
        ]);
        $this->writeRecord('news', 'alpha-tied-story', [
            'published_at' => '2026-07-01T09:00:00+00:00',
            'featured' => false,
        ]);
        $this->writeRecord('news', 'oldest-story', [
            'published_at' => '2024-01-01T09:00:00+00:00',
            'featured' => false,
        ]);

        $repository = new FileNewsRepository(new FileContentLoader($this->basePath));
        $records = $repository->homepageTrending();

        $this->assertSame([
            'older-featured',
            'newest-story',
            'alpha-tied-story',
            'beta-tied-story',
        ], array_column($records, 'slug'));
        $this->assertCount(4, array_unique(array_column($records, 'slug')));
        $this->assertSame(
            ['older-featured', 'newest-story'],
            array_column($repository->homepageTrending(2), 'slug'),
        );
        $this->assertSame([], $repository->homepageTrending(0));
    }

    public function test_featured_upcoming_events_filter_sort_and_limit_deterministically(): void
    {
        $today = new \DateTimeImmutable('today');
        $this->writeRecord('events', 'past-featured', [
            'start_date' => $today->modify('-2 days')->format('Y-m-d'),
            'end_date' => $today->modify('-1 day')->format('Y-m-d'),
            'featured' => true,
        ]);
        $this->writeRecord('events', 'unfeatured-sooner', [
            'start_date' => $today->modify('+1 day')->format('Y-m-d'),
            'end_date' => $today->modify('+1 day')->format('Y-m-d'),
            'featured' => false,
        ]);
        $this->writeRecord('events', 'ending-today-featured', [
            'start_date' => $today->modify('-1 day')->format('Y-m-d'),
            'end_date' => $today->format('Y-m-d'),
            'featured' => true,
        ]);
        $this->writeRecord('events', 'first-featured', [
            'start_date' => $today->modify('+2 days')->format('Y-m-d'),
            'end_date' => $today->modify('+2 days')->format('Y-m-d'),
            'featured' => true,
        ]);
        $this->writeRecord('events', 'beta-tied-featured', [
            'start_date' => $today->modify('+4 days')->format('Y-m-d'),
            'end_date' => $today->modify('+5 days')->format('Y-m-d'),
            'featured' => true,
        ]);
        $this->writeRecord('events', 'alpha-tied-featured', [
            'start_date' => $today->modify('+4 days')->format('Y-m-d'),
            'end_date' => $today->modify('+4 days')->format('Y-m-d'),
            'featured' => true,
        ]);

        $repository = new FileEventRepository(new FileContentLoader($this->basePath));

        $this->assertSame([
            'ending-today-featured',
            'first-featured',
            'alpha-tied-featured',
        ], array_column($repository->featuredUpcoming(), 'slug'));
        $this->assertSame(
            ['ending-today-featured', 'first-featured'],
            array_column($repository->featuredUpcoming(2), 'slug'),
        );
        $this->assertSame([
            'ending-today-featured',
            'first-featured',
            'alpha-tied-featured',
            'beta-tied-featured',
        ], array_column($repository->featuredUpcoming(4), 'slug'));
        $this->assertSame([], $repository->featuredUpcoming(0));
    }

    public function test_find_exists_and_explicit_slug_behaviour(): void
    {
        $this->writeRecord('research/areas', 'climate-science', [
            'slug' => 'preserved-exactly',
            'title' => 'Climate Science',
        ]);

        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        $this->assertSame([
            'slug' => 'preserved-exactly',
            'title' => 'Climate Science',
        ], $repository->find('climate-science'));
        $this->assertTrue($repository->exists('climate-science'));
        $this->assertNull($repository->find('missing-record'));
        $this->assertFalse($repository->exists('missing-record'));
    }

    public function test_find_or_fail_throws_a_content_specific_exception(): void
    {
        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        try {
            $repository->findOrFail('missing-record');
            $this->fail('Expected a content not-found exception.');
        } catch (ContentNotFoundException $exception) {
            $this->assertSame('research/areas', $exception->collection);
            $this->assertSame('missing-record', $exception->slug);
            $this->assertStringNotContainsString($this->basePath, $exception->getMessage());
        }
    }

    public function test_content_files_must_return_arrays(): void
    {
        $this->writePhp('research/areas', 'invalid-record.php', '<?php return "invalid";');
        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        try {
            $repository->find('invalid-record');
            $this->fail('Expected an invalid-content-file exception.');
        } catch (InvalidContentFileException $exception) {
            $this->assertStringContainsString('invalid-record.php', $exception->getMessage());
            $this->assertStringNotContainsString($this->basePath, $exception->getMessage());
        }
    }

    public function test_malformed_php_preserves_the_original_exception(): void
    {
        $this->writePhp('research/areas', 'broken-record.php', '<?php return [;');
        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        try {
            $repository->find('broken-record');
            $this->fail('Expected an invalid-content-file exception.');
        } catch (InvalidContentFileException $exception) {
            $this->assertInstanceOf(ParseError::class, $exception->getPrevious());
            $this->assertStringNotContainsString($this->basePath, $exception->getMessage());
        }
    }

    public function test_slug_validation_accepts_valid_values_and_rejects_invalid_values(): void
    {
        $this->createCollection('research/areas');
        $repository = new FileResearchRepository(new FileContentLoader($this->basePath));

        foreach (['climate-science', 'icelli-2026', 'space-weather', 'event-2026', 'research2'] as $slug) {
            $this->assertNull($repository->find($slug));
        }

        $invalidSlugs = [
            '../climate-science',
            'Climate-Science',
            'climate_science',
            'climate science',
            'climate-science.php',
            '/climate-science',
            'climate--science',
            'climate-science/',
            'climate.science',
        ];

        foreach ($invalidSlugs as $slug) {
            try {
                $repository->find($slug);
            } catch (InvalidContentPathException) {
                continue;
            }

            $this->fail("Expected slug [{$slug}] to be rejected.");
        }
    }

    public function test_collection_paths_cannot_escape_the_content_directory(): void
    {
        $loader = new FileContentLoader($this->basePath);

        foreach (['../', '../../', 'research/../../../', 'C:\\', '/', '\\'] as $collection) {
            try {
                $loader->loadCollection($collection);
            } catch (InvalidContentPathException) {
                continue;
            }

            $this->fail("Expected collection [{$collection}] to be rejected.");
        }
    }

    public function test_concrete_repositories_use_the_intended_collections(): void
    {
        $repositories = [
            FileResearchRepository::class => 'research/areas',
            FileDataRepository::class => 'data/items',
            FileProductRepository::class => 'products/items',
            FilePublicationRepository::class => 'publications',
            FileEventRepository::class => 'events',
            FileNewsRepository::class => 'news',
            FileOutreachRepository::class => 'outreach',
        ];

        foreach ($repositories as $repository => $collection) {
            $this->writeRecord($collection, 'expected-record', ['collection' => $collection]);
        }

        $loader = new FileContentLoader($this->basePath);

        foreach ($repositories as $repository => $collection) {
            $this->assertSame([
                ['slug' => 'expected-record', 'collection' => $collection],
            ], (new $repository($loader))->all());
        }
    }

    public function test_service_container_resolves_the_loader_and_domain_contracts(): void
    {
        $this->assertInstanceOf(FileContentLoader::class, $this->app->make(FileContentLoader::class));
        $this->assertSame(
            $this->app->make(FileContentLoader::class),
            $this->app->make(FileContentLoader::class),
        );

        $bindings = [
            ResearchRepository::class => FileResearchRepository::class,
            DataRepository::class => FileDataRepository::class,
            ProductRepository::class => FileProductRepository::class,
            PublicationRepository::class => FilePublicationRepository::class,
            EventRepository::class => FileEventRepository::class,
            NewsRepository::class => FileNewsRepository::class,
            OutreachRepository::class => FileOutreachRepository::class,
        ];

        foreach ($bindings as $contract => $repository) {
            $this->assertInstanceOf($repository, $this->app->make($contract));
        }

        $this->assertFalse($this->app->bound('App\\Contracts\\Content\\MeetingRepository'));
    }

    public function test_default_loader_uses_the_project_content_directory(): void
    {
        $records = (new FileContentLoader)->loadCollection('research/areas');

        $this->assertArrayHasKey('climate-science', $records);
        $this->assertSame('climate-science', $records['climate-science']['slug']);
    }

    public function test_invalid_base_path_is_rejected(): void
    {
        $this->expectException(InvalidContentPathException::class);

        new FileContentLoader($this->basePath.DIRECTORY_SEPARATOR.'missing');
    }

    private function createCollection(string $collection): string
    {
        $directory = $this->basePath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $collection);

        if (! is_dir($directory)) {
            mkdir($directory, recursive: true);
        }

        return $directory;
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function writeRecord(string $collection, string $slug, array $record): void
    {
        $this->writePhp(
            $collection,
            $slug.'.php',
            "<?php\n\nreturn ".var_export($record, true).";\n",
        );
    }

    private function writePhp(string $collection, string $filename, string $contents): void
    {
        file_put_contents(
            $this->createCollection($collection).DIRECTORY_SEPARATOR.$filename,
            $contents,
        );
    }
}
