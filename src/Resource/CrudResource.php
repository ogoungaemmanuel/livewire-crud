<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Resource;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Xslainadmin\LivewireCrud\Contracts\ResourceContract;
use Xslainadmin\LivewireCrud\Schema\FormSchema;
use Xslainadmin\LivewireCrud\Schema\TableSchema;

/**
 * Abstract base Resource class — inspired by FilamentPHP's Resource pattern.
 *
 * Each generated CRUD produces a concrete Resource class that centralises
 * all column, field, filter, action, and navigation config for that model.
 *
 * Usage (generated class):
 *
 *   class UserResource extends CrudResource
 *   {
 *       protected static string $model = User::class;
 *       protected static string $navigationIcon  = 'bi-people';
 *       protected static string $navigationLabel = 'Users';
 *       protected static ?string $navigationGroup = 'Administration';
 *
 *       public static function form(FormSchema $schema): FormSchema { ... }
 *       public static function table(TableSchema $table): TableSchema { ... }
 *   }
 */
abstract class CrudResource implements ResourceContract
{
    /** The fully-qualified Eloquent model class. */
    protected static string $model;

    /** Bootstrap Icons class used in navigation menus. */
    protected static string $navigationIcon = 'bi-table';

    /** Human-readable label shown in navigation sidebar. Defaults to plural model name. */
    protected static ?string $navigationLabel = null;

    /** Optional navigation group / section heading. Null means ungrouped. */
    protected static ?string $navigationGroup = null;

    /** Sort order within the navigation group (lower = higher). */
    protected static int $navigationSort = 0;

    /** Badge value shown next to the navigation item (e.g. unread count). */
    protected static ?string $navigationBadge = null;

    /** Color of the navigation badge (Bootstrap contextual: 'danger', 'warning', etc.). */
    protected static string $navigationBadgeColor = 'danger';

    /** Override the singular model label. */
    protected static ?string $modelLabel = null;

    /** Override the plural model label. */
    protected static ?string $pluralModelLabel = null;

    /** Custom base URL slug. Defaults to kebab-case plural model name. */
    protected static ?string $slug = null;

    /** Number of records per page in the index table. */
    protected static int $recordsPerPage = 10;

    /* ── abstract contract ───────────────────────────────────────────────── */

    /**
     * Define the form fields for create / edit modals.
     */
    abstract public static function form(FormSchema $schema): FormSchema;

    /**
     * Define the table columns, filters, actions, and bulk-actions.
     */
    abstract public static function table(TableSchema $table): TableSchema;

    /* ── label helpers ───────────────────────────────────────────────────── */

    public static function getModelClass(): string
    {
        return static::$model;
    }

    public static function getModelLabel(): string
    {
        if (static::$modelLabel !== null) {
            return static::$modelLabel;
        }
        $class = class_basename(static::$model);
        return Str::headline($class);
    }

    public static function getPluralModelLabel(): string
    {
        if (static::$pluralModelLabel !== null) {
            return static::$pluralModelLabel;
        }
        return Str::plural(static::getModelLabel());
    }

    public static function getNavigationLabel(): string
    {
        return static::$navigationLabel ?? static::getPluralModelLabel();
    }

    public static function getNavigationIcon(): string
    {
        return static::$navigationIcon;
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup;
    }

    public static function getNavigationSort(): int
    {
        return static::$navigationSort;
    }

    public static function getNavigationBadge(): ?string
    {
        return static::$navigationBadge;
    }

    public static function getNavigationBadgeColor(): string
    {
        return static::$navigationBadgeColor;
    }

    /* ── URL helpers ─────────────────────────────────────────────────────── */

    public static function getSlug(): string
    {
        if (static::$slug !== null) {
            return static::$slug;
        }
        return Str::slug(static::getPluralModelLabel());
    }

    public static function getUrl(string $name = 'index', array $params = []): string
    {
        $base = '/' . static::getSlug();
        return match ($name) {
            'index'  => $base,
            'create' => "{$base}/create",
            'edit'   => "{$base}/{$params['id']}/edit",
            'view'   => "{$base}/{$params['id']}",
            default  => $base,
        };
    }

    /* ── schema factory helpers ──────────────────────────────────────────── */

    /**
     * Retrieve the fully configured FormSchema instance.
     */
    public static function getForm(): FormSchema
    {
        return static::form(FormSchema::make());
    }

    /**
     * Retrieve the fully configured TableSchema instance.
     */
    public static function getTable(): TableSchema
    {
        return static::table(TableSchema::make());
    }

    /* ── query helpers ───────────────────────────────────────────────────── */

    /**
     * Build an Eloquent query from searchable columns.
     *
     * Used by the generated Livewire component's search() method.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string                                $search
     */
    public static function applySearch($query, string $search): void
    {
        $table = static::getTable();
        $first = true;
        foreach ($table->getColumns() as $column) {
            if (!$column->isSearchable()) {
                continue;
            }
            $col = $column->getName();
            if ($first) {
                $query->where($col, 'like', "%{$search}%");
                $first = false;
            } else {
                $query->orWhere($col, 'like', "%{$search}%");
            }
        }
    }

    /* ── per-page helper ─────────────────────────────────────────────────── */

    public static function getRecordsPerPage(): int
    {
        return static::$recordsPerPage;
    }

    /* ── Eloquent query ──────────────────────────────────────────────────── */

    /**
     * Return the base Eloquent Builder for the resource.
     * Override to add global scopes, eager-loads, or tenant constraints.
     */
    public static function getEloquentQuery(): Builder
    {
        return (static::$model)::query();
    }

    /* ── Page registration ───────────────────────────────────────────────── */

    /**
     * Map page names to fully-qualified Livewire component classes.
     *
     * Override in generated Resources to register custom pages.
     *
     * @return array<string, class-string>
     */
    public static function getPages(): array
    {
        return [];
    }

    /* ── Authorization ───────────────────────────────────────────────────── */

    /**
     * Resolve a policy ability with an optional model instance,
     * falling back to `true` when no policy / gate is registered.
     */
    protected static function resolvePolicy(string $ability, ?Model $record = null): bool
    {
        if (! config('livewire-crud.permissions.enabled', false)) {
            return true;
        }

        $user = auth()->user();
        if ($user === null) {
            return false;
        }

        $args = $record !== null
            ? [static::$model, $record]
            : [static::$model];

        try {
            return Gate::allows($ability, $args);
        } catch (\Exception) {
            return true;
        }
    }

    public static function canViewAny(): bool
    {
        return static::resolvePolicy('viewAny');
    }

    public static function canCreate(): bool
    {
        return static::resolvePolicy('create');
    }

    public static function canEdit(Model $record): bool
    {
        return static::resolvePolicy('update', $record);
    }

    public static function canDelete(Model $record): bool
    {
        return static::resolvePolicy('delete', $record);
    }

    public static function canView(Model $record): bool
    {
        return static::resolvePolicy('view', $record);
    }

    public static function canForceDelete(Model $record): bool
    {
        return static::resolvePolicy('forceDelete', $record);
    }

    public static function canRestore(Model $record): bool
    {
        return static::resolvePolicy('restore', $record);
    }

    /* ── Global search ───────────────────────────────────────────────────── */

    /**
     * Attributes to search across in the global search index.
     * Return an empty array to opt out of global search.
     *
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    /**
     * The title shown for this record in global search results.
     */
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $attrs = static::getGloballySearchableAttributes();
        $first = $attrs[0] ?? 'id';
        return (string) ($record->{$first} ?? $record->getKey());
    }

    /**
     * Additional key/value pairs shown below the title in global search results.
     *
     * @return array<string, string>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [];
    }

    /**
     * URL to navigate to when a global search result is clicked.
     */
    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['id' => $record->getKey()]);
    }

    /**
     * Perform the global search query and return matching records.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Model>
     */
    public static function performGlobalSearch(string $search): \Illuminate\Database\Eloquent\Collection
    {
        $attrs = static::getGloballySearchableAttributes();
        if (empty($attrs)) {
            return collect();
        }

        $query = static::getEloquentQuery();
        $first = true;

        foreach ($attrs as $attr) {
            if ($first) {
                $query->where($attr, 'like', "%{$search}%");
                $first = false;
            } else {
                $query->orWhere($attr, 'like', "%{$search}%");
            }
        }

        return $query->limit(10)->get();
    }
}
