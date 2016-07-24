<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Exception;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleVerification;
use Mnabialek\LaravelSimpleModules\Models\Module;
use Mnabialek\LaravelSimpleModules\Traits\Replacer;

class ModuleMakeMigration extends BaseCommand
{
    use ModuleVerification;
    use Replacer;
    use ModuleCreator;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:make-migration 
    {module : Module name}
    {name : Migration full name (ex. create_users_table)}
    {--type= : Type of migration (default options: create, edit)}
    {--table= : Table name (use with --type)}
    {--group= : Stub group name that will be used for creating this migration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create migration in selected module';

    /**
     * {@inheritdoc}
     */
    public function proceed()
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $type = $this->option('type');
        $table = $this->option('table');

        // verify whether both type and table used
        if ($type && !$table || $table && !$type) {
            throw new Exception('You need to use both options --type and --table when using any of them');
        }

        // verify whether module exists
        $modules = $this->verifyExisting(collect((array)$module));

        $this->createMigrationFile($modules->first(), $name, $type, $table);
    }

    /**
     * Create migration file
     *
     * @param Module $module
     * @param string $name
     * @param string $type
     * @param string $table
     */
    protected function createMigrationFile(Module $module, $name, $type, $table)
    {
        $stubGroup = $this->getStubGroup();
        $type = $type ?: $this->config->getMigrationDefaultType();
        $stubFile = $this->config->getMigrationStubFileName($type);

        if (!$stubFile) {
            throw new Exception("There is no {$type} in module_migrations.types registered in configuration file");
        }

        // migration file name
        $filename = date('Y_m_d_His') . '_' . snake_case($name) . '.php';

        // migration class name
        $migrationClass = studly_case($name);

        $this->copyStubFileIntoModule($module, $stubFile, $stubGroup,
            $module->getMigrationsPath() .  DIRECTORY_SEPARATOR . $filename ,
            ['migrationClass' => $migrationClass, 'table' => $table]
        );

        $this->info("[Module {$module}] Created migration file: {$filename}");
    }
}
