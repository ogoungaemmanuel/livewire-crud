<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

/**
 * Contract for Resource classes that participate in the global search feature.
 *
 * Implement this interface on any CrudResource to have its records surfaced
 * in a site-wide search result dropdown (similar to Filament's global search).
 *
 * The package's GlobalSearch service queries all registered Resources that
 * implement this interface and merges the results into a unified collection.
 */
interface GloballySearchable
{
    /**
     * Return the list of model attribute names to search across.
     * Only scalar / string attributes are supported.
     *
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array;

    /**
     * Return a human-readable title for a single search result.
     * Typically the model's primary display field (name, title, etc.).
     *
     * @param  object  $record  The Eloquent model instance
     */
    public static function getGlobalSearchResultTitle(object $record): string;

    /**
     * Return optional detail lines shown beneath the title in the result.
     * Keys are labels, values are the attribute values.
     *
     * @param  object  $record
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(object $record): array;

    /**
     * Return the URL to navigate to when the search result is clicked.
     *
     * @param  object  $record
     */
    public static function getGlobalSearchResultUrl(object $record): string;
}
