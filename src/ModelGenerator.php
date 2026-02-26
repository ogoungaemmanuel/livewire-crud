<?php

declare(strict_types=1);

namespace Xslainadmin\LivewireCrud;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ModelGenerator
{
    private ?string $functions = null;
    private string $table;
    private string $properties;
    private string $modelNamespace;

    /**
     * ModelGenerator constructor.
     */
    public function __construct(string $table, string $properties, string $modelNamespace)
    {
        $this->table = $table;
        $this->properties = $properties;
        $this->modelNamespace = $modelNamespace;
        $this->_init();
    }

    /**
     * Get all the eloquent relations.
     *
     * @return array{0: string|null, 1: string}
     */
    public function getEloquentRelations(): array
    {
        return [$this->functions, $this->properties];
    }

    private function _init(): void
    {
        foreach ($this->_getTableRelations() as $relation) {
            if ($relation->ref) {
                $tableKeys = $this->_getTableKeys($relation->ref_table);
                $eloquent = $this->_getEloquent($relation, $tableKeys);
            } else {
                $eloquent = 'hasOne';
            }

            $this->functions .= $this->_getFunction($eloquent, $relation->ref_table, $relation->foreign_key, $relation->local_key);
        }
    }

    private function _getEloquent(object $relation, array $tableKeys): string
    {
        $eloquent = '';
        foreach ($tableKeys as $tableKey) {
            if ($relation->foreign_key == $tableKey->Column_name) {
                $eloquent = 'hasMany';

                if ($tableKey->Key_name == 'PRIMARY') {
                    $eloquent = 'hasOne';
                } elseif ($tableKey->Non_unique == 0 && $tableKey->Seq_in_index == 1) {
                    $eloquent = 'hasOne';
                }
            }
        }

        return $eloquent;
    }

    private function _getFunction(string $relation, string $table, string $foreign_key, string $local_key): string
    {
        list($model, $relationName) = $this->_getModelName($table, $relation);
        $relClass = ucfirst($relation);

        switch ($relation) {
            case 'hasOne':
                $this->properties .= "\n * @property $model $$relationName";
                break;
            case 'hasMany':
                $this->properties .= "\n * @property ".$model."[] $$relationName";
                break;
        }

        return '
    /**
     * @return \Illuminate\Database\Eloquent\Relations\\'.$relClass.'
     */
    public function '.$relationName.'()
    {
        return $this->'.$relation.'(\''.$this->modelNamespace.'\\'.$model.'\', \''.$foreign_key.'\', \''.$local_key.'\');
    }
    ';
    }

    /**
     * Get the name relation and model.
     *
     * @return array{0: string, 1: string}
     */
    private function _getModelName(string $name, string $relation): array
    {
        $class = Str::studly(Str::singular($name));
        $relationName = '';

        switch ($relation) {
            case 'hasOne':
                $relationName = Str::camel(Str::singular($name));
                break;
            case 'hasMany':
                $relationName = Str::camel(Str::plural($name));
                break;
        }

        return [$class, $relationName];
    }

    /**
     * Get all relations from Table.
     *
     * @return array<object>
     */
    private function _getTableRelations(): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return [];
        }

        $db = DB::getDatabaseName();
        $sql = <<<SQL
SELECT TABLE_NAME ref_table, COLUMN_NAME foreign_key, REFERENCED_COLUMN_NAME local_key, '1' ref
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE REFERENCED_TABLE_NAME = '$this->table' AND TABLE_SCHEMA = '$db'
UNION
SELECT REFERENCED_TABLE_NAME ref_table, REFERENCED_COLUMN_NAME foreign_key, COLUMN_NAME local_key, '0' ref
  FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
  WHERE TABLE_NAME = '$this->table' AND TABLE_SCHEMA = '$db' AND REFERENCED_TABLE_NAME IS NOT NULL

ORDER BY ref_table ASC
SQL;

        return DB::select($sql);
    }

    /**
     * Get all Keys from table.
     *
     * @return array<object>
     */
    private function _getTableKeys(string $table): array
    {
        if (DB::getDriverName() !== 'mysql') {
            return [];
        }

        return DB::select("SHOW KEYS FROM `{$table}`");
    }
}
