<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Content;

use App\Services\Content\ContentSchemaRegistry;
use App\Services\Content\ContentValidationService;
use Tests\TestCase;

class ContentValidationServiceTest extends TestCase
{
    public function test_all_starter_content_passes_validation(): void
    {
        $result = $this->validation()->validateAll();

        $this->assertSame([], $result['errors']);
        $this->assertSame(40, $result['files']);
    }

    public function test_missing_required_field_fails(): void
    {
        $record = $this->researchRecord();
        unset($record['title']);

        $this->assertErrorContains(
            $this->validation()->validateRecord(
                ContentSchemaRegistry::RESEARCH_AREA,
                $record,
                'climate-science',
            ),
            'title',
        );
    }

    public function test_invalid_slug_fails(): void
    {
        $record = $this->researchRecord();
        $record['slug'] = 'Climate_Science';

        $this->assertErrorContains(
            $this->validation()->validateRecord(
                ContentSchemaRegistry::RESEARCH_AREA,
                $record,
                'Climate_Science',
            ),
            'Invalid content slug',
        );
    }

    public function test_filename_and_slug_mismatch_fails(): void
    {
        $record = $this->researchRecord();
        $record['slug'] = 'space-weather';

        $this->assertErrorContains(
            $this->validation()->validateRecord(
                ContentSchemaRegistry::RESEARCH_AREA,
                $record,
                'climate-science',
            ),
            'must match filename',
        );
    }

    public function test_invalid_enum_value_fails(): void
    {
        $this->assertErrorContains(
            $this->validation()->validateRecord(ContentSchemaRegistry::DATA_ITEM, [
                'slug' => 'starter-data',
                'title' => 'Starter Data',
                'summary' => 'Starter summary.',
                'provider' => 'Example provider',
                'ownership' => 'invalid',
                'source_url' => null,
                'image' => null,
                'image_alt' => null,
                'homepage_monitoring' => false,
            ], 'starter-data'),
            'ownership',
        );
    }

    public function test_invalid_url_fails(): void
    {
        $this->assertErrorContains(
            $this->validation()->validateRecord(ContentSchemaRegistry::DATA_ITEM, [
                'slug' => 'starter-data',
                'title' => 'Starter Data',
                'summary' => 'Starter summary.',
                'provider' => 'Example provider',
                'ownership' => 'external',
                'source_url' => 'not-a-url',
                'image' => null,
                'image_alt' => null,
                'homepage_monitoring' => false,
            ], 'starter-data'),
            'source url',
        );
    }

    public function test_empty_publication_authors_fail(): void
    {
        $record = require base_path('content/publications/low-latitude-geomagnetic-storm-responses.php');
        $record['authors'] = [];

        $this->assertErrorContains(
            $this->validation()->validateRecord(
                ContentSchemaRegistry::PUBLICATION,
                $record,
                $record['slug'],
            ),
            'authors',
        );
    }

    public function test_boolean_fields_require_actual_booleans(): void
    {
        $record = $this->researchRecord();
        $record['featured'] = 0;

        $this->assertErrorContains(
            $this->validation()->validateRecord(
                ContentSchemaRegistry::RESEARCH_AREA,
                $record,
                'climate-science',
            ),
            'featured',
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function researchRecord(): array
    {
        return [
            'slug' => 'climate-science',
            'title' => 'Climate Science',
            'summary' => 'Starter summary.',
            'featured' => false,
        ];
    }

    /**
     * @param  list<string>  $errors
     */
    private function assertErrorContains(array $errors, string $expected): void
    {
        $matchingErrors = array_filter(
            $errors,
            fn (string $error): bool => str_contains(strtolower($error), strtolower($expected)),
        );

        $this->assertNotEmpty(
            $matchingErrors,
            "Failed asserting that validation errors contain [{$expected}].",
        );
    }

    private function validation(): ContentValidationService
    {
        return $this->app->make(ContentValidationService::class);
    }
}
