<?php

namespace Mnabialek\LaravelSimpleModules\Console\Traits;

use Mnabialek\LaravelSimpleModules\Traits\Normalizer;

trait ModuleCreator
{
    use Normalizer;

    /**
     * Get directory for given stub group
     *
     * @param string $group
     *
     * @return string
     */
    protected function getStubGroupDirectory($group)
    {
        return $this->normalizePath($this->module->config('stubs.path')) .
        DIRECTORY_SEPARATOR .
        $this->module->config("stubs_groups.{$group}.stub_directory", $group);
    }

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
        $stubGroups = $this->module->config('stubs_groups', []);
        if (!array_key_exists($stubGroup, $stubGroups)) {
            throw new \Exception("Stub group {$stubGroup} does not exist. You need to add this to stubs_groups");
        }

        // then verify whether this stub group exists
        $directory = $this->getStubGroupDirectory($stubGroup);
        if (!$this->exists($directory)) {
            throw new \Exception("Stub group directory {$directory} does not exist");
        }
    }

    /**
     * Verify whether module config was published
     *
     * @throws \Exception
     */
    protected function verifyConfigExistence()
    {
        if (!$this->exists($this->module->getConfigFilePath())) {
            throw new \Exception('Config file does not exists. Please run php artisan vendor:publish (see docs for details)');
        }
    }

    /**
     * Verify whether given file or directory exists
     *
     * @param string $path
     *
     * @return bool
     */
    protected function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Get stub group - either from input of default one
     *
     * @param string $defaultKey
     *
     * @return mixed
     */
    protected function getStubGroup($defaultKey = 'module_default_group')
    {
        return $this->option('group') ?: $this->module->config('stubs.' .
            $defaultKey);
    }

    /**
     * Creates module directories
     *
     * @param string $module
     * @param string $moduleDirectory
     * @param string $stubGroup
     */
    protected function createModuleDirectories(
        $module,
        $moduleDirectory,
        $stubGroup
    ) {
        $stubConfig = $this->module->config("stubs_groups.{$stubGroup}", []);

        if (array_key_exists('directories', $stubConfig)) {
            $dirs = $stubConfig['directories'];

            foreach ($dirs as $dir) {
                $dirToCreate = $moduleDirectory . DIRECTORY_SEPARATOR . $dir;
                $this->createDirectory($dirToCreate, $module);
            }
        } else {
            $this->warn("[Module {$module}] No explicit directories created");
        }
    }

    /**
     * Creates directory
     *
     * @param string $dirToCreate
     * @param string $module
     */
    protected function createDirectory($dirToCreate, $module)
    {
        if (!$this->exists($dirToCreate)) {
            $this->line("[Module {$module}] Creating directory {$dirToCreate}");
            mkdir($dirToCreate, 0777, true);
        } else {
            $this->comment("[Module {$module}] Directory {$dirToCreate} already exists");
        }
    }

    /**
     * Create module files
     *
     * @param string $module
     * @param string $moduleDirectory
     * @param string $stubGroup
     *
     * @return bool
     */
    protected function createModuleFiles($module, $moduleDirectory, $stubGroup)
    {
        return $this->copyModuleFiles($module, $moduleDirectory, $stubGroup);
    }

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
    protected function createSubModuleFiles(
        $module,
        $moduleDirectory,
        $stubGroup,
        $subModule
    ) {
        return $this->copyModuleFiles($module, $moduleDirectory, $stubGroup,
            $subModule);
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
        $moduleDirectory,
        $stubGroup,
        $subModule = null
    ) {
        $checkExistence = false;
        $replacements = [];
        if ($subModule !== null) {
            $checkExistence = true;
            $replacements = ['class' => $subModule];
        }

        $stubDirectory = $this->getStubGroupDirectory($stubGroup);
        $stubConfig = $this->module->config("stubs_groups.{$stubGroup}", []);

        if (array_key_exists('files', $stubConfig)) {
            $files = $stubConfig['files'];
            $toCopy = count($files);

            foreach ($files as $moduleFile => $stubFile) {
                $copied =
                    $this->copyStubFileIntoModule($stubFile, $stubDirectory,
                        $moduleFile, $moduleDirectory, $module, $checkExistence,
                        $replacements);

                if ($copied) {
                    --$toCopy;
                }
            }

            // no files copied - return false
            if ($toCopy == count($files)) {
                return false;
            }
        } else {
            $this->warn("[Module {$module}] No files created");
        }

        return true;
    }

    /**
     * Creates directory for given file (if it doesn't exist)
     *
     * @param string $file
     * @param string $moduleDirectory
     * @param string $module
     */
    protected function createMissingDirectory($file, $moduleDirectory, $module)
    {
        $newDir = $moduleDirectory . DIRECTORY_SEPARATOR . dirname($file);

        if (!$this->exists($newDir)) {
            $this->createDirectory($newDir, $module);
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
        $stubFile,
        $stubDirectory,
        $moduleFile,
        $moduleDirectory,
        $module,
        $checkExistence,
        array $replacements = [],
        $final = false
    ) {
        $stubPath = $stubDirectory . DIRECTORY_SEPARATOR . $stubFile;

        if ($this->exists($stubPath)) {
            $moduleFile = $this->replace($moduleFile, $module, $replacements);
            $modulePath = $moduleDirectory . DIRECTORY_SEPARATOR . $moduleFile;

            if ($checkExistence && $this->exists($modulePath)) {
                $this->warn("[Module $module] File {$modulePath} already exists");

                return false;
            }

            $this->createMissingDirectory($moduleFile, $moduleDirectory,
                $module);
            $this->createFile($stubPath, $modulePath, $module, $replacements);
        } else {
            $this->warn("Stub file {$stubPath} does NOT exist");

            if ($final) {
                return false;
            }
        }

        return true;
    }

    /**
     * Creates single file
     *
     * @param string $sourceFile
     * @param string $destinationFile
     * @param string $module
     * @param array $replacements
     */
    protected function createFile(
        $sourceFile,
        $destinationFile,
        $module,
        array $replacements = []
    ) {
        file_put_contents($destinationFile,
            $this->replace(file_get_contents($sourceFile), $module,
                $replacements)
        );

        $this->line("[Module {$module}] Creating file {$destinationFile}");
    }
}
