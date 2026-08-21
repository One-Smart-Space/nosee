# Project Structure and Naming

This document defines the structure and naming conventions for the NOSEE Laravel application. It describes ownership only; repository contracts, implementations, content schemas, routes, controllers, and views belong to later milestones.

## Directory responsibilities

| Path | Responsibility |
| --- | --- |
| `app/Contracts/Content/` | Contracts for retrieving content without exposing its storage mechanism. |
| `app/Repositories/Content/` | File-backed implementations of content contracts. |
| `app/Services/Content/` | Application-level content selection and composition. |
| `app/Http/Controllers/` | HTTP request handling and delegation to services. |
| `app/View/Components/` | PHP-backed Blade component classes when a component needs server-side behaviour. |
| `content/` | Git-managed PHP and Markdown source content. |
| `resources/views/layouts/` | Shared Blade page shells. |
| `resources/views/components/` | Reusable Blade components grouped by purpose. |
| `resources/views/pages/` | Standalone pages that do not belong to a content section. |
| `resources/views/{section}/` | Listing and detail views for a named site section. |
| `docs/architecture/` | Architecture decisions and project conventions. |

Controllers must not read files directly. File access belongs in repositories, while cross-repository selection or composition belongs in services. Blade views receive prepared data and remain presentation-focused.

## PHP naming

- Follow PSR-4 under the `App` namespace and PSR-12 formatting.
- Use PascalCase singular class names and match each class name to its filename.
- Name content contracts by capability with a `Repository` suffix, such as `PublicationRepository`.
- Prefix file-backed implementations with `File`, such as `FilePublicationRepository`.
- Suffix application services with `Service`, such as `PublicationService`.
- Suffix controllers with `Controller`, such as `PublicationController`.
- Place PHP-backed Blade components under `App\View\Components` and use PascalCase names, such as `PublicationCard`.
- Use camelCase for methods and local variables. Use SCREAMING_SNAKE_CASE only for constants.

## Blade naming

- Use kebab-case filenames: `publication-card.blade.php`.
- Group reusable components by purpose: `components.navigation.primary-nav`, `components.cards.publication-card`, and similar names.
- Render anonymous components with the same dot-separated path: `<x-navigation.primary-nav />`.
- Match PHP-backed component tags to their kebab-case class names: `PublicationCard` becomes `<x-publication-card />` when it is not nested.
- Use `index.blade.php` for section listings and `show.blade.php` for individual records.
- Keep page-specific partials with their page or section; move them into `components/` only when they are genuinely reused.

## Content naming

- Use lowercase kebab-case directory and file names.
- Use the filename stem as the stable content slug, for example `atmosphere-and-air-quality.php`.
- Store structured records as `.php` files that return data; store long-form editorial bodies as `.md` files.
- Keep research areas in `content/research/areas/`, data records in `content/data/items/`, and product records in `content/products/items/`.
- Keep meeting, publication, event, news, and outreach records in their matching top-level content directories.
- Do not encode display order in filenames. Ordering belongs in structured content fields when schemas are introduced.
- Use ISO `YYYY-MM-DD` date strings in filenames only when a date is part of the editorial identity, such as `2026-07-31-example-event.md`.

## Web and asset naming

- Use lowercase kebab-case URL segments and dot-separated Laravel route names, such as `research.areas.show`.
- Preserve the existing `resources/css/app.css` and `resources/js/app.js` Vite entry points.
- Use kebab-case for additional CSS or JavaScript module filenames and camelCase within JavaScript.
- Prefer Blade, Tailwind CSS, and native browser behaviour before adding custom JavaScript.

## Structural rules

- Reuse an existing contract, repository, service, or component before creating another one.
- Keep storage-specific behaviour behind content contracts so the later database implementation can replace file-backed repositories without changing controllers or views.
- Do not add an abstraction until a milestone requires its behaviour.

## Commenting convention

- Add short comments above logical blocks when their purpose or interaction is not obvious from names alone.
- Explain intent and boundaries rather than narrating individual statements.
- Keep self-explanatory imports, assignments, markup, and tests free of redundant comments.

## Content repository foundation

`ContentRepository` defines the shared `all`, `find`, `exists`, and `findOrFail` operations. The domain contracts `ResearchRepository`, `DataRepository`, `ProductRepository`, `PublicationRepository`, `EventRepository`, `NewsRepository`, and `OutreachRepository` extend it with domain-specific queries only where needed.

`FileContentLoader` is the only class that reads structured PHP content files. It validates collection paths and slugs, restricts access to the configured content root, requires records to return arrays, and loads collections in filename order. A valid but missing collection is treated as empty; malformed files and invalid paths raise application-specific exceptions.

`AbstractFileContentRepository` implements the shared repository behaviour, derives missing slugs from filenames, and preserves explicit slug values. Each concrete file repository declares only its collection:

| Contract | File repository | Collection |
| --- | --- | --- |
| `ResearchRepository` | `FileResearchRepository` | `research/areas` |
| `DataRepository` | `FileDataRepository` | `data/items` |
| `ProductRepository` | `FileProductRepository` | `products/items` |
| `PublicationRepository` | `FilePublicationRepository` | `publications` |
| `EventRepository` | `FileEventRepository` | `events` |
| `NewsRepository` | `FileNewsRepository` | `news` |
| `OutreachRepository` | `FileOutreachRepository` | `outreach` |

`ContentRepositoryServiceProvider` binds every domain contract to its file implementation and configures the loader to use the root `content/` directory. Controllers will request domain contracts from Laravel's container; replacing those bindings with database implementations later will not require controller or view changes.

Controllers and Blade views must never directly `require` or `include` editorial content files. Controllers consume repository contracts and pass prepared data to views.

## Content schemas and validation

Singleton content lives in `content/site.php`, `content/navigation.php`, `content/homepage.php`, `content/about.php`, and `content/research/index.php`. Collection records remain in the eight domain directories listed above.

`ContentSchemaRegistry` defines the fixed starter rules for every singleton and collection type. `ContentValidationService` uses Laravel validation plus `ContentPathValidator` to enforce required fields, enums, URLs, ISO dates, date ordering, strict booleans, valid slugs, and filename-to-slug consistency. Unknown fields remain allowed for later extension.

`php artisan content:validate` loads every PHP file through `FileContentLoader`, reports errors by relative filename, and never modifies content. Malformed files are reported alongside other validation failures rather than stopping the run.

Content updates follow this workflow:

1. Edit or add a PHP file in the matching singleton or collection location.
2. Keep collection filenames and `slug` values identical and kebab-cased.
3. Run `php artisan content:validate` before committing or deploying.
4. Review the Git diff and commit the content change normally.

Markdown parsing is not part of the current validation flow.
