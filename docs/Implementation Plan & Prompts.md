# NOSEE Website Implementation Plan

Building will be completed with ChatGPT Codex.

Each Codex prompt should:

- Focus on one coherent deliverable.
- Preserve all previously completed work.
- Avoid unnecessary refactoring outside its scope.
- Follow the Laravel, Blade, Tailwind, and file-based content architecture.
- Use the Figma design as the visual reference.
- Use the PRD as the functional and information-architecture reference.
- Run relevant tests, formatting, and build commands before completion.
- Leave the application in a working state.

---

## Milestone 1: Project Structure and Content Architecture

### Prompt 1.1 — Establish the Laravel Project Structure and Naming Conventions

Create the agreed Laravel directory structure for controllers, repositories, contracts, services, Blade components, page views, and the root-level content directory.

### Prompt 1.2 — Implement the File-Based Content Repository Foundation

Create the repository contracts, file-based repository implementations, content-loading utilities, service-provider bindings, and error handling required for the MVP content architecture.

### Prompt 1.3 — Add Starter Content Schemas and Content Validation

Create the initial PHP content schemas, sample content records, shared validation rules, and the `php artisan content:validate` command.

---

## Milestone 2: Design Tokens and Global Styles

### Prompt 2.1 — Implement the Semantic Design Token System

Configure the brand colours, semantic text colours, surfaces, borders, shadows, radii, focus states, and other reusable CSS variables through Tailwind.

### Prompt 2.2 — Configure Fonts, Global Styles, and Layout Primitives

Set up the approved font families, typography defaults, responsive content containers, body styles, links, buttons, focus treatments, and shared layout utilities.

---

## Milestone 3: Navigation — Desktop and Mobile

### Prompt 3.1 — Build the Two-Tier Desktop Navigation

Implement the desktop utility navigation and primary navigation based on the Figma design.

The primary navigation must contain separate Data and Products items, while Meetings remains unchanged.

### Prompt 3.2 — Build the Accessible Mobile Navigation and Submenus

Implement the responsive mobile header, navigation drawer, submenu controls, keyboard behaviour, focus handling, and accessible state attributes.

### Prompt 3.3 — Integrate Navigation Content, Active States, and Responsive Behaviour

Connect the navigation to structured content files, add route-aware active states, implement dropdown content, and verify responsive transitions.

---

## Milestone 4: Homepage Hero

### Prompt 4.1 — Build the Responsive Homepage Hero from the Figma Design

Implement the hero layout, background image treatment, content positioning, eyebrow, headline, supporting copy, action button, image credit, and responsive behaviour.

### Prompt 4.2 — Add File-Driven Hero Content and Manual Slide Controls

Connect the hero to structured content files and add support for multiple slides, manual controls, pagination indicators, keyboard navigation, and touch interaction.

The hero must not autoplay.

---

## Milestone 5: Monitoring Dashboard

### Prompt 5.1 — Create the Monitoring Data Schema and Reusable Dashboard Card

Create the monitoring-data content model and the reusable Blade component for dashboard cards, including title, summary, image, provider, attribution, and source link.

### Prompt 5.2 — Build the Responsive Monitoring Dashboard Interaction

Implement the three-card homepage section, desktop card expansion, keyboard-equivalent focus state, stacked mobile layout, reduced-motion support, and Data-page action.

---

## Milestone 6: Research Outputs

### Prompt 6.1 — Create the Publication Content Model and Research Output Card

Create the publication schema, publication repository methods, sample records, and reusable research-output Blade component.

### Prompt 6.2 — Build the Homepage Research Outputs Section

Implement the three-column homepage section, featured and recent publication selection, metadata presentation, external publication links, and responsive behaviour.

---

## Milestone 7: Trending News

### Prompt 7.1 — Create the News Content Model and Trending Card Variants

Create the news content schema, file structure, repository methods, featured-story handling, and reusable large and compact news-card components.

### Prompt 7.2 — Build the Responsive Trending News Layout

Implement the featured story, three secondary stories, View More tile, desktop bento layout, responsive mobile stack, overlays, metadata, and accessible links.

---

## Milestone 8: Upcoming Events

### Prompt 8.1 — Create the Event Content Model and Reusable Event Card

Create the event schema, event repository methods, date-state handling, sample event records, and reusable event-card component.

### Prompt 8.2 — Build Upcoming Event Selection and Homepage Presentation

Implement automatic upcoming-event selection, exclude past events, and reproduce the three-card homepage event section from the Figma design.

---

## Milestone 9: Footer

### Prompt 9.1 — Build the Responsive Footer from the Figma Design

Implement the footer layout, visual hierarchy, responsive column structure, logo area, and mobile behaviour.

### Prompt 9.2 — Integrate Footer Navigation, Contact, Support, Newsletter, Social, and Legal Content

Connect the footer to structured content files and add contact information, support action, newsletter form, quick links, social links, copyright, Privacy Policy, Terms of Use, and accessibility links.

---

## Milestone 10: Remaining Pages

### Prompt 10.1 — Create the Shared Internal Page Foundation

Build the shared internal-page layout, page header, breadcrumbs where appropriate, content container, section-heading patterns, reusable cards, pagination patterns, empty appropriate states, and metadata support.

This prompt should not implement the final content of any individual page.

### Prompt 10.2 — Build the About Us Page

Implement the About Us page with organisation overview, mission, objectives, history, leadership, collaborations, participation call to action, and support or contact action.

### Prompt 10.3 — Build the Research Landing and Research Area Pages

Implement the Research landing page and the shared detail template for:

- Atmosphere and Air Quality.
- Climate Science.
- Earth and Space Informatics.
- Energy, Resources and Environment.
- Space Weather.

Include related publications, data, products, projects, researchers, and collaborators where available.

### Prompt 10.4 — Build the Data Page

Implement the Data listing page, data categories, ownership and attribution labels, provider information, source links, responsive cards, and optional internal detail-page support.

### Prompt 10.5 — Build the Products Page

Implement the Products page for NOSEE tools, dashboards, reports, visualisations, software, services, and processed outputs.

Include product status, maintainer, audience, related research, related data, and external or internal access links.

### Prompt 10.6 — Build the Meetings Page

Implement the Meetings page with featured meetings, upcoming meetings, past meetings, reports, outcomes, presentations, recordings, and supporting resources.

Keep the section and navigation label as Meetings.

### Prompt 10.7 — Build the Publications Page

Implement the full publications catalogue, publication cards, year filtering, research-area filtering, publication-type filtering, empty states, and optional publication detail pages.

### Prompt 10.8 — Build the Outreach Page

Implement the Outreach page with programme overview, current programmes, featured initiative, past activities, outcomes, media, participation action, and partnership contact.

### Prompt 10.9 — Build the News Listing and News Article Pages

Implement the News listing page, featured article, category filtering, pagination, article metadata, and Markdown-driven article detail pages.

### Prompt 10.10 — Build the Events Listing and Event Detail Pages

Implement Upcoming, Ongoing, and Past event groups, event filtering, event metadata, registration actions, and reusable event detail pages.

### Prompt 10.11 — Build the Support Page

Implement the Support page with reasons to support NOSEE, supported activities, research sponsorship, event sponsorship, institutional partnerships, volunteering, in-kind support, and enquiry actions.

### Prompt 10.12 — Build the Contact Page

Implement contact details, programme contacts, social channels, partnership and media enquiries, and an accessible contact form with validation and spam-protection preparation.

### Prompt 10.13 — Build the Privacy Policy Page

Implement the Privacy Policy route and structured legal-page layout using file-managed content.

Include placeholders only where NOSEE has not yet supplied approved legal wording.

### Prompt 10.14 — Build the Terms of Use Page

Implement the Terms of Use route and structured legal-page layout using file-managed content.

Include placeholders only where NOSEE has not yet supplied approved legal wording.

---

## Milestone 11: Performance Optimisation and Accessibility

### Prompt 11.1 — Optimise Images, Assets, JavaScript, and Application Caching

Implement responsive images, image formats, lazy loading, critical-image priority, asset versioning, content caching, page caching, cache invalidation, and JavaScript reduction.

### Prompt 11.2 — Complete the Accessibility and Responsive Behaviour Audit

Audit and fix keyboard navigation, focus states, headings, landmarks, contrast, motion preferences, image alternatives, labels, error messages, touch targets, and responsive layouts.

### Prompt 11.3 — Add SEO Metadata, Sitemap, Structured Data, and Automated Quality Checks

Implement page metadata, canonical URLs, Open Graph data, sitemap generation, robots configuration, relevant structured data, link checking, content validation, and automated quality checks.

---

## Milestone 12: Deployment

### Prompt 12.1 — Prepare the Production Environment and CI/CD Pipeline

Configure production environment requirements, environment variables, dependency installation, automated testing, content validation, asset building, and deployment checks.

### Prompt 12.2 — Configure Hosting, CDN Caching, and Cache Invalidation

Configure the selected hosting platform, public directory, HTTPS, CDN behaviour, browser caching, Laravel caches, deployment cache clearing, and persistent storage requirements.

### Prompt 12.3 — Run Production Smoke Tests and Complete the Launch Checklist

Verify routes, assets, forms, external links, redirects, metadata, responsive layouts, accessibility, caching, logs, error pages, rollback procedures, and final launch readiness.

---

# Prompt Summary

| Milestone | Number of prompts |
|---|---:|
| Milestone 1 | 3 |
| Milestone 2 | 2 |
| Milestone 3 | 3 |
| Milestone 4 | 2 |
| Milestone 5 | 2 |
| Milestone 6 | 2 |
| Milestone 7 | 2 |
| Milestone 8 | 2 |
| Milestone 9 | 2 |
| Milestone 10 | 14 |
| Milestone 11 | 3 |
| Milestone 12 | 3 |
| **Total** | **40 Codex prompts** |