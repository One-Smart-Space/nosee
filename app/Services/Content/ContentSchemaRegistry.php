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
        'lecture',
        'training',
        'meeting',
        'outreach',
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
                'footer' => ['required', 'array'],
                'footer.description' => ['required', 'string'],
                'footer.link_groups' => ['required', 'array', 'size:1'],
                'footer.link_groups.*.heading' => ['required', 'string', 'distinct:strict'],
                'footer.link_groups.*.links' => ['required', 'array', 'min:1'],
                'footer.link_groups.*.links.*.label' => ['required', 'string', 'distinct:strict'],
                'footer.link_groups.*.links.*.url' => ['required', 'string', 'starts_with:/'],
                'footer.link_groups.0.heading' => ['required', 'string', Rule::in(['Quick Links'])],
                'footer.link_groups.0.links' => ['required', 'array', 'size:5'],
                'footer.contact' => ['required', 'array'],
                'footer.contact.heading' => ['required', 'string'],
                'footer.contact.address' => ['required', 'string'],
                'footer.contact.email' => ['required', 'email:rfc'],
                'footer.contact.phone' => ['required', 'string'],
                'footer.social_links' => ['required', 'array', 'size:5'],
                'footer.social_links.*.label' => ['required', 'string', 'distinct:strict'],
                'footer.social_links.*.url' => ['required', 'url'],
                'footer.social_links.*.icon' => [
                    'required',
                    'string',
                    'starts_with:/',
                    $this->existingPublicFileRule(),
                ],
                'footer.legal_links' => ['required', 'array', 'size:2'],
                'footer.legal_links.*.label' => ['required', 'string', 'distinct:strict'],
                'footer.legal_links.*.url' => ['required', 'string', 'starts_with:/'],
                'footer.legal_links.0.label' => ['required', 'string', Rule::in(['Privacy Policy'])],
                'footer.legal_links.0.url' => ['required', 'string', Rule::in(['/privacy'])],
                'footer.legal_links.1.label' => ['required', 'string', Rule::in(['Terms of Use'])],
                'footer.legal_links.1.url' => ['required', 'string', Rule::in(['/terms'])],
                'footer.newsletter' => ['required', 'array'],
                'footer.newsletter.heading' => ['required', 'string'],
                'footer.newsletter.description' => ['required', 'string'],
                'footer.newsletter.label' => ['required', 'string'],
                'footer.newsletter.placeholder' => ['required', 'string'],
                'footer.newsletter.button_label' => ['required', 'string'],
                'footer.newsletter.enabled' => $this->strictBooleanRules(),
                'footer.newsletter.action' => [
                    'present',
                    'nullable',
                    'string',
                    'required_if:footer.newsletter.enabled,true',
                    static function (string $attribute, mixed $value, Closure $fail): void {
                        if (
                            is_string($value)
                            && ! str_starts_with($value, '/')
                            && filter_var($value, FILTER_VALIDATE_URL) === false
                        ) {
                            $fail("The {$attribute} field must be an internal path or valid URL.");
                        }
                    },
                ],
                'footer.newsletter.disabled_text' => ['required', 'string'],
                'footer.support_url' => ['required', 'string', 'starts_with:/'],
                'footer.copyright' => ['required', 'string'],
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
                        'Events',
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
                'utility' => ['required', 'array', 'size:4'],
                'utility.*.label' => [
                    'required',
                    'string',
                    'distinct:strict',
                    Rule::in(['News', 'Multimedia', 'Support NOSEE', 'Login']),
                ],
                'utility.*.url' => ['present', 'nullable', 'string'],
                'utility.*.enabled' => $this->strictBooleanRules(),
                'utility.3.label' => ['required', 'string', Rule::in(['Login'])],
                'utility.3.version' => ['required', 'string', Rule::in(['v1'])],
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
                'page_title' => ['required', 'string'],
                'intro' => ['required', 'array'],
                'intro.eyebrow' => ['required', 'string'],
                'intro.headline' => ['required', 'string'],
                'intro.description_mobile' => ['required', 'string'],
                'intro.description_tablet' => ['required', 'string'],
                'intro.description' => ['required', 'string'],
                'intro.closing_statement_compact' => ['required', 'string'],
                'intro.closing_statement' => ['required', 'string'],
                'intro.image_mobile' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'intro.image_tablet' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'intro.image' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'intro.image_alt' => ['required', 'string'],
                'mission' => ['required', 'array'],
                'mission.title' => ['required', 'string'],
                'mission.statement_mobile' => ['required', 'string'],
                'mission.statement_tablet' => ['required', 'string'],
                'mission.statement' => ['required', 'string'],
                'mission.objectives' => ['required', 'array', 'size:3'],
                'mission.objectives.*' => ['required', 'array'],
                'mission.objectives.*.number' => ['required', 'string', 'distinct:strict'],
                'mission.objectives.*.title' => ['required', 'string'],
                'mission.objectives.*.description_mobile' => ['required', 'string'],
                'mission.objectives.*.description' => ['required', 'string'],
                'quote' => ['required', 'array'],
                'quote.text' => ['required', 'string'],
                'quote.attribution' => ['required', 'string'],
                'quote.image_tablet' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'quote.image' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'quote.image_alt' => ['required', 'string'],
                'story' => ['required', 'array'],
                'story.title' => ['required', 'string'],
                'story.lead' => ['required', 'string'],
                'story.body' => ['required', 'string'],
                'story.image' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'story.image_alt' => ['required', 'string'],
                'leadership' => ['required', 'array'],
                'leadership.title' => ['required', 'string'],
                'leadership.intro' => ['required', 'string'],
                'leadership.people' => ['required', 'array', 'min:1'],
                'leadership.people.*' => ['required', 'array'],
                'leadership.people.*.name' => ['required', 'string'],
                'leadership.people.*.title' => ['required', 'string'],
                'leadership.people.*.image' => ['required', 'string', 'starts_with:/media/about/', $this->existingPublicFileRule()],
                'leadership.people.*.image_alt' => ['required', 'string'],
                'collaboration' => ['required', 'array'],
                'collaboration.eyebrow' => ['required', 'string'],
                'collaboration.title' => ['required', 'string'],
                'collaboration.description' => ['required', 'string'],
                'collaboration.actions' => ['required', 'array', 'size:3'],
                'collaboration.actions.*' => ['required', 'array'],
                'collaboration.actions.*.label' => ['required', 'string', 'distinct:strict'],
                'collaboration.actions.*.url' => ['required', 'string', 'starts_with:/'],
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
                'featured' => $this->strictBooleanRules(),
                'image' => ['present', 'nullable', 'string'],
                'image_alt' => ['present', 'nullable', 'required_with:image', 'string'],
                'timezone' => ['required', 'string', 'timezone'],
                'start_date' => ['required', 'date_format:Y-m-d'],
                'end_date' => ['present', 'nullable', 'date_format:Y-m-d'],
                'schedule' => ['required', 'array'],
                'schedule.mode' => ['required', 'string', Rule::in(['single_day', 'multi_day'])],
                'schedule.start_time' => ['present', 'nullable', 'date_format:H:i'],
                'schedule.end_time' => ['present', 'nullable', 'date_format:H:i'],
                'schedule.itinerary' => ['present', 'array'],
                'schedule.itinerary.*' => ['required', 'array'],
                'schedule.itinerary.*.date' => ['required', 'date_format:Y-m-d'],
                'schedule.itinerary.*.start_time' => ['required', 'date_format:H:i'],
                'schedule.itinerary.*.end_time' => ['present', 'nullable', 'date_format:H:i'],
                'schedule.itinerary.*.title' => ['present', 'nullable', 'string'],
                'schedule.itinerary.*.description' => ['present', 'nullable', 'string'],
                'location' => ['required', 'array'],
                'location.type' => ['required', 'string', Rule::in(['physical', 'online', 'hybrid'])],
                'location.venue' => ['present', 'nullable', 'required_if:location.type,physical,hybrid', 'string'],
                'location.address' => ['present', 'nullable', 'string'],
                'location.city' => ['present', 'nullable', 'required_if:location.type,physical,hybrid', 'string'],
                'location.country' => ['present', 'nullable', 'required_if:location.type,physical,hybrid', 'string'],
                'location.platform' => ['present', 'nullable', 'required_if:location.type,online,hybrid', 'string'],
                'application_deadline' => ['present', 'nullable', 'date_format:Y-m-d\TH:i:sP'],
                'meeting_site_url' => [
                    'present',
                    'nullable',
                    'required_if:type,meeting',
                    $this->httpsUrlRule(),
                ],
                'organiser' => ['present', 'nullable', 'required_unless:type,meeting', 'string'],
                'speakers' => ['present', 'array'],
                'speakers.*' => ['required', 'string'],
                'registration_url' => ['present', 'nullable', 'url'],
                'body' => ['present', 'nullable', 'required_unless:type,meeting', 'string'],
                'resources' => ['present', 'array'],
                'resources.*' => ['required', 'array'],
                'resources.*.label' => ['required', 'string'],
                'resources.*.url' => ['required', 'url'],
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

    private function httpsUrlRule(): Closure
    {
        return static function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null) {
                return;
            }

            if (
                ! is_string($value)
                || filter_var($value, FILTER_VALIDATE_URL) === false
                || parse_url($value, PHP_URL_SCHEME) !== 'https'
                || ! is_string(parse_url($value, PHP_URL_HOST))
            ) {
                $fail("The {$attribute} field must be a valid HTTPS URL.");
            }
        };
    }
}
