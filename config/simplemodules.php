<?php

return [
    /**
     * Directory where new modules will be created
     */
    'directory' => 'app/Modules',

    /**
     * Namespace for new modules
     */
    'namespace' => 'App\\Modules',

    /**
     * If set to true, it will convert module name to studly caps case
     * It's recommended setting otherwise you will need to be very careful
     * what you are typing when generating new modules
     */
    'normalize_module_name' => true,

    /**
     * Stubs settings
     */
    'stubs' => [
        /**
         * Path where all stubs groups are located
         */
        'path' => base_path('resources/stubs/simple-modules'),

        /**
         * Default stub groups used for module and files - they must match
         * keys from stub_groups
         */
        'module_default_group' => 'default',
        'files_default_group' => 'submodule',
    ],

    /**
     * Creating migration settings
     */
    'module_migrations' => [
        /*
         * Available types of migrations stubs to use         
         */
        'types' => [
            'default' => 'migration.php.stub',
            'create' => 'migration_create.php.stub',
            'edit' => 'migration_edit.php.stub',
        ],
        /**
         * Default migration type (if none specified)
         */
        'default_type' => 'default',

        /*
         * Path (inside module) where migrations file should be created
         */
        'path' => 'Database/Migrations',
    ],

    /**
     * Module seeding settings
     */
    'module_seeding' => [
        /**
         * Path (inside module) where main seeder file should be created
         */
        'path' => 'Database/Seeds',

        /**
         * Seeder filename
         */
        'filename' => '{class}DatabaseSeeder.php',

        /**
         * Seeder namespace (it will be automatically prefixed with modules
         * namespace)
         */
        'namespace' => 'Database\\Seeds',
    ],

    /**
     * Module routing settings
     */
    'module_routing' => [
        /**
         * Routing file path and name (inside module)
         */
        'file' => 'Http/routes.php',

        /**
         * Routing group controller namespace (this namespace will be
         * automatically added to all controllers defined inside above routing
         * file
         */
        'route_group_namespace' => 'Http\\Controllers',
    ],

    /**
     * Settings for module model factories
     */
    'module_factories' => [
        /**
         * Model factory file path and name (inside module)
         */
        'file' => 'Database/Factories/{class}ModelFactory.php',
    ],

    /**
     * Settings for module service providers
     */
    'module_service_providers' => [
        /**
         * Path (inside module) where service provider file should be created
         */
        'path' => 'Providers',

        /**
         * Service provider filename
         */
        'filename' => '{class}ServiceProvider.php',

        /**
         * Service provider namespace (it will be automatically prefixed with modules
         * namespace)
         */
        'namespace' => 'Providers',
    ],

    /**
     * Settings for module creation
     */
    'module_make' => [
        /**
         * Whether after creating new module this file should be filled with new
         * module name
         */
        'auto_add' => true,

        /**
         * Pattern what should be searched in this file to add here new module
         * (don't change it unless you know what you are doing)
         */
        'pattern' => "#(modules'\s*=>\s*\[\s*)(.*)(^\s*\/\/\s* end of modules \(don't remove this comment\)\s*])#sm",

        /**
         * Module template - what will be added in this file when new module
         * is created
         */
        'module_template' => "        '{class}' => ['active' => true, 'routes' => true,],\n",
    ],

    /**
     * List of available modules in format:
     * 'moduleName' => ['active' => true, 'routes' => true, 'factories' => true, 'provider' => true],
     * ('active', `routes' `factories`, `provider` are optional but when
     * used and filled correctly they will improve performance.
     *
     * If you fill them manually extra checks won't be done. So for example if
     * you set `provider` => false and this module has service provider, it
     * won't be loaded unless you change it to  `provider` => true
     */
    'modules' => [
        // end of modules (don't remove this comment)
    ],

    /**
     * Here we define what directories and what files should be created for
     * each stub groups. By default directory is the same as stub group, however
     * we could define another using stub_directory (see submodule group)
     */
    'stubs_groups' => [
        'default' => [
            'directories' => [
                'Models',
                'Repositories',
                'Services',
                'Http/Controllers',
                'Http/Requests',
                'Database/Migrations',
                'Database/Seeds',
                'Database/Factories',
            ],
            'files' => [
                'Models/.gitkeep' => '.gitkeep.stub',
                'Repositories/.gitkeep' => '.gitkeep.stub',
                'Services/.gitkeep' => '.gitkeep.stub',
                'Http/Controllers/.gitkeep' => '.gitkeep.stub',
                'Http/Requests/.gitkeep' => '.gitkeep.stub',
                'Database/Migrations/.gitkeep' => '.gitkeep.stub',
                'Database/Seeds/.gitkeep' => '.gitkeep.stub',
                'Database/Factories/.gitkeep' => '.gitkeep.stub',
                'Http/Controllers/{class}Controller.php' => 'Controller.php.stub',
                'Http/Requests/{class}Request.php' => 'Request.php.stub',
                'Models/{class}.php' => 'Model.php.stub',
                'Http/routes.php' => 'routes.php.stub',
                'Database/Seeds/{class}DatabaseSeeder.php' => 'DatabaseSeeder.php.stub',
                'Database/Factories/{class}ModelFactory.php' => 'ModelFactory.php.stub',
                'Repositories/{class}Repository.php' => 'Repository.php.stub',
                'Services/{class}Service.php' => 'Service.php.stub',
            ],
        ],
        'submodule' => [
            'stub_directory' => 'default',
            'files' => [
                'Http/Controllers/{class}Controller.php' => 'Controller.php.stub',
                'Models/{class}.php' => 'Model.php.stub',
                'Database/Seeds/{class}DatabaseSeeder.php' => 'DatabaseSeeder.php.stub',
                'Repositories/{class}Repository.php' => 'Repository.php.stub',
                'Services/{class}Service.php' => 'Service.php.stub',
            ],
        ],
        'model' => [
            'stub_directory' => 'default',
            'files' => [
                'Models/{class}.php' => 'Model.php.stub',
            ],
        ],
        'controller' => [
            'stub_directory' => 'default',
            'files' => [
                'Http/Controllers/{class}Controller.php' => 'Controller.php.stub',
            ],
        ],
        'repository' => [
            'stub_directory' => 'default',
            'files' => [
                'Repositories/{class}Repository.php' => 'Repository.php.stub',
            ],
        ],
        'service' => [
            'stub_directory' => 'default',
            'files' => [
                'Services/{class}Service.php' => 'Service.php.stub',
            ],
        ],
        'factory' => [
            'stub_directory' => 'default',
            'files' => [
                'Database/Factories/{class}ModelFactory.php' => 'ModelFactory.php.stub',
            ],
        ],
        'provider' => [
            'stub_directory' => 'default',
            'files' => [
                'Providers/{class}ServiceProvider.php' => 'ServiceProvider.php.stub',
            ],
        ],
    ],

    /**
     * Separators for replacements
     */
    'separators' => [
        'start' => '{',
        'end' => '}',
    ],

    /**
     * Providers that will be overridden by custom ones (you should not change
     * them unless you have very good reason to do that)
     */
    'providers' => [
        'Illuminate\Database\MigrationServiceProvider' =>
            'Mnabialek\LaravelSimpleModules\Providers\Migration',
    ],
];
