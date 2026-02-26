<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Xslainadmin\LivewireCrud\Navigation\NavigationGroup;
use Xslainadmin\LivewireCrud\Navigation\NavigationItem;
use Xslainadmin\LivewireCrud\Resource\CrudResource;

/**
 * Runtime registry for CrudResource classes.
 *
 * Register your module's Resources in a ServiceProvider's boot() method:
 *
 *   public function boot(): void
 *   {
 *       ResourceRegistry::register([
 *           UserResource::class,
 *           ProductResource::class,
 *       ]);
 *   }
 *
 * The registry is used to:
 *  - Build the sidebar navigation automatically
 *  - Power the `resources()` Blade directive / view data injection
 *  - Enable global search across all registered models
 */
class ResourceRegistry
{
    /** @var array<class-string<CrudResource>> */
    private static array $resources = [];

    // -----------------------------------------------------------------------
    // Registration
    // -----------------------------------------------------------------------

    /**
     * Register one or more Resource classes.
     *
     * @param  array<class-string<CrudResource>> $resources
     */
    public static function register(array $resources): void
    {
        foreach ($resources as $resource) {
            if (!in_array($resource, static::$resources, true)) {
                static::$resources[] = $resource;
            }
        }
    }

    /**
     * Register a single Resource class.
     *
     * @param  class-string<CrudResource> $resource
     */
    public static function push(string $resource): void
    {
        static::register([$resource]);
    }

    /**
     * Remove all registered Resources (useful in tests).
     */
    public static function flush(): void
    {
        static::$resources = [];
    }

    // -----------------------------------------------------------------------
    // Queries
    // -----------------------------------------------------------------------

    /**
     * Return all registered Resource class strings.
     *
     * @return array<class-string<CrudResource>>
     */
    public static function all(): array
    {
        return static::$resources;
    }

    /**
     * Return registered Resources as a Collection.
     *
     * @return Collection<int, class-string<CrudResource>>
     */
    public static function collect(): Collection
    {
        return collect(static::$resources);
    }

    /**
     * Return registered Resources sorted by their navigation sort order.
     *
     * @return array<class-string<CrudResource>>
     */
    public static function sorted(): array
    {
        $resources = static::$resources;
        usort($resources, fn ($a, $b) => $a::getNavigationSort() <=> $b::getNavigationSort());
        return $resources;
    }

    /**
     * Find the first registered Resource whose model class matches.
     *
     * @param  class-string $model
     * @return class-string<CrudResource>|null
     */
    public static function forModel(string $model): ?string
    {
        foreach (static::$resources as $resource) {
            if ($resource::getModelClass() === $model) {
                return $resource;
            }
        }
        return null;
    }

    // -----------------------------------------------------------------------
    // Auto-discovery
    // -----------------------------------------------------------------------

    /**
     * Scan a directory for classes that extend CrudResource and register them.
     *
     * @param  string $path       Absolute filesystem path to scan (recursive).
     * @param  string $namespace  Base PHP namespace corresponding to $path.
     *                             e.g. 'Modules\Blog\Resources'
     */
    public static function discover(string $path, string $namespace): void
    {
        if (! is_dir($path)) {
            return;
        }

        $namespace = rtrim($namespace, '\\') . '\\';

        $finder = (new Finder())
            ->files()
            ->name('*Resource.php')
            ->in($path)
            ->sortByName();

        foreach ($finder as $file) {
            $relative   = Str::of($file->getRelativePathname())
                ->replace('/', '\\')
                ->replaceLast('.php', '')
                ->toString();

            $class = $namespace . $relative;

            if (! class_exists($class)) {
                continue;
            }

            try {
                $ref = new ReflectionClass($class);
            } catch (\ReflectionException) {
                continue;
            }

            if ($ref->isAbstract() || ! $ref->isSubclassOf(CrudResource::class)) {
                continue;
            }

            static::push($class);
        }
    }

    /**
     * Scan all paths from config('livewire-crud.resources.paths').
     *
     * Expected config shape (matches config/config.php):
     *   'resources' => [
     *       'auto_discover' => true,
     *       'paths' => [
     *           ['path' => app_path('Filament/Resources'), 'namespace' => 'App\\Filament\\Resources'],
     *           ['path' => base_path('Modules/Shop/Livewire/Resources'), 'namespace' => 'Modules\\Shop\\Livewire\\Resources'],
     *       ],
     *   ],
     */
    public static function discoverFromConfig(): void
    {
        /** @var bool $enabled */
        $enabled = config('livewire-crud.resources.auto_discover', false);
        if (! $enabled) {
            return;
        }

        /** @var array<array{path: string, namespace: string}> $paths */
        $paths = config('livewire-crud.resources.paths', []);

        foreach ($paths as $entry) {
            if (is_array($entry) && isset($entry['path'], $entry['namespace'])) {
                static::discover($entry['path'], $entry['namespace']);
            } elseif (is_string($entry)) {
                // Legacy flat format: path => namespace
                static::discover(key((array) $entry), current((array) $entry));
            }
        }
    }

    // -----------------------------------------------------------------------
    // Global search
    // -----------------------------------------------------------------------

    /**
     * Run a search query across all registered Resources that opt in to global search.
     *
     * @return array<array{resource: class-string<CrudResource>, title: string, details: array<string,string>, url: string}>
     */
    public static function globalSearch(string $search): array
    {
        $results = [];

        foreach (static::$resources as $resource) {
            if (empty($resource::getGloballySearchableAttributes())) {
                continue;
            }

            $records = $resource::performGlobalSearch($search);

            foreach ($records as $record) {
                $results[] = [
                    'resource' => $resource,
                    'title'    => $resource::getGlobalSearchResultTitle($record),
                    'details'  => $resource::getGlobalSearchResultDetails($record),
                    'url'      => $resource::getGlobalSearchResultUrl($record),
                ];
            }
        }

        return $results;
    }

    // -----------------------------------------------------------------------
    // Navigation builders
    // -----------------------------------------------------------------------

    /**
     * Build a flat array of NavigationItems for all registered Resources,
     * grouped and sorted.
     *
     * @return array<NavigationItem>
     */
    public static function navigationItems(): array
    {
        $items = [];
        foreach (static::sorted() as $resource) {
            $items[] = NavigationItem::make($resource::getNavigationLabel())
                ->url($resource::getUrl())
                ->icon($resource::getNavigationIcon())
                ->group($resource::getNavigationGroup())
                ->sort($resource::getNavigationSort());
        }
        return $items;
    }

    /**
     * Build an array of NavigationGroups, each populated with their
     * Resources' NavigationItems.  Resources with no group are returned
     * as a special group with an empty label.
     *
     * @return array<NavigationGroup>
     */
    public static function navigationGroups(): array
    {
        $grouped = [];

        foreach (static::sorted() as $resource) {
            $group = $resource::getNavigationGroup();
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = NavigationItem::make($resource::getNavigationLabel())
                ->url($resource::getUrl())
                ->icon($resource::getNavigationIcon())
                ->sort($resource::getNavigationSort());
        }

        $navGroups = [];
        foreach ($grouped as $groupLabel => $items) {
            $navGroups[] = NavigationGroup::make($groupLabel ?: 'General')
                ->icon('bi-grid')
                ->items($items);
        }

        return $navGroups;
    }

    /**
     * Render the full sidebar navigation HTML.
     * Suitable for use in a Blade layout partial.
     */
    public static function renderNavigation(): string
    {
        $html = '';
        foreach (static::navigationGroups() as $group) {
            $html .= $group->render() . "\n";
        }
        return $html;
    }
}
