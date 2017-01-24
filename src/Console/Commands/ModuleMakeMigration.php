<?php

namespace Mnabialek\LaravelModular\Console\Commands;

use Exception;
use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Traits\Replacer;
use Mnabialek\LaravelModular\Console\Traits\ModuleCreator;
use Mnabialek\LaravelModular\Console\Traits\ModuleVerification;

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
     *
     * @throws Exception
     */
    protected function createMigrationFile(Module $module, $name, $type, $table)
    {
        $stubGroup = $this->getStubGroup();
        $type =
            $type ?: $this->laravel['modular.config']->migrationDefaultType();
        $stubFile =
            $this->laravel['modular.config']->migrationStubFileName($type);

        if (!$stubFile) {
            throw new Exception("There is no {$type} in module_migrations.types registered in configuration file");
        }

        // migration file name
        $filename = $this->getMigrationFileName($name);

        // migration class name
        $migrationClass = studly_case($name);

        $this->copyStubFileIntoModule($module, $stubFile, $stubGroup,
            $module->migrationsPath(true) . DIRECTORY_SEPARATOR . $filename,
            ['migrationClass' => $migrationClass, 'table' => $table]
        );

        $this->info("[Module {$module->name()}] Created migration file: {$filename}");
    }

    /**
     * Get migration file name based on user given migration name
     *
     * @param string $name
     *
     * @return string
     */
    protected function getMigrationFileName($name)
    {
        return date('Y_m_d_His') . '_' . snake_case($name) . '.php';
    }
}
