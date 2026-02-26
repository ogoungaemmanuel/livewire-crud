<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Contracts;

use Xslainadmin\LivewireCrud\Schema\FormSchema;
use Xslainadmin\LivewireCrud\Schema\TableSchema;

/**
 * Contract that all generated Resource classes must satisfy.
 */
interface ResourceContract
{
    /**
     * Define the form fields for create / edit modals.
     */
    public static function form(FormSchema $schema): FormSchema;

    /**
     * Define the table columns, filters, and actions.
     */
    public static function table(TableSchema $table): TableSchema;

    /**
     * Return the fully-qualified Eloquent model class string.
     */
    public static function getModelClass(): string;

    /**
     * Return the human-readable singular label.
     */
    public static function getModelLabel(): string;

    /**
     * Return the human-readable plural label.
     */
    public static function getPluralModelLabel(): string;

    /**
     * Return the label shown in the navigation sidebar.
     */
    public static function getNavigationLabel(): string;

    /**
     * Return the Bootstrap Icons class (e.g. 'bi-people').
     */
    public static function getNavigationIcon(): string;

    /**
     * Return the navigation group / section heading.
     */
    public static function getNavigationGroup(): ?string;

    /**
     * Return the URL slug used to build routes.
     */
    public static function getSlug(): string;

    /**
     * Return the number of records shown per page in the index table.
     */
    public static function getRecordsPerPage(): int;
}
