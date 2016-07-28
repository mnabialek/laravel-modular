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
    private function getStubGroupDirectory($group)
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
        if (!$this->exists($this->laravel['modular.config']->configFilePath())) {
            throw new Exception('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');
        }
    }

    /**
     * Verify whether given file or directory exists
     *
     * @param string $path
     *
     * @return bool
     */
    private function exists($path)
    {
        return $this->laravel['files']->exists($path);
    }

    /**
     * Get stub group - either from input of default one
     *
     * @return mixed
     */
    protected function getStubGroup()
    {
        return $this->option('group') ?: $this->config->getStubsDefaultGroup();
    }

    protected function getFilesStubGroup()
    {
        return $this->option('group') ?: $this->config->getFilesStubsDefaultGroup();
    }

    /**
     * Creates module directories
     *
     * @param Module $module
     * @param string $stubGroup
     */
    protected function createModuleDirectories(
        Module $module,
        $stubGroup
    ) {
        $directories = collect($this->config
            ->getStubGroupDirectories($stubGroup))->unique();

        if ($directories->isEmpty()) {
            $this->warn("[Module {$module->getName()}] No explicit directories created");

            return;
        }

        $directories->each(function ($directory) use ($module) {
            $this->createDirectory($module, $module->getDirectory() .
                DIRECTORY_SEPARATOR . $directory);
        });
    }

    /**
     * Creates directory
     *
     * @param Module $module
     * @param string $directory
     */
    protected function createDirectory(Module $module, $directory)
    {
        if (!$this->exists($directory)) {
            mkdir($directory, 0777, true);
            $this->line("[Module {$module->getName()}] Created directory {$directory}");
        }
    }

    /**
     * Create module files
     *
     * @param Module $module
     * @param string $moduleDirectory
     * @param string $stubGroup
     *
     * @return bool
     */
//    protected function createModuleFiles($module, $stubGroup)
//    {
//        return $this->copyModuleFiles($module, $stubGroup);
//    }

    /**
     * Create submodule files inside given module
     *
     * @param string $module
     * @param string $moduleDirectory
     * @param string $stubGroup
     * @param string $subModule
     *
     * @return bool
     */
    protected function createModuleFiles(
        $module,
        $stubGroup,
        $subModule = null
    ) {
        return $this->copyModuleFiles($module, $stubGroup, $subModule);
    }

    /**
     * Copy stub files into given module
     *
     * @param string $module
     * @param string $moduleDirectory
     * @param string $stubGroup
     * @param null $subModule
     *
     * @return bool
     */
    protected function copyModuleFiles(
        $module,
        $stubGroup,
        $subModule = null
    ) {
        $replacements = [];
        if ($subModule !== null) {
            $replacements = ['class' => $subModule];
        }

        $files = $this->config->getStubGroupFiles($stubGroup);

        if ($files->isEmpty()) {
            $this->warn("[Module {$module}] No files created");

            return;
        }

        $files->each(function ($stubFile, $moduleFile) use ($module, $stubGroup, $replacements) {
            $this->copyStubFileIntoModule($module, $stubFile, $stubGroup,
                $moduleFile, $replacements);
        });
    }

    /**
     * Creates directory for given file (if it doesn't exist)
     *
     * @param Module $module
     * @param string $file
     */
    protected function createMissingDirectory(Module $module, $file)
    {
        if (!$this->exists($dir = dirname($file))) {
            $this->createDirectory($module, $dir);
        }
    }

    /**
     * Copy single stub file into module
     *
     * @param string $stubFile
     * @param string $stubDirectory
     * @param string $moduleFile
     * @param string $moduleDirectory
     * @param string $module
     * @param bool $checkExistence
     * @param array $replacements
     * @param bool $final
     *
     * @return bool
     */
    protected function copyStubFileIntoModule(
        Module $module,
        $stubFile,
        $stubGroup,
        $moduleFile,
        array $replacements = []
    ) {
        $stubPath =
            $this->getStubGroupDirectory($stubGroup) . DIRECTORY_SEPARATOR .
            $stubFile;

        if ($this->exists($stubPath)) {
            $moduleFile = $this->replace($moduleFile, $module, $replacements);

            if ($this->exists($moduleFile)) {
                throw new Exception("[Module $module] File {$moduleFile} already exists");
            }

            $this->createMissingDirectory($module, $moduleFile);
            $this->createFile($module, $stubPath, $moduleFile, $replacements);
        } else {
            throw new Exception("Stub file {$stubPath} does NOT exist");
        }
    }

    /**
     * Creates single file
     *
     * @param string $sourceFile
     * @param string $destinationFile
     * @param Module $module
     * @param array $replacements
     */
    protected function createFile(
        $module,
        $sourceFile,
        $destinationFile,
        array $replacements = []
    ) {
        file_put_contents($destinationFile,
            $this->replace(file_get_contents($sourceFile), $module,
                $replacements)
        );

        $this->line("[Module {$module}] Created file {$destinationFile}");
    }
}
