<?php

declare(strict_types=1);

namespace App\View\Components\Navigation;

use App\Services\Content\FileContentLoader;
use Illuminate\Http\Request;
use Illuminate\View\Component;

abstract class Navigation extends Component
{
    /** @var list<array<string, mixed>> */
    public array $primary;

    /** @var list<array<string, mixed>> */
    public array $utility;

    public bool $homeCurrent;

    public bool $transparent;

    public function __construct(
        FileContentLoader $loader,
        Request $request,
        bool|string $transparent = false,
    ) {
        $this->transparent = match ($transparent) {
            true, 'true' => true,
            false, 'false' => false,
            default => throw new \InvalidArgumentException('Navigation transparent must be a boolean.'),
        };

        // Normalize the request once, then decorate both navigation groups with shared active state.
        $path = $this->normalizePath($request->getPathInfo());
        $navigation = $loader->loadFile('navigation.php');

        $this->homeCurrent = $path === '/';
        $this->primary = $this->withActiveState($navigation['primary'], $path);
        $this->utility = $this->withActiveState($navigation['utility'], $path);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function withActiveState(array $items, string $path): array
    {
        return array_values(array_map(function (array $item) use ($path): array {
            // Match exact and nested paths, then bubble an active child state to its parent.
            $children = isset($item['children'])
                ? $this->withActiveState($item['children'], $path)
                : [];
            $itemPath = $this->internalPath($item['url']);
            $current = $itemPath === $path;
            $active = $current
                || ($itemPath !== null && $itemPath !== '/' && str_starts_with($path, $itemPath.'/'))
                || in_array(true, array_column($children, 'active'), true);

            return [
                ...$item,
                ...($children === [] ? [] : ['children' => $children]),
                'active' => $active,
                'current' => $current,
            ];
        }, array_filter(
            $items,
            static fn (array $item): bool => $item['enabled'] === true,
        )));
    }

    private function internalPath(?string $url): ?string
    {
        // Only root-relative application URLs participate in request-path matching.
        if ($url === null || $url === '' || $url[0] !== '/') {
            return null;
        }

        $parts = parse_url($url);

        if (! is_array($parts) || isset($parts['host']) || ! isset($parts['path'])) {
            return null;
        }

        return $this->normalizePath($parts['path']);
    }

    private function normalizePath(string $path): string
    {
        return '/'.trim($path, '/') ?: '/';
    }
}
