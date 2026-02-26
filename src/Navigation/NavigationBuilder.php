<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Navigation;

use Illuminate\Support\Collection;
use Xslainadmin\LivewireCrud\ResourceRegistry;

/**
 * NavigationBuilder — assembles the full sidebar navigation tree from all
 * registered CrudResource classes.
 *
 * Navigation builder. The builder reads navigation
 * metadata from each Resource (label, icon, group, sort, badge) and produces
 * a sorted list of NavigationGroup objects ready for rendering.
 *
 * Usage (in a Livewire layout component or Blade view):
 *
 *   $groups = NavigationBuilder::build();
 *
 * Or to render directly:
 *
 *   {!! NavigationBuilder::render() !!}
 */
class NavigationBuilder
{
    /**
     * Build the navigation tree from all registered Resources.
     *
     * @return Collection<int, NavigationGroup>  Grouped + sorted navigation
     */
    public static function build(): Collection
    {
        $items  = self::buildItems();
        $groups = self::groupItems($items);
        return $groups->sortBy(fn (NavigationGroup $g) => $g->getSort());
    }

    /**
     * Render the full sidebar HTML from all registered Resources.
     */
    public static function render(): string
    {
        $html = '';
        foreach (static::build() as $group) {
            $html .= $group->render() . "\n";
        }
        return $html;
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    /**
     * Build a flat list of NavigationItems from registered Resources.
     *
     * @return Collection<int, NavigationItem>
     */
    protected static function buildItems(): Collection
    {
        return collect(ResourceRegistry::all())->map(function (string $resource): NavigationItem {
            $badge = $resource::getNavigationBadge();

            return NavigationItem::make($resource::getNavigationLabel())
                ->url($resource::getUrl())
                ->icon($resource::getNavigationIcon())
                ->group($resource::getNavigationGroup())
                ->sort($resource::getNavigationSort())
                ->badge($badge, $badge !== null ? $resource::getNavigationBadgeColor() : 'danger');
        });
    }

    /**
     * Group NavigationItems into NavigationGroup objects.
     * Items without a group form an unnamed flat group rendered inline.
     *
     * @param  Collection<int, NavigationItem> $items
     * @return Collection<int, NavigationGroup>
     */
    protected static function groupItems(Collection $items): Collection
    {
        $grouped = $items->groupBy(fn (NavigationItem $item): string => $item->getGroup() ?? '');

        $groups = collect();
        $sort   = 0;

        foreach ($grouped as $groupLabel => $groupItems) {
            $group = NavigationGroup::make($groupLabel ?: 'General')
                ->sort($sort++)
                ->items($groupItems->all());

            $groups->push($group);
        }

        return $groups;
    }

    // -----------------------------------------------------------------------
    // Custom navigation registration
    // -----------------------------------------------------------------------

    /** @var array<NavigationItem> */
    private static array $extraItems = [];

    /** @var array<NavigationGroup> */
    private static array $extraGroups = [];

    /**
     * Register extra navigation items that are not backed by a Resource.
     *
     * @param  NavigationItem[]  $items
     */
    public static function registerItems(array $items): void
    {
        static::$extraItems = array_merge(static::$extraItems, $items);
    }

    /**
     * Register a completely custom navigation group.
     */
    public static function registerGroup(NavigationGroup $group): void
    {
        static::$extraGroups[] = $group;
    }

    /** Clear registered custom items / groups (for testing). */
    public static function flush(): void
    {
        static::$extraItems  = [];
        static::$extraGroups = [];
    }
}
