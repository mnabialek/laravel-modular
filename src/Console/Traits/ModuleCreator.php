<?php

namespace Mnabialek\LaravelModular\Console\Traits;

use Mnabialek\LaravelModular\Models\Module;
use Mnabialek\LaravelModular\Traits\Normalizer;
use Exception;

trait ModuleCreator
{
    use Normalizer;

    /**
     * Verify stub group
     *
     * @param string $stubGroup
     *
     * @throws \Exception
     */
    protected function verifyStubGroup($stubGroup)
    {
        // first verify whether this group is in config file

        if (!collect($this->laravel['modular.config']->stubGroups())->contains($stubGroup)) {
            throw new Exception("Stub group {$stubGroup} does not exist. You need to add it to stubs_groups");
        }

        // then verify whether this stub group directory exists
        $directory = $this->getStubGroupDirectory($stubGroup);
        if (!$this->exists($directory)) {
            throw new Exception("Stub group directory {$directory} does not exist");
        }
    }

    /**
     * Get directory for given stub group
     *
     * @param string $group
     *
     * @return string
     */
    protected function getStubGroupDirectory($group)
    {
        return $this->normalizePath($this->laravel['modular.config']->stubsPath() .
            DIRECTORY_SEPARATOR .
            $this->laravel['modular.config']->stubGroupDirectory($group));
    }

    /**
     * Verify whether module config was published
     *
     * @throws \Exception
     */
    protected function verifyConfigExistence()
    {
        if (!$this->exists($this->laravel['modular.config']->configPath())) {
            throw new Exception('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');
        }
    }

    /**
     * Verify whether given file or directory exists
     *
     * @param string $path
     * @param Module $module
     *
     * @return bool
     */
    protected function exists($path, Module $module = null)
    {
        if ($module !== null) {
            $path = $module->directory() . DIRECTORY_SEPARATOR . $path;
        }

        return $this->laravel['files']->exists($path);
    }

    /**
     * Get stub group - either from input of default one
     *
     * @return mixed
     */
    protected function getStubGroup()
    {
        return $this->option('group') ?: $this->laravel['modular.config']->stubsDefaultGroup();
    }

    protected function getFilesStubGroup()
    {
        return $this->option('group') ?: $this->laravel['modular.config']->filesStubsDefaultGroup();
    }

    /**
     * Creates module directories
     *
     * @param Module $module
     * @param string $stubGroup
     */
    protected function createModuleDirectories(Module $module, $stubGroup)
    {
        $directories = collect($this->laravel['modular.config']
            ->stubGroupDirectories($stubGroup))->unique();

        if ($directories->isEmpty()) {
            $this->warn("[Module {$module->name()}] No explicit directories created");
        } else {
            $directories->each(function ($directory) use ($module) {
                $this->createDirectory($module, $directory);
            });
        }
    }

    /**
     * Creates directory
     *
     * @param Module $module
     * @param string $directory
     *
     * @return bool
     * @throws Exception
     */
    protected function createDirectory(Module $module, $directory)
    {
        if (!$this->exists($directory, $module)) {
            $result =
                $this->laravel['files']->makeDirectory($module->directory() .
                    DIRECTORY_SEPARATOR . $directory, 0755, true);
            if ($result) {
                $this->line("[Module {$module->name()}] Created directory {$directory}");
            } else {
                throw new Exception("[Module {$module->name()}] Cannot create directory {$directory}");
            }

            return true;
        }

        return false;
    }

    /**
     * Create files inside given module
     *
     * @param Module $module
     * @param string $stubGroup
     * @param string $subModule
     *
     * @return bool
     */
    protected function createModuleFiles(
        Module $module,
        $stubGroup,
        $subModule = null
    ) {
        $files = collect($this->laravel['modular.config']
            ->stubGroupFiles($stubGroup));

        if ($files->isEmpty()) {
            $this->warn("[Module {$module->name()}] No files created");

            return false;
        }

        $replacements = $subModule ? ['class' => $subModule] : [];

        $files->each(function ($stubFile, $moduleFile) use ($module, $stubGroup, $replacements) {
            $this->copyStubFileIntoModule($module, $stubFile, $stubGroup,
                $moduleFile, $replacements);
        });

        return true;
    }

    /**
     * Copy single stub file into module
     *
     * @param Module $module
     * @param $stubFile
     * @param $stubGroup
     * @param $moduleFile
     * @param array $replacements
     *
     * @throws Exception
     */
    protected function copyStubFileIntoModule(
        Module $module,
        $stubFile,
        $stubGroup,
        $moduleFile,
        array $replacements = []
    ) {
        $stubPath = $this->getStubGroupDirectory($stubGroup) .
            DIRECTORY_SEPARATOR . $stubFile;

        if (!$this->exists($stubPath)) {
            throw new Exception("Stub file {$stubPath} does NOT exist");
        }
        $moduleFile = $this->replace($moduleFile, $module, $replacements);

        if ($this->exists($moduleFile, $module)) {
            throw new Exception("[Module {$module->name()}] File {$moduleFile} already exists");
        }

        $this->createMissingDirectory($module, $moduleFile);
        $this->createFile($module, $stubPath, $moduleFile, $replacements);
    }

    /**
     * Creates directory for given file (if it doesn't exist)
     *
     * @param Module $module
     * @param string $file
     */
    protected function createMissingDirectory(Module $module, $file)
    {
        if (!$this->exists(($dir = dirname($file)), $module)) {
            $this->createDirectory($module, $dir);
        }
    }

    /**
     * Creates single file
     *
     * @param Module $module
     * @param string $sourceFile
     * @param string $destinationFile
     * @param array $replacements
     *
     * @throws Exception
     */
    protected function createFile(
        Module $module,
        $sourceFile,
        $destinationFile,
        array $replacements = []
    ) {
        $result = $this->laravel['files']->put($module->directory() .
            DIRECTORY_SEPARATOR . $destinationFile,
            $this->replace($this->laravel['files']->get($sourceFile), $module,
                $replacements)
        );

        if ($result === false) {
            throw new Exception("[Module {$module->name()}] Cannot create file {$destinationFile}");
        }

        $this->line("[Module {$module->name()}] Created file {$destinationFile}");
    }
}
