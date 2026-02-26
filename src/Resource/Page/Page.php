<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource\Page;

use Xslainadmin\LivewireCrud\Resource\CrudResource;

/**
 * Abstract base for all Resource pages.
 *
 * Model Pages. A Page is a Livewire component
 * that is tied to a specific Resource and renders one of the CRUD views
 * (list / create / edit / view). Pages are registered in the Resource's
 * static::getPages() method, allowing the package to build routes
 * automatically.
 *
 * Usage:
 *
 *   class ListUsers extends ListRecords
 *   {
 *       protected static string $resource = UserResource::class;
 *   }
 */
abstract class Page extends \Livewire\Component
{
    /**
     * The Resource class this page belongs to.
     *
     * @var class-string<CrudResource>
     */
    protected static string $resource;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /** @return class-string<CrudResource> */
    public static function getResource(): string
    {
        return static::$resource;
    }

    public static function getNavigationLabel(): string
    {
        return static::$resource::getNavigationLabel();
    }

    public static function getNavigationIcon(): string
    {
        return static::$resource::getNavigationIcon();
    }

    /** Resolve the URL for this page type. Subclasses may override. */
    public static function getUrl(array $params = []): string
    {
        return static::$resource::getUrl(static::pageKey(), $params);
    }

    /** Return the page key used by Resource::getUrl(). */
    protected static function pageKey(): string
    {
        return 'index';
    }

    // -----------------------------------------------------------------------
    // Breadcrumbs
    // -----------------------------------------------------------------------

    /**
     * Return an array of breadcrumb items: [['label' => string, 'url' => string|null]]
     *
     * @return array<int, array{label: string, url: string|null}>
     */
    public function getBreadcrumbs(): array
    {
        return [
            ['label' => static::$resource::getPluralModelLabel(), 'url' => static::$resource::getUrl()],
        ];
    }

    // -----------------------------------------------------------------------
    // Authorization
    // -----------------------------------------------------------------------

    /**
     * Returns true when the current user is allowed to view this page.
     * Override in the concrete page class for fine-grained control.
     */
    public static function canAccess(): bool
    {
        return static::$resource::canViewAny();
    }
}
