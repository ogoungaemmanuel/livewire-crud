<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Xslainadmin\LivewireCrud\LivewireCrudServiceProvider;

class TestCase extends OrchestraTestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LivewireCrudServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}
