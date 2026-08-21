<?php

declare(strict_types=1);

namespace App\Services\Content;

use App\Exceptions\InvalidContentFileException;
use App\Exceptions\InvalidContentPathException;
use DateTimeImmutable;
use Illuminate\Contracts\Validation\Factory;

final class ContentValidationService
{
    public function __construct(
        private readonly FileContentLoader $loader,
        private readonly ContentSchemaRegistry $schemas,
        private readonly Factory $validator,
        private readonly ContentPathValidator $pathValidator,
    ) {}

    /**
     * @return array{files: int, errors: array<string, list<string>>}
     */
    public function validateAll(): array
    {
        $files = 0;
        $errors = [];
        $homepageMonitoringRecords = 0;
        $featuredPublications = 0;
        $featuredNewsRecords = 0;
        $featuredUpcomingEvents = 0;
        $eventRecords = 0;
        $today = (new DateTimeImmutable('today'))->format('Y-m-d');

        foreach ($this->schemas->singletons() as $path => $schema) {
            $files++;
            $this->validateFile($path, $schema, null, $errors);
        }

        foreach ($this->schemas->collections() as $collection => $schema) {
            try {
                $collectionFiles = $this->loader->collectionFiles($collection);
            } catch (InvalidContentPathException $exception) {
                $errors[$collection][] = $exception->getMessage();

                continue;
            }

            foreach ($collectionFiles as $path) {
                $files++;

                if ($collection === 'events') {
                    $eventRecords++;
                }

                $content = $this->validateFile(
                    $path,
                    $schema,
                    pathinfo($path, PATHINFO_FILENAME),
                    $errors,
                );

                if ($collection === 'data/items' && ($content['homepage_monitoring'] ?? null) === true) {
                    $homepageMonitoringRecords++;
                }

                if ($collection === 'publications' && ($content['featured'] ?? null) === true) {
                    $featuredPublications++;
                }

                if ($collection === 'news' && ($content['featured'] ?? null) === true) {
                    $featuredNewsRecords++;
                }

                if ($collection === 'events' && ($content['featured'] ?? null) === true) {
                    if (($content['end_date'] ?? $content['start_date'] ?? '') >= $today) {
                        $featuredUpcomingEvents++;
                    } else {
                        $errors[$path][] = 'Featured events must not have passed.';
                    }
                }
            }
        }

        // Keep the homepage selection deterministic without coupling it to file names.
        if ($homepageMonitoringRecords !== 3) {
            $errors['data/items'][] = 'Exactly three data records must enable homepage monitoring.';
        }

        if ($featuredPublications !== 3) {
            $errors['publications'][] = 'Exactly three publication records must be featured.';
        }

        if ($featuredNewsRecords !== 1) {
            $errors['news'][] = 'Exactly one news record must be featured.';
        }

        if ($eventRecords !== 9) {
            $errors['events'][] = 'Exactly nine event records are required.';
        }

        if ($featuredUpcomingEvents !== 3) {
            $errors['events'][] = 'Exactly three upcoming event records must be featured.';
        }

        return ['files' => $files, 'errors' => $errors];
    }

    /**
     * @param  array<string, mixed>  $content
     * @return list<string>
     */
    public function validateRecord(string $schema, array $content, ?string $expectedSlug = null): array
    {
        $validation = $this->validator->make($content, $this->schemas->rules($schema));
        $errors = $validation->errors()->all();

        if ($schema === ContentSchemaRegistry::EVENT) {
            array_push($errors, ...$this->validateEventRecord($content));
        }

        $slug = $content['slug'] ?? null;

        if (is_string($slug)) {
            try {
                $this->pathValidator->assertValidSlug($slug);
            } catch (InvalidContentPathException $exception) {
                $errors[] = 'slug: '.$exception->getMessage();
            }

            if ($expectedSlug !== null && $slug !== $expectedSlug) {
                $errors[] = "slug: The value must match filename [{$expectedSlug}.php].";
            }
        }

        return array_values($errors);
    }

    /**
     * @param  array<string, mixed>  $content
     * @return list<string>
     */
    private function validateEventRecord(array $content): array
    {
        $errors = [];
        $isMeeting = ($content['type'] ?? null) === 'meeting';
        $schedule = $content['schedule'] ?? null;
        $mode = is_array($schedule) ? ($schedule['mode'] ?? null) : null;
        $startDate = $this->date($content['start_date'] ?? null);
        $endDate = $this->date($content['end_date'] ?? null);

        if (! $isMeeting && ($content['meeting_site_url'] ?? null) !== null) {
            $errors[] = 'meeting site url: Regular events must not define a meeting site URL.';
        }

        if (! is_array($schedule) || ! in_array($mode, ['single_day', 'multi_day'], true)) {
            return $errors;
        }

        $startTime = $schedule['start_time'] ?? null;
        $endTime = $schedule['end_time'] ?? null;
        $itinerary = $schedule['itinerary'] ?? null;

        if ($mode === 'single_day') {
            if (($content['end_date'] ?? null) !== null) {
                $errors[] = 'end date: Single-day events must have a null end date.';
            }

            if (! $isMeeting && ! $this->isTime($startTime)) {
                $errors[] = 'schedule start time: Single-day regular events require a start time.';
            }

            if (is_array($itinerary) && $itinerary !== []) {
                $errors[] = 'schedule itinerary: Single-day events must have an empty itinerary.';
            }

            if ($this->isTime($startTime) && $this->isTime($endTime) && $endTime <= $startTime) {
                $errors[] = 'schedule end time: The end time must be later than the start time.';
            }

            return $errors;
        }

        if ($startDate !== null && ($endDate === null || $endDate <= $startDate)) {
            $errors[] = 'end date: Multi-day events require an end date later than the start date.';
        }

        if ($isMeeting) {
            return $errors;
        }

        if ($startTime !== null || $endTime !== null) {
            $errors[] = 'schedule time: Multi-day regular events must use null top-level times.';
        }

        if ($startDate === null || $endDate === null || $endDate <= $startDate || ! is_array($itinerary)) {
            return $errors;
        }

        $expectedDates = [];

        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            $expectedDates[] = $date->format('Y-m-d');
        }

        $actualDates = array_map(
            static fn (mixed $entry): mixed => is_array($entry) ? ($entry['date'] ?? null) : null,
            $itinerary,
        );

        if ($actualDates !== $expectedDates) {
            $errors[] = 'schedule itinerary: Dates must be unique, ordered, and exactly cover the event date range.';
        }

        foreach ($itinerary as $index => $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $entryStart = $entry['start_time'] ?? null;
            $entryEnd = $entry['end_time'] ?? null;

            if ($this->isTime($entryStart) && $this->isTime($entryEnd) && $entryEnd <= $entryStart) {
                $errors[] = 'schedule itinerary '.($index + 1).' end time: The end time must be later than the start time.';
            }
        }

        return $errors;
    }

    private function date(mixed $value): ?DateTimeImmutable
    {
        if (! is_string($value)) {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        return $date !== false && $date->format('Y-m-d') === $value ? $date : null;
    }

    private function isTime(mixed $value): bool
    {
        return is_string($value) && preg_match('/\A(?:[01]\d|2[0-3]):[0-5]\d\z/D', $value) === 1;
    }

    /**
     * @param  array<string, list<string>>  $errors
     * @return array<string, mixed>|null
     */
    private function validateFile(
        string $path,
        string $schema,
        ?string $expectedSlug,
        array &$errors,
    ): ?array {
        try {
            $content = $this->loader->loadFile($path);
        } catch (InvalidContentFileException|InvalidContentPathException $exception) {
            $errors[$path][] = $exception->getMessage();

            return null;
        }

        $messages = $this->validateRecord($schema, $content, $expectedSlug);

        if ($messages !== []) {
            $errors[$path] = $messages;
        }

        return $content;
    }
}
