<?php

declare(strict_types=1);

namespace App\Services\Content;

use Closure;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

final class ContentSchemaRegistry
{
    private const EVENT_TYPES = [
        'conference',
        'workshop',
        'seminar',
        'lecture',
        'training',
        'meeting',
        'outreach',
        'deadline',
    ];

    private const NEWS_CATEGORIES = [
        'organisation-news',
        'research-news',
        'member-achievement',
        'partnership',
        'funding-opportunity',
        'scientific-development',
        'outreach-report',
    ];

    private const PUBLICATION_TYPES = [
        'journal-article',
        'conference-paper',
        'book-chapter',
        'report',
    ];

    // Match the five research-area slugs already established by navigation content.
    private const RESEARCH_AREA_SLUGS = [
        'atmosphere-and-air-quality',
        'climate-science',
        'earth-and-space-informatics',
        'energy-resources-and-environment',
        'space-weather',
    ];

    public const ABOUT = 'about';

    public const DATA_ITEM = 'data-item';

    public const EVENT = 'event';

    public const HOMEPAGE = 'homepage';

    public const MEETING = 'meeting';

    public const NAVIGATION = 'navigation';

    public const NEWS = 'news';

    public const OUTREACH = 'outreach';

    public const PRODUCT = 'product';

    public const PUBLICATION = 'publication';

    public const RESEARCH_AREA = 'research-area';

    public const RESEARCH_INDEX = 'research-index';

    public const SITE = 'site';

    /**
     * @return array<string, string>
     */
    public function singletons(): array
    {
        return [
            'site.php' => self::SITE,
            'navigation.php' => self::NAVIGATION,
            'homepage.php' => self::HOMEPAGE,
            'about.php' => self::ABOUT,
            'research/index.php' => self::RESEARCH_INDEX,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function collections(): array
    {
        return [
            'research/areas' => self::RESEARCH_AREA,
            'data/items' => self::DATA_ITEM,
            'products/items' => self::PRODUCT,
            'meetings' => self::MEETING,
            'publications' => self::PUBLICATION,
            'events' => self::EVENT,
            'news' => self::NEWS,
            'outreach' => self::OUTREACH,
        ];
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(string $schema): array
    {
        return match ($schema) {
            self::SITE => [
                'name' => ['required', 'string'],
                'tagline' => ['required', 'string'],
                'description' => ['required', 'string'],
            ],
            self::NAVIGATION => [
                'primary' => ['required', 'array', 'size:6'],
                'primary.*.label' => [
                    'required',
                    'string',
                    'distinct:strict',
                    Rule::in([
                        'About NSEE',
                        'Research',
                        'Data & Products',
                        'Meetings',
                        'Publications',
                        'Outreach',
                    ]),
                ],
                'primary.*.url' => ['required', 'string', 'starts_with:/'],
                'primary.*.enabled' => $this->strictBooleanRules(),
                'primary.*.children' => ['sometimes', 'array', 'min:1'],
                'primary.*.children.*.label' => ['required', 'string'],
                'primary.*.children.*.url' => ['required', 'string', 'starts_with:/'],
                'primary.*.children.*.enabled' => $this->strictBooleanRules(),
                'primary.0.children' => ['required', 'array', 'size:3'],
                'primary.0.children.*.label' => ['required', 'string', Rule::in(['Mission', 'Leadership', 'Collaborations'])],
                'primary.1.children' => ['required', 'array', 'size:5'],
                'primary.1.children.*.label' => [
                    'required',
                    'string',
                    Rule::in([
                        'Atmosphere and Air Quality',
                        'Climate Science',
                        'Earth and Space Informatics',
                        'Energy, Resources and Environment',
                        'Space Weather',
                    ]),
                ],
                'primary.2.children' => ['required', 'array', 'size:2'],
                'primary.2.children.*.label' => ['required', 'string', 'distinct:strict', Rule::in(['Data', 'Products'])],
                'utility' => ['required', 'array', 'size:5'],
                'utility.*.label' => [
                    'required',
                    'string',
                    'distinct:strict',
                    Rule::in(['News', 'Events', 'Multimedia', 'Support NSEE', 'Login']),
                ],
                'utility.*.url' => ['present', 'nullable', 'string'],
                'utility.*.enabled' => $this->strictBooleanRules(),
                'utility.4.label' => ['required', 'string', Rule::in(['Login'])],
                'utility.4.enabled' => [
                    'required',
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        if ($value !== false) {
                            $fail("The {$attribute} field must be false for V1.");
                        }
                    },
                ],
                'utility.4.version' => ['required', 'string', Rule::in(['v1'])],
            ],
            self::HOMEPAGE => [
                'hero' => ['required', 'array'],
                'hero.items' => ['required', 'array', 'min:1'],
                'hero.items.*' => ['required', 'array'],
                'hero.items.*.id' => ['required', 'string', 'distinct:strict'],
                'hero.items.*.eyebrow' => ['required', 'string'],
                'hero.items.*.title' => ['required', 'string'],
                'hero.items.*.summary' => ['required', 'string'],
                'hero.items.*.image' => ['required', 'string', 'starts_with:/'],
                'hero.items.*.image_alt' => ['required', 'string'],
                'hero.items.*.image_credit' => ['required', 'string'],
                'hero.items.*.cta_label' => ['required', 'string'],
                'hero.items.*.cta_url' => ['required', 'string', 'starts_with:/'],
            ],
            self::ABOUT => [
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
                'mission' => ['required', 'string'],
            ],
            self::RESEARCH_INDEX => [
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
            ],
            self::RESEARCH_AREA => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
                'featured' => $this->strictBooleanRules(),
            ],
            self::DATA_ITEM => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
                'provider' => ['required', 'string'],
                'ownership' => ['required', 'string', Rule::in(['nosee', 'processed', 'external'])],
                'source_url' => ['present', 'nullable', 'url'],
                'image' => [
                    'required_if:homepage_monitoring,true',
                    'nullable',
                    'string',
                    'starts_with:/',
                    $this->existingPublicFileRule(),
                ],
                'image_alt' => [
                    'required_if:homepage_monitoring,true',
                    'nullable',
                    'string',
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        if (is_string($value) && trim($value) === '') {
                            $fail("The {$attribute} field must not be empty.");
                        }
                    },
                ],
                'homepage_monitoring' => $this->strictBooleanRules(),
            ],
            self::PRODUCT => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
                'status' => ['required', 'string', Rule::in(['active', 'beta', 'planned', 'archived'])],
                'url' => ['present', 'nullable', 'url'],
            ],
            self::MEETING => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'type' => ['required', 'string'],
                'start_date' => ['required', 'date_format:Y-m-d'],
                'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'venue' => ['required', 'string'],
            ],
            self::PUBLICATION => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'type' => ['required', 'string', Rule::in(self::PUBLICATION_TYPES)],
                'authors' => ['required', 'array', 'min:1'],
                'authors.*' => ['required', 'string'],
                'publication_date' => ['required', 'date_format:Y-m-d'],
                'publication_source' => ['required', 'string'],
                'research_area' => ['required', 'string', Rule::in(self::RESEARCH_AREA_SLUGS)],
                'doi' => [
                    'required',
                    'string',
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        if (! is_string($value) || preg_match('/^10\.\d{4,9}\/[-._;()\/:a-z0-9]+$/i', $value) !== 1) {
                            $fail("The {$attribute} field must be a valid DOI identifier without a URL prefix.");
                        }
                    },
                ],
                'featured' => $this->strictBooleanRules(),
            ],
            self::EVENT => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'type' => ['required', 'string', Rule::in(self::EVENT_TYPES)],
                'summary' => ['required', 'string'],
                'start_date' => ['required', 'date_format:Y-m-d'],
                'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
                'timezone' => ['required', 'string', 'timezone'],
                'venue' => ['required', 'string'],
                'registration_url' => ['present', 'nullable', 'url'],
                'featured' => $this->strictBooleanRules(),
            ],
            self::NEWS => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'excerpt' => ['required', 'string'],
                'category' => ['required', 'string', Rule::in(self::NEWS_CATEGORIES)],
                'published_at' => ['required', 'date_format:Y-m-d\TH:i:sP'],
                'image' => [
                    'required',
                    'string',
                    'starts_with:/media/trending/',
                    $this->existingPublicFileRule(),
                ],
                'image_alt' => [
                    'required',
                    'string',
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        if (is_string($value) && trim($value) === '') {
                            $fail("The {$attribute} field must not be empty.");
                        }
                    },
                ],
                'featured' => $this->strictBooleanRules(),
            ],
            self::OUTREACH => [
                'slug' => ['required', 'string'],
                'title' => ['required', 'string'],
                'summary' => ['required', 'string'],
                'status' => ['required', 'string', Rule::in(['active', 'completed', 'planned'])],
            ],
            default => throw new InvalidArgumentException("Unknown content schema [{$schema}]."),
        };
    }

    /**
     * @return array<int, mixed>
     */
    private function strictBooleanRules(): array
    {
        return [
            'required',
            static function (string $attribute, mixed $value, Closure $fail): void {
                if (! is_bool($value)) {
                    $fail("The {$attribute} field must be a boolean.");
                }
            },
        ];
    }

    private function existingPublicFileRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if (! is_string($value)) {
                return;
            }

            $publicPath = realpath(public_path());
            $resolvedPath = realpath(public_path(ltrim(str_replace('\\', '/', $value), '/')));

            if (
                $publicPath === false
                || $resolvedPath === false
                || ! is_file($resolvedPath)
                || ! str_starts_with($resolvedPath, $publicPath.DIRECTORY_SEPARATOR)
            ) {
                $fail("The {$attribute} field must reference an existing public file.");
            }
        };
    }
}
