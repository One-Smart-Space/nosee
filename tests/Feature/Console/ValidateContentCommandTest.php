<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Services\Content\ContentPathValidator;
use App\Services\Content\FileContentLoader;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class ValidateContentCommandTest extends TestCase
{
    private string $basePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basePath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'nosee-validation-'.bin2hex(random_bytes(8));
        mkdir($this->basePath);
        $this->copyStarterContent();

        $this->app->instance(
            FileContentLoader::class,
            new FileContentLoader(
                $this->basePath,
                $this->app->make(ContentPathValidator::class),
            ),
        );
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

    public function test_command_returns_success_for_valid_fixtures(): void
    {
        $this->artisan('content:validate')
            ->expectsOutput('Content validation passed: 40 files validated.')
            ->assertExitCode(0);
    }

    public function test_command_requires_exactly_three_homepage_monitoring_records(): void
    {
        $path = 'data/items/range-error-from-tec.php';
        $record = require $this->path($path);
        $record['homepage_monitoring'] = false;
        $this->writeRecord($path, $record);

        $this->artisan('content:validate')
            ->expectsOutputToContain('Exactly three data records must enable homepage monitoring.')
            ->assertExitCode(1);
    }

    public function test_command_requires_exactly_three_featured_publications(): void
    {
        $path = 'publications/low-latitude-geomagnetic-storm-responses.php';
        $record = require $this->path($path);
        $record['featured'] = false;
        $this->writeRecord($path, $record);

        $this->artisan('content:validate')
            ->expectsOutputToContain('Exactly three publication records must be featured.')
            ->assertExitCode(1);
    }

    public function test_command_requires_exactly_one_featured_news_record(): void
    {
        $path = 'news/solar-storm-monitoring-exercise.php';
        $record = require $this->path($path);
        $record['featured'] = false;
        $this->writeRecord($path, $record);

        $this->artisan('content:validate')
            ->expectsOutputToContain('Exactly one news record must be featured.')
            ->assertExitCode(1);
    }

    public function test_command_requires_exactly_three_featured_upcoming_events(): void
    {
        $path = 'events/regional-space-weather-conference.php';
        $record = require $this->path($path);
        $record['featured'] = false;
        $this->writeRecord($path, $record);

        $this->artisan('content:validate')
            ->expectsOutputToContain('Exactly three upcoming event records must be featured.')
            ->assertExitCode(1);
    }

    public function test_command_requires_exactly_nine_event_records(): void
    {
        unlink($this->path('events/ionospheric-methods-seminar.php'));

        $this->artisan('content:validate')
            ->expectsOutputToContain('Exactly nine event records are required.')
            ->assertExitCode(1);
    }

    public function test_command_rejects_a_featured_event_that_has_passed(): void
    {
        $path = 'events/regional-space-weather-conference.php';
        $record = require $this->path($path);
        $record['start_date'] = '2025-01-01';
        $record['end_date'] = '2025-01-02';
        $this->writeRecord($path, $record);

        $this->artisan('content:validate')
            ->expectsOutputToContain('Featured events must not have passed.')
            ->assertExitCode(1);
    }

    public function test_command_returns_failure_with_file_level_errors(): void
    {
        $this->writeRecord('research/areas/climate-science.php', [
            'slug' => 'climate-science',
            'summary' => 'The title is intentionally missing.',
            'featured' => false,
        ]);

        $this->artisan('content:validate')
            ->expectsOutputToContain('research/areas/climate-science.php')
            ->expectsOutputToContain('title')
            ->assertExitCode(1);
    }

    public function test_command_continues_after_a_malformed_file(): void
    {
        file_put_contents(
            $this->path('research/areas/climate-science.php'),
            '<?php return [;',
        );
        $this->writeRecord('data/items/starter-environmental-dataset.php', [
            'slug' => 'starter-environmental-dataset',
            'title' => 'Starter Environmental Dataset',
            'summary' => 'Starter summary.',
            'provider' => 'Example provider',
            'ownership' => 'external',
            'source_url' => 'not-a-url',
            'image' => null,
            'image_alt' => null,
            'homepage_monitoring' => false,
        ]);

        $this->artisan('content:validate')
            ->expectsOutputToContain('research/areas/climate-science.php')
            ->expectsOutputToContain('data/items/starter-environmental-dataset.php')
            ->assertExitCode(1);
    }

    private function copyStarterContent(): void
    {
        $source = base_path('content');
        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($items as $item) {
            if (! $item->isFile() || $item->getExtension() !== 'php') {
                continue;
            }

            $relativePath = substr($item->getPathname(), strlen($source) + 1);
            $target = $this->path($relativePath);
            $directory = dirname($target);

            if (! is_dir($directory)) {
                mkdir($directory, recursive: true);
            }

            copy($item->getPathname(), $target);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    private function writeRecord(string $path, array $record): void
    {
        file_put_contents(
            $this->path($path),
            "<?php\n\nreturn ".var_export($record, true).";\n",
        );
    }

    private function path(string $relativePath): string
    {
        return $this->basePath.DIRECTORY_SEPARATOR.str_replace(
            ['/', '\\'],
            DIRECTORY_SEPARATOR,
            $relativePath,
        );
    }
}
