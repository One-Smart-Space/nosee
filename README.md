# NOSEE Website

The NOSEE website is a server-rendered Laravel application for presenting the Network of Space-Earth Environmentalists' research, data, publications, news, and events.

The current MVP stores editorial content as PHP files under `content/` rather than in a database. Laravel loads and validates those files, repositories select the records needed by each page, Blade components render the interface, and small JavaScript modules add interaction without turning the site into a single-page application.

Only the homepage and newsletter submission endpoint are currently routed. Several links point to sections represented in the content model but do not yet have listing or detail pages.

## Getting started

### Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- Git

### Clone or update the repository

For a fresh checkout:

```bash
git clone <repository-url>
cd nosee
git switch dev
```

For an existing checkout:

```bash
git switch dev
git pull origin dev
```

Install the backend and frontend dependencies:

```bash
composer install
npm install
```

### Configure Laravel

Create the local environment file, application key, and SQLite database.

On macOS or Linux:

```bash
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate
```

On Windows PowerShell:

```powershell
Copy-Item .env.example .env
New-Item database/database.sqlite -ItemType File -Force
php artisan key:generate
php artisan migrate
```

The default environment uses SQLite-backed sessions, cache, and queues, so migrations are part of local setup. You may also set `APP_NAME=NOSEE` and `APP_URL=http://127.0.0.1:8000` in `.env`.

Validate the Git-managed content before starting the site:

```bash
php artisan content:validate
```

### Run the application

Use two terminals during normal frontend development.

Terminal 1 starts Laravel:

```bash
php artisan serve
```

Terminal 2 starts Vite and watches CSS and JavaScript:

```bash
npm run dev
```

Open [http://127.0.0.1:8000](http://127.0.0.1:8000). Port `5173` is Vite's asset server, not the Laravel website.

Alternatively, `composer run dev` starts Laravel, Vite, the queue listener, and the application log together. Use `npm run build` when you need production-ready frontend assets.

## Five-minute architecture overview

The application follows Laravel's request-and-view model, with a service and repository layer between controllers and content files.

```text
Browser request
    -> routes/web.php
    -> controller
    -> content service
    -> repository contract
    -> file-backed repository
    -> FileContentLoader
    -> content/*.php
    -> Blade page and components
    -> HTML response
```

The homepage request works like this:

1. `routes/web.php` sends `GET /` to `HomeController`.
2. `HomeController` asks `HomepageContentService` for homepage-ready data.
3. The service loads the hero from `content/homepage.php` and requests monitoring records, research outputs, news, and events from domain repositories.
4. The repositories use `FileContentLoader`, then filter and order their records.
5. The controller passes arrays to `resources/views/pages/home.blade.php`.
6. The page composes section components inside `layouts/app.blade.php`.
7. The layout adds navigation, the shared footer, and the Vite entries.

Controllers and Blade views do not read files directly. File access stays in the content layer, while views receive data that is ready to render.

## Repository map

| Path | Responsibility |
| --- | --- |
| `app/Contracts/Content/` | Interfaces describing the operations available for each content domain. |
| `app/Repositories/Content/` | File-backed implementations and homepage selection queries. |
| `app/Services/Content/` | File loading, path safety, schemas, validation, and data composition. |
| `app/Http/Controllers/` | Thin HTTP handlers that validate requests or delegate page preparation. |
| `app/Providers/` | Laravel container bindings and shared layout-data registration. |
| `app/View/Components/` | PHP-backed Blade components; navigation uses these for request-aware state. |
| `bootstrap/` | Laravel startup configuration and the list of registered providers. |
| `config/` | Framework configuration sourced from `.env`. |
| `content/` | Git-managed singleton and collection content returned as PHP arrays. |
| `database/` | SQLite file location plus framework migrations, factories, and seeders. |
| `resources/views/` | Blade layouts, pages, reusable UI components, cards, and homepage sections. |
| `resources/css/app.css` | Tailwind entry point, local fonts, semantic tokens, and base styles. |
| `resources/js/` | Vanilla JavaScript for navigation, carousel, newsletter, and homepage motion. |
| `public/media/` | Images and SVG icons referenced by records and components. |
| `routes/web.php` | Current web route definitions. |
| `storage/` | Laravel-generated logs, compiled views, sessions, and cache data. |
| `tests/` | PHPUnit feature/unit tests and Node JavaScript tests. |
| `docs/architecture/` | Detailed structure and naming conventions. |

The root manifests describe the two runtimes: `composer.json` owns PHP dependencies and Composer scripts, `package.json` owns frontend dependencies and npm scripts, and `vite.config.js` connects Laravel, Tailwind, CSS, and JavaScript. `artisan` is Laravel's command-line entry point. Generated dependencies live in `vendor/` and `node_modules/` and should not be edited directly.

## PHP layers in plain language

### Controllers

A controller handles an HTTP request. `HomeController` requests prepared content from `HomepageContentService` and passes it to the homepage view.

`NewsletterSubscriptionController` validates an email address, writes the development subscription to the `stderr` log channel, and returns JSON. It does not persist subscribers or send email yet.

### Services

Services coordinate work that does not belong in a template or route:

- `HomepageContentService` combines the hero singleton with repository-selected records.
- `FooterContentService` loads and validates the footer from `content/site.php`.
- `ContentValidationService` validates all singleton files and collection records.
- `FileContentLoader` is the only class that executes structured content files.
- `ContentPathValidator` prevents invalid slugs, unsafe paths, and traversal outside `content/`.

### Contracts and repositories

`ContentRepository` defines `all`, `find`, `exists`, and `findOrFail`. Domain contracts such as `NewsRepository` and `EventRepository` add queries needed by their sections.

`AbstractFileContentRepository` implements the shared operations. Concrete repositories identify their collection and add focused selection methods:

- `FileDataRepository::homepageMonitoring()` returns homepage monitoring records.
- `FilePublicationRepository::featured(3)` returns featured publications newest first.
- `FileNewsRepository::homepageTrending(4)` places the featured article first, then recent articles.
- `FileEventRepository::featuredUpcoming(3)` returns upcoming featured events by start date.

`ContentRepositoryServiceProvider` tells Laravel which file implementation satisfies each contract. Controllers and services therefore depend on interfaces; a later database implementation can replace the bindings without changing public-facing views.

### Providers and dependency injection

Laravel creates dependencies from constructor type hints. `bootstrap/providers.php` registers:

- `ContentRepositoryServiceProvider`, which binds repositories and configures the content loader.
- `AppServiceProvider`, which uses a view composer to provide validated footer data whenever `layouts.app` renders.

This keeps shared footer loading out of individual controllers.

## How content works

Structured content files return PHP arrays:

```php
<?php

return [
    'slug' => 'example-record',
    'title' => 'Example record',
];
```

Singleton files describe shared or page-level content:

- `content/site.php`
- `content/navigation.php`
- `content/homepage.php`
- `content/about.php`
- `content/research/index.php`

Collections keep one record per file:

- `content/research/areas/`
- `content/data/items/`
- `content/products/items/`
- `content/meetings/`
- `content/publications/`
- `content/events/`
- `content/news/`
- `content/outreach/`

For collection records, the filename and `slug` must match and use kebab-case. Referenced images must exist under `public/`. Dates, URLs, booleans, supported categories, and required homepage record counts are enforced by `ContentSchemaRegistry` and `ContentValidationService`.

The normal content-editing workflow is:

1. Add or update the appropriate PHP file.
2. Add local media under `public/media/` and reference it with a path such as `/media/trending/example.png`.
3. Run `php artisan content:validate`.
4. Review the Git diff before committing.

Editorial content is currently versioned through Git. SQLite supports Laravel's framework services, not the MVP editorial content source.

## Blade layout and component usage

`resources/views/layouts/app.blade.php` is the shared document shell. It owns metadata, Vite assets, the skip link, responsive navigation, `<main>`, and the footer. Pages extend the layout and fill `@yield('content')`.

`resources/views/pages/home.blade.php` stays declarative by composing section components:

```blade
<x-home.hero :items="$heroItems" />
<x-home.monitoring-dashboard :records="$monitoringRecords" />
<x-home.research-outputs :records="$researchOutputs" />
<x-home.trending-news :articles="$trendingArticles" />
<x-home.upcoming-events :events="$upcomingEvents" />
```

The component groups are:

| Component group | Examples | Purpose |
| --- | --- | --- |
| `components/ui/` | `container`, `button`, `arrow-right` | Small layout and interaction primitives. |
| `components/cards/` | Event, news, research-output, and monitoring cards | Reusable record presentation. |
| `components/home/` | Hero and homepage sections | Page-level composition around cards and data. |
| `components/navigation/` | Desktop, mobile, and primary-link views | Responsive navigation presentation. |
| `components/footer/` | `site-footer` | Shared footer and newsletter form. |

Anonymous Blade components declare inputs with `@props`. Dynamic values use the `:` prefix, while attribute bags let callers merge additional classes:

```blade
<x-ui.container class="py-12">
    <x-ui.button href="/data" size="lg" icon-position="right">
        View data

        <x-slot:icon>
            <x-ui.arrow-right />
        </x-slot:icon>
    </x-ui.button>
</x-ui.container>
```

The shared button validates its size, intent, variant, icon position, type, and disabled state. It renders an anchor when `href` is supplied and a native button otherwise.

Navigation is PHP-backed because it needs the current request path. The shared `Navigation` class loads `content/navigation.php`, filters disabled links, calculates `active` and `current` states, and passes the same prepared data to desktop and mobile views.

## Frontend styling and behavior

Tailwind CSS 4 is configured through `resources/css/app.css`. It imports locally bundled Ubuntu Sans and IBM Plex Sans fonts and defines semantic tokens such as `brand`, `surface`, `primary`, and `line-soft`. Components use these names instead of repeating raw colors.

`resources/js/app.js` is the Vite entry point. It loads:

- `navigation-state.js` for shared transparent-to-compact header state.
- `mobile-navigation.js` for the accessible drawer, submenus, focus management, and scroll locking.
- `hero-carousel.js` for desktop autoplay, indicators, mobile swipe, and reduced-motion behavior.
- `newsletter.js` for asynchronous subscription and the confirmation dialog.
- `homepage-animations.js` through a dynamic import only when homepage markers exist.

The page remains useful before JavaScript runs: the first hero item, content, links, and forms are server-rendered. JavaScript locates behavior through `data-*` hooks and progressively enhances that markup.

## Current routes

| Method | Path | Handler | Purpose |
| --- | --- | --- | --- |
| `GET` | `/` | `HomeController` | Renders the homepage. |
| `POST` | `/newsletter/subscribe` | `NewsletterSubscriptionController` | Validates and logs a development subscription. |
| `GET` | `/up` | Laravel health route | Reports whether the application can boot. |

Unmatched routes use `resources/views/errors/404.blade.php` and retain the shared navigation and footer.

## Checks and tests

Run the checks that match the area you changed:

```bash
# Validate Git-managed content
php artisan content:validate

# Run PHP unit and feature tests
php artisan test

# Run vanilla JavaScript tests
npm test

# Check PHP formatting
php vendor/bin/pint --test

# Build production frontend assets
npm run build

# Check the diff for whitespace errors
git diff --check
```

PHP tests use an in-memory SQLite database configured in `phpunit.xml`. JavaScript tests use Node's built-in test runner and do not require a browser.

## Practices used in this codebase

- Thin controllers delegate preparation to services.
- Repository contracts separate data access from storage details.
- Laravel's service container supplies dependencies and repository implementations.
- Blade components provide server-rendered composition without a frontend framework.
- Semantic Tailwind tokens keep colors and typography consistent.
- Progressive enhancement keeps core content usable without JavaScript.
- Content is validated at the file boundary before it reaches views.
- Native controls, semantic HTML, focus states, and reduced-motion behavior support accessibility.
- Short comments explain non-obvious block intent rather than narrating individual statements.

## Conventions and further reading

- Use PascalCase for PHP classes and match class names to filenames.
- Use kebab-case for Blade, JavaScript module, content, and URL filenames.
- Keep direct content-file access inside `FileContentLoader`.
- Reuse existing Blade components and semantic tokens before adding parallel implementations.
- Prefer semantic HTML, native browser behavior, CSS, and small vanilla JavaScript modules.

See [`docs/architecture/project-structure-and-naming.md`](docs/architecture/project-structure-and-naming.md) for the full naming and ownership rules. Product scope and planned milestones are documented in [`docs/PRD.md`](docs/PRD.md) and [`docs/Implementation Plan & Prompts.md`](docs/Implementation%20Plan%20&%20Prompts.md).
