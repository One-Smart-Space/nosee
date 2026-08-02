<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories\Content;

use App\Contracts\Content\DataRepository;
use App\Contracts\Content\EventRepository;
use App\Contracts\Content\MeetingRepository;
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
use App\Repositories\Content\FileMeetingRepository;
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
            FileMeetingRepository::class => 'meetings',
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
            MeetingRepository::class => FileMeetingRepository::class,
            PublicationRepository::class => FilePublicationRepository::class,
            EventRepository::class => FileEventRepository::class,
            NewsRepository::class => FileNewsRepository::class,
            OutreachRepository::class => FileOutreachRepository::class,
        ];

        foreach ($bindings as $contract => $repository) {
            $this->assertInstanceOf($repository, $this->app->make($contract));
        }
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
