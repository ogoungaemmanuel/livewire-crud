<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Artisan command to generate (or re-generate) a single Resource class.
 *
 * Usage:
 *   php artisan crud:resource User users Admin
 *   php artisan crud:resource Product products Shop
 */
class MakeResourceCommand extends LivewireGeneratorCommand
{
    protected $signature = 'crud:resource
        {name    : Model / class name (PascalCase), e.g. User}
        {theme   : Theme slug (use "none" if no theme)}
        {menu    : Menu identifier}
        {module  : nWidart module name}';

    protected $description = 'Generate a CrudResource class for an existing CRUD module';

    protected Filesystem $filesystem;

    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
        $this->filesystem = $files;
    }

    public function handle(): int
    {
        $this->modelName   = Str::studly($this->argument('name'));
        $this->table  = Str::snake(Str::plural($this->modelName));
        $this->module = $this->argument('module');
        $this->theme  = $this->argument('theme');
        $this->menu   = $this->argument('menu');

        if (!$this->tableExists()) {
            $this->error("Table [{$this->table}] does not exist in the database.");
            return self::FAILURE;
        }

        $resourcePath = $this->_getResourcePath();

        if (File::exists($resourcePath)) {
            if ($this->ask("Resource [{$this->modelName}Resource] already exists. Overwrite? (y/n)", 'y') === 'n') {
                $this->info('Skipped.');
                return self::SUCCESS;
            }
            File::delete($resourcePath);
        }

        $replace  = array_merge($this->buildReplacements(), $this->modelReplacements());
        $template = str_replace(
            array_keys($replace),
            array_values($replace),
            $this->getStub('Resource')
        );

        $this->write($resourcePath, $template);
        $this->info("Resource created: <comment>{$resourcePath}</comment>");

        return self::SUCCESS;
    }

    protected function getArguments(): array
    {
        return [
            ['name',   InputArgument::REQUIRED, 'Model name (PascalCase)'],
            ['theme',  InputArgument::REQUIRED, 'Theme slug'],
            ['menu',   InputArgument::REQUIRED, 'Menu identifier'],
            ['module', InputArgument::REQUIRED, 'Module name'],
        ];
    }

    /**
     * Not used by this command — only a Resource class is generated.
     */
    public function buildModel(): static
    {
        return $this;
    }

    /**
     * Not used by this command — only a Resource class is generated.
     */
    public function buildViews(): static
    {
        return $this;
    }
}
