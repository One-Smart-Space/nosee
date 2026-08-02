# NOSEE Website Redesign — Product Requirements Document

**Project:** NOSEE Website Redesign  
**Organisation:** NOSEE — Network of Space-Earth Environmentalists  
**Delivery team:** One Smart Space  
**Release:** MVP, with V1 preparation  
**Technology:** Laravel, Blade, Tailwind CSS  
**Last updated:** 29 July 2026

---

## 1. Product overview

One Smart Space will design and build a modern, lightweight public website for NOSEE.

The new website will replace NOSEE’s current outdated website while preserving and improving its useful public information and functionality.

The website will be delivered in at least two stages:

- **MVP:** A complete public-facing website with no admin dashboard. Content will be updated manually through structured files in the codebase and deployed through Git.
- **V1:** The same public website, extended with authentication, an admin dashboard, database-backed content, publishing workflows, and editorial tools.

The MVP must not feel like a placeholder or downgrade. It should serve as a complete organisational website while establishing a technical foundation that can evolve into V1 without a major frontend rebuild.

---

## 2. Product goal

Build a modern digital platform that presents NOSEE as a credible network for researchers, professionals, students, institutions, and communities working across space science, Earth science, climate science, environmental science, and related fields.

The website should:

- Explain NOSEE’s mission, identity, leadership, and collaborations.
- Present the organisation’s research areas and scientific activities.
- Surface current publications, news, meetings, events, outreach activities, data, and products.
- Provide access to useful Earth–space environmental information.
- Support participation, collaboration, institutional partnerships, and financial support.
- Load reliably on slow mobile networks and low-powered devices.
- Remain easy to maintain during the MVP phase.
- Transition cleanly into an admin-managed V1.

---

## 3. Product principles

### 3.1 Complete public experience

The MVP should preserve the useful public scope of the existing website while improving its structure, presentation, accessibility, and performance.

### 3.2 Content-driven architecture

Blade templates and page components must consume structured content rather than contain hard-coded editorial copy.

### 3.3 Manual content management for MVP

Content editors or developers will update PHP and Markdown files, commit changes, and deploy the website.

### 3.4 Minimal JavaScript

The site should render usable HTML on the server. JavaScript should only be used for interactions that require browser state.

### 3.5 Performance first

The website should remain usable on slow 3G networks, unreliable connections, and low-powered mobile devices.

### 3.6 V1-ready architecture

Controllers and Blade templates should consume content through repository interfaces so file-based storage can later be replaced by database-backed storage.

---

## 4. Target audiences

Primary audiences include:

- Space, Earth, atmospheric, climate, and environmental researchers.
- University faculty and postgraduate students.
- Early-career researchers.
- Government agencies.
- Research institutions.
- Scientific collaborators.
- Event participants and speakers.
- Schools and communities reached through outreach.
- Sponsors, donors, and institutional supporters.
- Members and prospective members of NOSEE.

---

## 5. Release scope

## 5.1 MVP

The MVP includes:

- Complete public-facing website.
- Two-tier desktop navigation.
- Responsive mobile navigation.
- Homepage with featured organisational content.
- About Us page.
- Research landing page.
- Individual research-area pages.
- Data page.
- Products page.
- Meetings page.
- Publications page.
- Outreach page.
- News listing and detail pages.
- Events listing and detail pages.
- Support page.
- Contact page.
- Privacy Policy.
- Terms of Use.
- File-based content management.
- Reusable Blade layouts and components.
- Responsive image handling.
- Server-rendered HTML.
- Page, asset, and content caching.
- Search-engine metadata.
- Sitemap and robots configuration.
- Accessibility and performance optimisation.

The MVP excludes:

- Admin dashboard.
- User accounts.
- Member login.
- Role-based access.
- Database-backed editorial content.
- Member-managed profiles.
- Member-submitted publications.
- Automated DOI, Crossref, or ORCID ingestion.
- Internal scientific data-processing pipelines.
- Advanced custom scientific visualisations.
- On-site payment processing unless an approved external provider is supplied.
- Complex site-wide search unless content volume requires it.

## 5.2 V1

V1 is expected to introduce:

- Login and authenticated admin dashboard.
- Database-backed content.
- Role-based access.
- Draft, preview, publish, archive, and revision workflows.
- News, events, publications, research, meetings, outreach, data, and product management.
- Asset and media management.
- Internal data and product pages.
- Selected scientific visualisations.
- Scheduled external data ingestion.
- Automated or assisted publication imports.
- Admin-side content validation.
- Audit history.

---

## 6. Technology stack

The website will use:

- **Laravel** for application architecture, routing, controllers, content loading, validation, caching, scheduled tasks, and future admin functionality.
- **Blade** for server-side rendering and reusable interface components.
- **Tailwind CSS** for layouts, responsive design, semantic design tokens, and interface states.
- **Vite** for asset compilation, optimisation, and versioning.
- **PHP files** for structured MVP content.
- **Markdown** for long-form editorial content.
- **Small vanilla JavaScript modules** for mobile navigation, dropdowns, and hero controls.
- **A CDN** for cached page and static-asset delivery.

React, Vue, Livewire, or Alpine should not be added unless a specific requirement justifies them.

---

## 7. Proposed information architecture

```text
/
├── /about
├── /research
│   ├── /research/atmosphere-and-air-quality
│   ├── /research/climate-science
│   ├── /research/earth-and-space-informatics
│   ├── /research/energy-resources-and-environment
│   └── /research/space-weather
├── /data
│   └── /data/[slug]                 # Optional in MVP
├── /products
│   └── /products/[slug]             # Optional in MVP
├── /meetings
│   └── /meetings/[slug]             # Optional in MVP
├── /publications
│   └── /publications/[slug]         # Optional detail pages
├── /outreach
│   └── /outreach/[slug]             # Optional programme pages
├── /news
│   └── /news/[slug]
├── /events
│   └── /events/[slug]
├── /support
├── /contact
├── /privacy
├── /terms
└── /login                           # V1
```

---

## 8. Navigation

## 8.1 Utility navigation

The upper navigation tier will contain:

- NOSEE logo.
- Home.
- News.
- Events.
- Support.
- Login — hidden until V1.

The logo links to the homepage.

## 8.2 Primary navigation

The lower navigation tier will contain:

- About Us.
- Research.
- Data.
- Products.
- Meetings.
- Publications.
- Outreach.

**Data and Products are separate navigation items and separate sections.**

**Meetings remains labelled “Meetings.”**

## 8.3 Dropdowns

### About Us dropdown

- Mission.
- Leadership.
- Collaborations.

For the MVP, these may link to anchored sections on `/about`.

### Research dropdown

- Atmosphere and Air Quality.
- Climate Science.
- Earth and Space Informatics.
- Energy, Resources and Environment.
- Space Weather.

The parent label remains a link to the Research landing page.

## 8.4 Mobile navigation

The mobile header will use a menu button and navigation drawer.

For navigation items with child pages:

- Selecting the text opens the parent page.
- Selecting the separate caret expands or collapses the submenu.
- The caret must be a real button.
- The button must use `aria-expanded`.
- Dropdowns must not depend on hover.
- Support remains visually prominent.
- Login remains hidden until V1.

---

## 9. Homepage

The homepage is the primary entry point and should establish NOSEE’s identity, surface current information, and direct visitors into deeper sections.

Recommended section order:

1. Header and navigation.
2. Hero.
3. Earth–Space Environment Monitoring.
4. Research Outputs.
5. What’s Trending.
6. Upcoming Events.
7. Footer.

---

## 9.1 Hero

The hero presents one or more featured announcements.

Each hero item may contain:

- Optional image.
- Optional image credit.
- Optional eyebrow.
- Required title.
- Optional subtitle or supporting text.
- Optional call-to-action label.
- Optional call-to-action destination.

Required behaviour:

- The first item renders immediately.
- The first hero image may be prioritised.
- Additional images are lazy-loaded.
- The hero must not autoplay.
- Previous and next controls appear only when multiple items exist.
- Pagination indicators show the active item.
- Swipe may be supported on touch devices.
- Keyboard interaction is required.
- Visible focus states are required.
- The layout must work correctly with one item.
- Text must remain readable if the image fails to load.

---

## 9.2 Earth–Space Environment Monitoring

This section will feature three scientific monitoring resources or datasets.

Each card may contain:

- Name.
- Short description.
- Thumbnail.
- Provider.
- Source attribution.
- Optional update frequency.
- Optional last-updated information.
- Link to the original source.

MVP behaviour:

- Cards lead to the original external source.
- NOSEE must not imply ownership of third-party data.
- The section links to `/data`.

Desktop interaction:

- Three equal-width cards.
- Hovering or focusing a card expands it.
- The other cards contract proportionally.
- The interaction should use CSS where possible.
- Keyboard focus should provide an equivalent state.
- Reduced-motion preferences must be respected.

Mobile interaction:

- Cards stack vertically.
- Expansion is removed.
- Each card has a clear link.

V1 may introduce internal NOSEE pages with visualisations, summaries, processing, and interpretation.

---

## 9.3 Research Outputs

This section will present three recent or featured research outputs from NOSEE members and research groups.

Supported output types may include:

- Journal articles.
- Conference papers.
- Books.
- Book chapters.
- Datasets.
- Technical reports.
- Policy briefs.
- Posters.
- Theses.
- Software.

Each item may display:

- Output type.
- Title.
- Authors.
- Publication date.
- Journal, publisher, or repository.
- Research area.
- DOI or external link.

Featured outputs should appear first. When no featured outputs are defined, items should be sorted by publication date.

The section links to `/publications`.

---

## 9.4 What’s Trending

This section is the homepage presentation of News.

Desktop layout:

- One large featured story occupying approximately half the section.
- Three smaller story cards.
- One “View All News” tile.
- The view-all tile uses a stronger hover and focus treatment.

The featured story is manually selected.

Mobile layout:

- Featured story first.
- Remaining stories stacked vertically.
- View-all action last.
- Desktop bento proportions are not retained.
- Images use stable aspect ratios.

---

## 9.5 Upcoming Events

This section will display the nearest upcoming events, talks, workshops, meetings, application deadlines, and related opportunities.

Each item may contain:

- Date.
- Event type.
- Title.
- Short description.
- Venue or online status.
- Registration deadline.
- Registration or detail link.

Items should be sorted automatically by start date. Past events must not appear in the upcoming section.

The section links to `/events`.

---

## 9.6 Footer

The footer will contain:

- NOSEE logo.
- Short organisation description.
- Contact details.
- Social links.
- Support button.
- Newsletter subscription.
- Quick links.
- Privacy Policy.
- Terms of Use.
- Copyright information.

The newsletter form must only show a success state when connected to a functioning provider.

---

## 10. Page requirements

## 10.1 About Us

The About page should establish NOSEE’s identity, purpose, leadership, history, and credibility.

Proposed sections:

1. Organisation overview.
2. Mission and objectives.
3. History or background.
4. Leadership.
5. Collaborations and institutional partners.
6. Participation call to action.
7. Support or contact call to action.

Mission, Leadership, and Collaborations should remain on one page for the MVP unless the available content becomes too large.

---

## 10.2 Research

The Research landing page explains NOSEE’s overall research agenda.

It should include:

- Research overview.
- Research objectives.
- Research-area grid.
- Selected projects.
- Featured research outputs.
- Relevant collaborations.
- Links to Publications, Data, and Products.

Research explains what NOSEE investigates. Publications contains the resulting scholarly outputs.

### Research-area pages

Each research area will use a shared template.

Research areas:

- Atmosphere and Air Quality.
- Climate Science.
- Earth and Space Informatics.
- Energy, Resources and Environment.
- Space Weather.

Each page may contain:

- Overview.
- Key scientific questions.
- Objectives.
- Themes.
- Current and completed projects.
- Researchers or coordinators.
- Related publications.
- Related data.
- Related products.
- Collaborating institutions.

---

## 10.3 Data

The Data page will provide access to scientific measurements, observations, datasets, indices, feeds, and externally sourced monitoring information.

Potential content includes:

- Total Electron Content.
- Kp Index.
- Dst Index.
- Solar activity.
- Ionospheric observations.
- Atmospheric data.
- Air-quality data.
- Climate datasets.

Each item may contain:

- Name.
- Description.
- Category.
- Provider.
- Geographic coverage.
- Update frequency.
- Thumbnail.
- Attribution.
- External source link.
- Optional internal detail-page link.

The Data page should distinguish between:

- Data owned or collected by NOSEE.
- Data processed or interpreted by NOSEE.
- External data curated by NOSEE.

In the MVP, most items may link to their original providers.

---

## 10.4 Products

The Products page will present tools, dashboards, reports, visualisations, software, services, and processed outputs created or maintained by NOSEE.

Potential product types include:

- Scientific dashboards.
- Data visualisation tools.
- Processed datasets.
- Research software.
- Reports.
- Monitoring tools.
- Educational resources.
- Public information services.

Each product may contain:

- Product name.
- Summary.
- Product type.
- Intended audience.
- Status.
- Maintainer.
- Thumbnail.
- Link to use or view the product.
- Related data.
- Related research area.
- Documentation link.

The MVP may contain a smaller product catalogue than the Data catalogue.

---

## 10.5 Meetings

The Meetings page will cover NOSEE’s formal scientific and organisational meetings.

Potential content includes:

- Annual general meetings.
- Scientific meetings.
- Conferences.
- Workshops.
- Seminars.
- Guest lectures.
- Training sessions.
- Meeting reports.
- Presentations.
- Recordings.
- Supporting resources.

Suggested sections:

1. Featured upcoming meeting.
2. Upcoming meetings.
3. Past meetings.
4. Reports and outcomes.
5. Slides, recordings, and resources.

The Events page answers “What is happening and when?” The Meetings page provides deeper information about NOSEE meetings and their outcomes.

---

## 10.6 Publications

The Publications page will be the central catalogue of scholarly outputs produced by NOSEE members and research groups.

Publication types may include:

- Journal articles.
- Conference papers.
- Books.
- Book chapters.
- Technical reports.
- Policy briefs.
- Datasets.
- Posters.
- Theses.
- Software.

The MVP should support lightweight filtering by:

- Publication type.
- Research area.
- Year.

Search is optional unless the initial publication catalogue is large.

---

## 10.7 Outreach

The Outreach page will explain how NOSEE engages audiences beyond specialist research communities.

Potential content includes:

- School engagement.
- Public lectures.
- Community science.
- Environmental-awareness programmes.
- Student programmes.
- Mentorship.
- Science communication.
- Media appearances.
- Policy engagement.
- Partnership programmes.

Suggested sections:

1. Outreach overview.
2. Current programmes.
3. Featured programme.
4. Past activities.
5. Outcomes.
6. Gallery or media.
7. Participation call to action.
8. Partnership contact.

---

## 10.8 News

The News page will contain organisational announcements and editorial updates.

Possible categories:

- Organisation news.
- Research news.
- Member achievements.
- Partnership announcements.
- Funding opportunities.
- Scientific developments.
- Outreach reports.

The listing page should include:

- Featured story.
- Latest stories.
- Category filtering.
- Pagination or “Load More”.
- Individual article pages.

Long-form article bodies should use Markdown.

---

## 10.9 Events

The Events page will be date-driven.

Each event may contain:

- Title.
- Type.
- Summary.
- Start and end dates.
- Time zone.
- Venue or virtual platform.
- Organiser.
- Speakers.
- Registration link.
- Registration deadline.
- Event status.
- Related files or resources.

The page should separate:

- Upcoming events.
- Ongoing events.
- Past events.

Event state should be derived from dates where possible.

---

## 10.10 Support

The Support page will explain why support is needed and what contributions enable.

Possible sections:

- Why support NOSEE.
- Areas where contributions are used.
- Institutional sponsorship.
- Research support.
- Event sponsorship.
- Partnership opportunities.
- Volunteer support.
- In-kind support.
- Donor or partner contact.

On-site payment processing is not required for the MVP.

---

## 10.11 Contact

The Contact page should contain:

- General email address.
- Office address.
- Telephone number.
- Departmental or programme contacts.
- Social channels.
- Partnership enquiries.
- Media enquiries.
- Lightweight contact form, subject to spam protection.

---

## 10.12 Legal pages

The MVP should include:

- Privacy Policy.
- Terms of Use.

Additional policies may be introduced when analytics, payments, accounts, or personal-data collection are implemented.

---

## 11. MVP content workflow

The MVP will use a manual Git-based content workflow similar to the originally proposed Next.js workflow.

### Content-update process

1. Edit or add a PHP or Markdown content file.
2. Add or update associated media.
3. Run content validation locally.
4. Preview the website.
5. Commit changes.
6. Push to the repository.
7. Run automated checks in CI.
8. Deploy the website.
9. Clear or rebuild affected caches.

```text
Edit content
    ↓
Validate
    ↓
Preview
    ↓
Commit and push
    ↓
Automated checks
    ↓
Deploy
    ↓
Clear page cache
    ↓
Updated website
```

---

## 12. Content architecture

Recommended project structure:

```text
app/
├── Contracts/
│   └── Content/
├── Http/
│   └── Controllers/
├── Repositories/
│   └── Content/
├── Services/
└── View/
    └── Components/

content/
├── site.php
├── navigation.php
├── homepage.php
├── about.php
├── research/
│   ├── index.php
│   └── areas/
│       ├── atmosphere-and-air-quality.php
│       ├── climate-science.php
│       ├── earth-and-space-informatics.php
│       ├── energy-resources-and-environment.php
│       └── space-weather.php
├── data/
│   ├── index.php
│   └── items/
├── products/
│   ├── index.php
│   └── items/
├── meetings/
├── publications/
├── events/
├── news/
└── outreach/

resources/
├── css/
├── js/
└── views/
    ├── layouts/
    ├── components/
    ├── pages/
    ├── research/
    ├── data/
    ├── products/
    ├── meetings/
    ├── publications/
    ├── news/
    └── events/
```

Editorial content should not be placed inside Laravel’s `config/` directory.

---

## 13. Content formats

### 13.1 PHP arrays

Use PHP files for structured records such as:

- Hero items.
- Navigation.
- Research metadata.
- Data records.
- Product records.
- Publications.
- Events.
- Meeting metadata.
- People.
- Partners.

Example:

```php
<?php

return [
    'slug' => 'climate-science',
    'title' => 'Climate Science',
    'summary' => 'Research into climate systems, variability, and change.',
    'featured' => true,
    'image' => '/media/research/climate-science.webp',
];
```

### 13.2 Markdown

Use Markdown for long-form content such as:

- News articles.
- Research descriptions.
- Meeting reports.
- Outreach reports.
- Long project descriptions.

### 13.3 Validation

The project should provide a custom validation command:

```bash
php artisan content:validate
```

Validation should check:

- Required fields.
- Duplicate slugs.
- Invalid dates.
- Missing referenced records.
- Missing images.
- Invalid external URLs.
- Unsupported categories.
- Broken relationships.

---

## 14. Repository architecture

Controllers should not read content files directly.

Each content domain should use a repository contract:

```text
ResearchRepository
DataRepository
ProductRepository
PublicationRepository
EventRepository
NewsRepository
MeetingRepository
OutreachRepository
```

MVP implementation:

```text
ResearchRepository
    └── FileResearchRepository
```

V1 implementation:

```text
ResearchRepository
    └── DatabaseResearchRepository
```

Controllers should depend on repository interfaces rather than storage details.

This allows the data source to change without rebuilding Blade templates or public routes.

---

## 15. Routing and controllers

Routes should remain explicit and predictable.

Example:

```php
Route::get('/', HomeController::class)->name('home');

Route::get('/about', AboutController::class)->name('about');

Route::get('/research', [ResearchController::class, 'index'])
    ->name('research.index');

Route::get('/research/{slug}', [ResearchController::class, 'show'])
    ->name('research.show');

Route::get('/data', [DataController::class, 'index'])
    ->name('data.index');

Route::get('/data/{slug}', [DataController::class, 'show'])
    ->name('data.show');

Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');

Route::get('/products/{slug}', [ProductController::class, 'show'])
    ->name('products.show');

Route::get('/meetings', [MeetingController::class, 'index'])
    ->name('meetings.index');
```

The homepage controller should load only the records required by the homepage.

---

## 16. Blade architecture

Blade should be organised around layouts and reusable components.

Recommended structure:

```text
resources/views/
├── layouts/
│   └── app.blade.php
├── components/
│   ├── navigation/
│   ├── home/
│   ├── cards/
│   ├── forms/
│   ├── media/
│   └── ui/
├── pages/
├── research/
├── data/
├── products/
├── meetings/
├── publications/
├── news/
└── events/
```

Example homepage composition:

```blade
<x-home.hero :items="$heroItems" />

<x-home.monitoring :items="$monitoringData" />

<x-home.research-outputs :items="$researchOutputs" />

<x-home.trending :articles="$trendingNews" />

<x-home.upcoming-events :events="$upcomingEvents" />
```

Blade components should focus on presentation. Sorting, filtering, date calculations, and relationships should happen before content reaches the view.

---

## 17. Tailwind and design tokens

The website will use a central semantic design-token system.

Tailwind’s default spacing and typography scales may remain unless the visual design requires changes.

Token categories should include:

- Brand colours.
- Text colours.
- Surface colours.
- Border colours.
- Focus states.
- Status colours.
- Shadows.
- Radii.
- Interactive states.

Initial brand colours:

```text
#13853D
#1E4EC4
```

Their final primary and secondary hierarchy will be determined during visual design.

Example CSS variables:

```css
:root {
    --brand-green: #13853d;
    --brand-blue: #1e4ec4;

    --text-strong: #172033;
    --text-default: #344054;
    --text-muted: #667085;

    --surface-page: #ffffff;
    --surface-subtle: #f7f9fc;
    --surface-raised: #ffffff;

    --border-subtle: #e4e7ec;
    --focus-ring: var(--brand-blue);
}
```

Components should use semantic classes rather than repeated raw colour values.

---

## 18. JavaScript strategy

The public site should use as little JavaScript as possible.

JavaScript may be used for:

- Mobile navigation.
- Dropdown state.
- Hero controls.
- Optional progressive filtering.
- Newsletter feedback.
- Small accessibility enhancements.

JavaScript should not be required for:

- Core page content.
- Research pages.
- Publication cards.
- News cards.
- Data and product listings.
- Monitoring-card expansion.
- Basic navigation.

The monitoring-card interaction should remain CSS-based.

---

## 19. Image handling

Laravel does not provide a complete image pipeline by default, so the project must define one.

Requirements:

- Compress source images before deployment.
- Generate AVIF, WebP, and fallback versions.
- Generate responsive widths.
- Use explicit width and height.
- Preserve stable aspect ratios.
- Lazy-load below-the-fold images.
- Prioritise only critical hero media.
- Use SVG for suitable icons and diagrams.
- Avoid serving desktop-sized images to mobile devices.

A reusable Blade component should generate responsive `<picture>` markup.

---

## 20. Performance requirements

The website must remain usable on slow 3G networks.

### 20.1 Rendering

- Render complete HTML on the server.
- Avoid loading data for routes the user has not opened.
- Avoid full-page JavaScript hydration.
- Avoid heavy animation libraries.
- Avoid autoplay media.
- Keep the homepage controller limited to homepage content.

### 20.2 Caching

Use layered caching:

- Parsed content-file cache.
- Repository-query cache.
- Homepage collection cache.
- Blade view cache.
- Route cache.
- Configuration cache.
- Full-page cache.
- CDN cache.
- Long-lived immutable asset cache.

### 20.3 Static assets

Vite should produce versioned filenames for CSS and JavaScript.

### 20.4 Cache invalidation

Deployments should clear or rebuild relevant page and content caches.

Visitors should receive updated content after deployment while unchanged assets remain cached.

### 20.5 External data

External scientific data should be fetched server-side, validated, normalised, cached, and served to visitors from the application cache.

Visitors should not normally fetch third-party scientific data directly from their browsers.

---

## 21. Accessibility requirements

The MVP should target WCAG 2.2 AA practices.

Minimum requirements:

- Keyboard navigation.
- Logical heading hierarchy.
- Visible focus states.
- Accessible names for icon buttons.
- Sufficient colour contrast.
- Reduced-motion support.
- Alternative text for meaningful images.
- Empty alternative text for decorative images.
- Form labels and error messages.
- Accessible dropdown states.
- Accessible mobile-menu states.
- No interaction that depends only on hover.
- Adequate mobile touch targets.
- Skip-to-content link.
- Correct landmarks.
- Semantic HTML.

---

## 22. SEO and discoverability

The MVP should include:

- Unique page titles.
- Unique page descriptions.
- Canonical URLs.
- Open Graph metadata.
- Social-sharing images.
- Structured data where useful.
- XML sitemap.
- Robots configuration.
- Descriptive URLs.
- Correct heading structure.
- News article metadata.
- Event metadata.
- Publication metadata where practical.
- Redirects from important legacy URLs.

---

## 23. Analytics and measurement

Analytics should be privacy-conscious and approved by NOSEE.

Useful tracked events may include:

- Support-button clicks.
- Newsletter submissions.
- Data-source clicks.
- Product clicks.
- DOI clicks.
- Event-registration clicks.
- Research-area visits.
- Contact-form submissions.
- External partner-link clicks.

The site should avoid collecting unnecessary personal information.

---

## 24. Success criteria

The MVP is successful when:

- It improves on the existing website without reducing its public scope.
- Visitors can understand NOSEE’s purpose from the homepage.
- Visitors can find Research, Data, Products, Meetings, Publications, Outreach, News, Events, Support, and Contact information.
- Data and product ownership or attribution is clear.
- Editors can add or update content without editing Blade templates.
- New records automatically appear in the correct listings.
- The website remains usable on mobile and slow networks.
- Public pages can later consume database-backed content without a major rewrite.
- The site passes agreed accessibility, performance, and build checks.

Recommended launch targets:

- No critical accessibility violations.
- No broken internal links.
- No horizontal overflow.
- Stable layouts.
- Core content remains usable without JavaScript.
- Main pages meet agreed mobile performance budgets.
- Content validation passes before deployment.

---

## 25. Dependencies

NOSEE must provide or approve:

- Official organisation name and acronym.
- Logo and brand assets.
- Final brand-colour hierarchy.
- Organisation overview.
- History.
- Mission and objectives.
- Leadership information.
- Partner and collaborator details.
- Research-area content.
- Publication records.
- Data sources and attribution rules.
- Product records.
- Meeting archives.
- News and event archives.
- Outreach programmes.
- Support instructions.
- Contact details.
- Social-media accounts.
- Privacy and terms content.
- Newsletter provider.
- Analytics preference.
- Legacy URLs requiring redirects.

---

## 26. Open decisions

1. Confirm the official acronym used publicly.
2. Confirm the initial three monitoring datasets shown on the homepage.
3. Confirm whether Data detail pages are included in the MVP.
4. Confirm whether Product detail pages are included in the MVP.
5. Confirm whether publication detail pages are included in the MVP.
6. Confirm whether event registration is external.
7. Confirm the newsletter provider.
8. Confirm the support or donation process.
9. Confirm the analytics platform.
10. Confirm the hosting provider.
11. Confirm the full-page caching strategy.
12. Confirm the image-processing tool.
13. Confirm which brand colour is primary.
14. Confirm which existing URLs require redirects.

---

## 27. Future considerations

Potential later releases may include:

- Member directory.
- Member profiles.
- Publication submissions.
- Research-group dashboards.
- DOI and ORCID synchronisation.
- Custom scientific visualisations.
- Downloadable datasets.
- Site-wide search.
- Event registration.
- Attendance management.
- Membership applications.
- Donation processing.
- Private member resources.
- Saved content.
- Personalised dashboards.
- Public APIs.

---

## 28. Delivery ownership

### One Smart Space

One Smart Space is responsible for:

- Product definition.
- Information architecture.
- UX and UI design.
- Design-system implementation.
- Laravel architecture.
- Blade implementation.
- Tailwind implementation.
- Content architecture.
- Performance optimisation.
- Accessibility implementation.
- Deployment setup.
- Technical preparation for V1.

### NOSEE

NOSEE is responsible for:

- Content accuracy.
- Scientific validation.
- Brand approval.
- Attribution approval.
- Legal and policy content.
- Leadership information.
- Organisational information.
- Approval of external services.
- Approval of integrations.
