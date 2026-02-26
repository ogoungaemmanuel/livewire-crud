<?php

declare(strict_types=1);

use Xslainadmin\LivewireCrud\Navigation\NavigationGroup;
use Xslainadmin\LivewireCrud\ResourceRegistry;
use Xslainadmin\LivewireCrud\Schema\FormSchema;
use Xslainadmin\LivewireCrud\Schema\TableSchema;

// -----------------------------------------------------------------------
// Test doubles
// -----------------------------------------------------------------------

/** @internal */
class FakeUserResource extends \Xslainadmin\LivewireCrud\Resource\CrudResource
{
    protected static string $model          = 'App\\Models\\User';
    protected static ?string $navigationGroup = 'Admin';
    protected static int $navigationSort   = 10;

    public static function form(FormSchema $schema): FormSchema
    {
        return $schema;
    }

    public static function table(TableSchema $table): TableSchema
    {
        return $table;
    }
}

/** @internal */
class FakePostResource extends \Xslainadmin\LivewireCrud\Resource\CrudResource
{
    protected static string $model          = 'App\\Models\\Post';
    protected static ?string $navigationGroup = 'Content';
    protected static int $navigationSort   = 20;

    public static function form(FormSchema $schema): FormSchema
    {
        return $schema;
    }

    public static function table(TableSchema $table): TableSchema
    {
        return $table;
    }
}

// -----------------------------------------------------------------------
// Tests
// -----------------------------------------------------------------------

beforeEach(function () {
    ResourceRegistry::flush();
});

afterEach(function () {
    ResourceRegistry::flush();
});

it('starts empty', function () {
    expect(ResourceRegistry::all())->toBeEmpty();
});

it('registers a single resource', function () {
    ResourceRegistry::push(FakeUserResource::class);
    expect(ResourceRegistry::all())->toHaveCount(1)
                                   ->toContain(FakeUserResource::class);
});

it('registers multiple resources at once', function () {
    ResourceRegistry::register([FakeUserResource::class, FakePostResource::class]);
    expect(ResourceRegistry::all())->toHaveCount(2);
});

it('does not register duplicates', function () {
    ResourceRegistry::push(FakeUserResource::class);
    ResourceRegistry::push(FakeUserResource::class);
    expect(ResourceRegistry::all())->toHaveCount(1);
});

it('flushes the registry', function () {
    ResourceRegistry::push(FakeUserResource::class);
    ResourceRegistry::flush();
    expect(ResourceRegistry::all())->toBeEmpty();
});

it('returns a collection', function () {
    ResourceRegistry::push(FakeUserResource::class);
    expect(ResourceRegistry::collect())->toBeInstanceOf(\Illuminate\Support\Collection::class)
                                       ->toHaveCount(1);
});

it('resolves resource by model class', function () {
    ResourceRegistry::push(FakeUserResource::class);
    expect(ResourceRegistry::forModel('App\\Models\\User'))->toBe(FakeUserResource::class);
});

it('returns null when no resource matches the model', function () {
    expect(ResourceRegistry::forModel('App\\Models\\Missing'))->toBeNull();
});

it('builds navigation items from registered resources', function () {
    ResourceRegistry::register([FakeUserResource::class, FakePostResource::class]);
    $items = ResourceRegistry::navigationItems();
    expect($items)->toHaveCount(2);
});

it('builds navigation groups from registered resources', function () {
    ResourceRegistry::register([FakeUserResource::class, FakePostResource::class]);
    $groups = ResourceRegistry::navigationGroups();
    // Two different groups → two NavigationGroup objects
    expect($groups)->toHaveCount(2)
                   ->each->toBeInstanceOf(NavigationGroup::class);
});

it('merges resources with the same group into one NavigationGroup', function () {
    // Both resources in the same navigation group
    ResourceRegistry::register([FakeUserResource::class, FakeUserResource::class]);
    ResourceRegistry::flush();

    // Register a second resource in the same group manually
    ResourceRegistry::push(FakeUserResource::class);

    $groups = ResourceRegistry::navigationGroups();
    // Only 'Admin' group → 1 NavigationGroup
    expect($groups)->toHaveCount(1);
});

it('sorts resources by navigationSort', function () {
    ResourceRegistry::register([FakePostResource::class, FakeUserResource::class]);
    $sorted = ResourceRegistry::sorted();
    expect($sorted[0])->toBe(FakeUserResource::class)  // sort 10
                      ->and($sorted[1])->toBe(FakePostResource::class); // sort 20
});
