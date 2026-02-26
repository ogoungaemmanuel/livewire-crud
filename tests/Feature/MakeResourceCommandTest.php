<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;

// -----------------------------------------------------------------------
// crud:resource command
// -----------------------------------------------------------------------

it('fails when the model table does not exist', function () {
    $this->artisan('crud:resource', [
        'name'   => 'Widget',
        'theme'  => 'none',
        'menu'   => 'admin',
        'module' => 'Shop',
    ])
    ->expectsOutputToContain('does not exist')
    ->assertExitCode(1);
});

it('creates the Resource file when the table exists', function () {
    // Create the table in the in-memory SQLite DB so the command proceeds.
    Schema::create('widgets', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $outputPath = base_path('Modules/Shop/Resources/WidgetResource.php');

    // Clean up any leftover file from a previous run.
    if (file_exists($outputPath)) {
        unlink($outputPath);
    }

    $this->artisan('crud:resource', [
        'name'   => 'Widget',
        'theme'  => 'none',
        'menu'   => 'admin',
        'module' => 'Shop',
    ])
    ->expectsOutputToContain('Resource created')
    ->assertExitCode(0);

    expect(file_exists($outputPath))->toBeTrue();

    $contents = file_get_contents($outputPath);
    expect($contents)
        ->toContain('WidgetResource')
        ->toContain('extends CrudResource')
        ->toContain('public static function form')
        ->toContain('public static function table');

    // Tear down
    Schema::dropIfExists('widgets');
    if (file_exists($outputPath)) {
        unlink($outputPath);
    }
});
