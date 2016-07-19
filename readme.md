Laravel Simple Modules
===

This module makes managing your Laravel 5 application much more easier. No more putting dozens or hundreds of files into single directory. 

Now you can create for your Laravel 5 application multiple modules and each of them will have its own structure (in the way you decide).

## Installation

1. For Laravel 5.3+ run

   ```php   
   composer require mnabialek/laravel-simple-modules 0.2.*
   ```
        
   and for Laravel < 5.3
   
   ```php   
   composer require mnabialek/laravel-simple-modules 0.1.*
   ```     
   
   in console to install this module.   
   
2. Open `config/app.php` and: 

  * Comment line with

    ```php
    Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
     ```
    
   * Add
    
       ```php
        Mnabialek\LaravelSimpleModules\Providers\SimpleModules::class,
        Mnabialek\LaravelSimpleModules\Providers\ConsoleSupport::class,
       ```
        
       in same section (`providers`)
    
   * Add 
    
        ```php
        'SimpleModule' => Mnabialek\LaravelSimpleModules\Facades\SimpleModule::class,
        ``` 
    
        into `aliases` section
3. Run

    ```php
    php artisan vendor:publish --provider="Mnabialek\LaravelSimpleModules\Providers\SimpleModules"
    ```
    
    in your console to publish default configuration files, sample app files (published in `app/Core`) and sample stubs files

4. In default seeder `database/seeds/DatabaseSeeder` at the end of `run` method add:

    ```php
     SimpleModule::seed($this);
    ``` 

5. In `app/Providers/RouteServiceProvider.php` at the end of `map` function add

    ```php
    \SimpleModule::loadRoutes($router);
    ```

6. In `database/factories/ModelFactory.php` (this step applies only to Laravel 5.1+) add at the end of file:

    ```php
    SimpleModule::loadFactories($factory);
    ```

## Getting started

To get started run:

```php
php artisan module:make TestModule
```

This command will create your `TestModule` module structure. Open directory and look at its structure. 
As you see some files uses `app/Core` abstract classes (those file where created during installation).

First decide, whether you want to use `app/Core` files or not. If not, you can remove this directory. Go to `resources/stubs/simple-modules` and look at
sample stubs. You can now alter them depending on your needs (you can remove all usages of `app/Core` files`, create new stubs etc.

Now open `config/simplemodules.php` file, go to `stubs_groups` section and adjust `default` structure - you can specify here what `directories` and `files` should be created for default modules. When you finish, run:

```php
php artisan module:make TestModule2
```

Now this new module will be created according to your needs. Great - you created your first module as you wanted!
 
## Customization

This module is highly customizable. The concept is based on using group stubs and in each group you can define which directories should be created and which files should be created (you can omit directories if you want when in those directories you want to place files - they will be automatically created).

You can create multiple stubs groups, you can configure many things. Just go to `config/simplemodules.php` and look at sample settings and comments in this file - if you want to change them, just do it, generate new sample module (or files) and see what will happen.

For all stubs groups  the following replacements will be done for filename and file content (assuming you haven't changed default `{` and `}` separators to custom ones):
 
* `{module}` - this will be changed into module name
* `{class}` - this will be changed into used name of module/submodule/file
* `{moduleNamespace}` - this will be changed into module namespace
* `{namespace}` - this will be changed into main namespace of modules directory
* `{plural|lower}` - this will be changed into plural name of module (lowercase)      

## Available commands

### module:make

This command creates new modules. You can create one module or multiple modules at once.

Example usage:

```php
php artisan module:make Product Order
```

You can control what is created when running this command in `config/simplemodules.php` in `stubs_groups` section. You can pass optional stub group name using `--group=test` in case you want to use multiple types of modules.

### module:files

Allow to create files in module that already exists.
 
Example usage:
 
```php
php artisan module:make Product Camera Radio
```
 
You can control what is created when running this command in `config/simplemodules.php` in `stubs_groups` section. You can pass optional stub group name using `--group=test`.
 
By default it creates "submodule" in existing module.

### module:create-migration

Creates migration file in given module

Example usage:

```php
php artisan module:make-migration Product create_products_table
```

You can also use optional `--type` and `--table` options to set table and type of migration (you can of course create own types if you want) in order to create migration with template for given type, for example:

```php
php artisan module:make-migration Product create_camera_table --table=cameras --type=create
```

it will create migration that is of type `create` - so in `up` method there will be creating `cameras` table and in `down` method deleting `cameras` table
 
### module:seed

Runs main seeder for given modules. You need to remember that only main seeder will be launched. In case you have multiple seeders in single module, you should manually run extra seeders in main module seeder.

Example usage:
  
```php
php artisan module:seed Product Order
```  

### migrate

This module overrides default `Artisan` command. When running this command all migrations will be run (both general and for all active modules)

Example usage:
  
```php
php artisan migrate
```  

### db:seed

This module overrides default `Artisan` command. When running this command all main modules seeds will be run

Example usage:
  
```php
php artisan db:seed
``` 

### Licence

This package is licenced under the [MIT license](http://opensource.org/licenses/MIT)
