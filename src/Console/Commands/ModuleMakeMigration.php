<?php

namespace Mnabialek\LaravelSimpleModules\Console\Commands;

use Exception;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleCreator;
use Mnabialek\LaravelSimpleModules\Console\Traits\ModuleVerification;
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
            $this->error('You need to use both options --type and --table when using any of them');

            return;
        }

        // verify whether module exists
        $modules = $this->verifyExisting((array)$module);
        if ($modules === false) {
            return;
        }

        $stubGroup = $this->getStubGroup();

        $this->createMigrationFile($module, $name, $stubGroup, $type, $table);
    }

    /**
     * Create migration file
     *
     * @param string $module
     * @param string $name
     * @param string $stubGroup
     * @param string $type
     * @param string $table
     */
    protected function createMigrationFile(
        $module,
        $name,
        $stubGroup,
        $type,
        $table
    ) {
        $type = $type ?:
            $this->module->config('module_migrations.default_type');

        $stubFile = $this->module->config("module_migrations.types.{$type}");

        if (!$stubFile) {
            $this->error("There is no {$type} in module_migrations.types registered in configuration file");

            return;
        }

        // migration file name
        $filename = date('Y_m_d_His') . '_' . snake_case($name) . '.php';

        // migration class name
        $migrationClass = studly_case($name);

        $created = $this->copyStubFileIntoModule(
            $stubFile,
            $this->getStubGroupDirectory($stubGroup),
            $filename,
            $this->module->getMigrationsPath($module),
            $module,
            true,
            ['migrationClass' => $migrationClass, 'table' => $table],
            true
        );

        if ($created) {
            $this->info("[Module {$module}] Migration file created: {$filename}");
        }
    }
}
