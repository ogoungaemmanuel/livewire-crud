<?php

namespace Xslainadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use File;

class LivewireCrudGenerator extends LivewireGeneratorCommand
{

	protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];

    protected $signature = 'crud:generate {name : Table name} {theme} {module}';

    protected $description = 'Generate Livewire Component and CRUD operations';

    /**
     * Execute the console command.
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->table = $this->getNameInput();
        $this->module = $this->getModuleInput();
        $this->theme = $this->getThemeInput();

        // If table not exist in DB return
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return false;
        }

        // Build the class name from table name
        $this->name = $this->_buildClassName();

        // Generate the crud
           $this->buildModel()
				->buildViews();

		//Updating Routes
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $theme = $this->getThemeInput();
        $this->filesystem = new Filesystem;
        $this->argument = $this->getNameInput();
        $routeFile = base_path("Modules/{$module}/routes/web.php");
        $routeContents = $this->filesystem->get($routeFile);
        if ($this->getThemeInput() == 'none') {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire." . $this->getNameInput() . ".index')->middleware('auth');";
        }else {
            $routeItemStub = "\tRoute::view('" .     $this->getNameInput() . "', '{$modulelower}::livewire.' . My_Theme() . '." . $this->getNameInput() . ".index')->middleware('auth');";
        }
		$routeItemHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        }

		//Updating Nav Bar
        $layoutFile = 'resources/views/layouts/app.blade.php';
		//$layoutFile = base_path("Modules/backend/resources/views/pmsmenu/{$modulelower}.blade.php");
        $layoutContents = $this->filesystem->get($layoutFile);
		//$navItemStub = "\t\t\t\t\t\t<li><a href=\"{{ url('/{$modulelower}/" . $this->getNameInput() . "') }}\"> " . ucfirst($this->getNameInput()) . "</a></li>";
        $navItemStub = "\t\t\t\t\t\t<li class=\"nav-item\"><a href=\"{{ url('/".$this->getNameInput()."') }}\" class=\"nav-link\"><i class=\"fab fa-laravel text-info\"></i> ". ucfirst($this->getNameInput()) ."</a></li>";
        $navItemHook = '<!--Nav Bar Hooks - Do not delete!!-->';

        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $layoutFile . '</info>');
        }

        $this->info('');
        $this->info('Livewire Component & CRUD Generated Successfully.');

        return true;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildModel()
    {
        $modulelower = Str::lower($this->getModuleInput());
        $module = $this->getModuleInput();
        $theme = $this->getThemeInput();
        $modelPath = $this->_getCreateModelPath($this->name);
        // $modelPath = $this->_getModelPath($this->name);
        $createlPath = $this->_getCreatePath($this->name);
        $deletePath = $this->_getDeletePath($this->name);
        $editPath = $this->_getEditPath($this->name);
        $showPath = $this->_getShowPath($this->name);
		$livewirePath = $this->_getLivewirePath($this->name);
        $modulePath = trim($this->_getModulePath($this->name), '{}');
        $factoryPath = $this->_getFactoryPath($this->name);

        if ($this->files->exists($modulePath) && $this->ask("Livewire Component ". Str::studly(Str::singular($this->table)) ."Component Already exist. Do you want overwrite (y/n)?", 'y') == 'n') {
            return $this;
        }

        // Make Replacements in Model / Livewire / Migrations / Factories
        $replace = array_merge($this->buildReplacements(), $this->modelReplacements());

        $modelTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Model')
        );
		$factoryTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Factory')
        );

        if ($theme == 'none') {
        $livewireTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Livewire')
        );
        $editTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub("modals/Edit")
        );
        $createTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub("modals/Create")
        );
        $showTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub("modals/Show")
        );
         $deleteTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub("modals/Delete")
        );
        }else{
            $livewireTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub('Livewirethemed')
            );
            $editTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub("modalsthemed/Edit")
            );
            $createTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub("modalsthemed/Create")
            );
            $showTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub("modalsthemed/Show")
            );
            $deleteTemplate = str_replace(
                array_keys($replace),
                array_values($replace),
                $this->getStub("modalsthemed/Delete")
            );
        }
        $this->warn('Creating: <info>Livewire Component...</info>');
        // $this->write($livewirePath, $livewireTemplate);
        $this->write($modulePath, $livewireTemplate);
		$this->warn('Creating: <info>Model...</info>');
        //start Create
        $this->write($createlPath, $createTemplate);
		$this->warn('Creating: <info>Create...</info>');
        //end create
        //start edit
        $this->write($editPath, $editTemplate);
		$this->warn('Creating: <info>Edit...</info>');
        //end edit
        //start Create
        $this->write($showPath, $showTemplate);
		$this->warn('Creating: <info>Show...</info>');
        //end create
        //start Create
        $this->write($deletePath, $deleteTemplate);
		$this->warn('Creating: <info>Delete...</info>');
        //end create
        $this->write($modelPath, $modelTemplate);
        $this->warn('Creating: <info>Factories, Please edit before running Factory ...</info>');
        $this->write($factoryPath, $factoryTemplate);

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    protected function buildViews()
    {
        $theme = $this->getThemeInput();
        $this->warn('Creating:<info> Views ...</info>');

        $tableHead = "\n";
        $tableBody = "\n";
        $viewRows = "\n";
        $form = "\n";
        $show = "\n";
        $type = null;

        foreach ($this->getFilteredColumns() as $column) {
            $title = Str::title(str_replace('_', ' ', $column));

            $tableHead .= "\t\t\t\t". $this->getHead($title);
            $tableBody .= "\t\t\t\t". $this->getBody($column);
            $form .= $this->getField($title, $column, 'form-field');
			$form .= "\n";
            $show .= $this->showField($title, $column, 'show-field');
			$show .= "\n";
        }

		foreach ($this->getColumns() as $values) {
			$type = "text";
            // if (Str::endsWith(($values->Type), ['timestamp', 'date', 'datetime'])) {
                // $type = "date";
            // }
			// elseif (Str::endsWith(($values->Type), 'int')) {
				// $type = "number";
			// }
			// elseif (Str::startsWith(($values->Type), 'time')) {
				// $type = "time";
			// }
			// elseif (Str::contains(($values->Type), 'text')) {
				// $type = "textarea";
			// }
			// else{
				// $type = "text";
			// }
		}

        $replace = array_merge($this->buildReplacements(), [
            '{{tableHeader}}' => $tableHead,
            '{{tableBody}}' => $tableBody,
            '{{viewRows}}' => $viewRows,
            '{{form}}' => $form,
            '{{show}}' => $show,
            '{{type}}' => $type,
        ]);

        $this->buildLayout();
        foreach (['view', 'index', 'create', 'delete', 'show', 'update'] as $view) {
            if ($this->getThemeInput() == 'none') {
                $viewTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub("views/{$view}")
                );
            } else {
                $viewTemplate = str_replace(
                    array_keys($replace),
                    array_values($replace),
                    $this->getStub("themes/{$theme}/views/{$view}")
                );
            }
            $this->write($this->_getViewPath($view), $viewTemplate);
        }

        return $this;
    }

    /**
     * Make the class name from table name.
     *
     * @return string
     */
    private function _buildClassName()
    {
        return Str::studly(Str::singular($this->table));
    }

	private function replace($content)
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}
