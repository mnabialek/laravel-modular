## Laravel Modular

[![Build Status](https://travis-ci.org/mnabialek/laravel-modular.svg?branch=master)](https://travis-ci.org/mnabialek/laravel-modular)
[![Coverage Status](https://coveralls.io/repos/github/mnabialek/laravel-modular/badge.svg?branch=master)](https://coveralls.io/github/mnabialek/laravel-modular?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mnabialek/laravel-modular/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mnabialek/laravel-modular/?branch=master)
[![StyleCI](https://styleci.io/repos/48952569/shield)](https://styleci.io/repos/48952569)

This module makes managing your Laravel 5 application much more easier. No more putting dozens or hundreds of files into single directory. 

Now you can create for your Laravel 5 application multiple modules and each of them will have its own structure (in the way you decide).

### Supported version

To install in Laravel **5.3+** use this branch, however to install in Laravel < 5.3 please refer to **[version 0.1](https://github.com/mnabialek/laravel-modular/tree/0.1)**.

### Installation

1. For Laravel 5.3+ run

   ```php   
   composer require mnabialek/laravel-modular 0.2.*
   ``` 
   
   in console to install this module.   
   
2. Open `config/app.php` and: 
   
   * Add
    
       ```php
        Mnabialek\LaravelModular\Providers\ModularServiceProvider::class,
       ```
        
       in same section (`providers`)
    
   * Add 
    
        ```php
        'Modular' => Mnabialek\LaravelModular\Facades\Modular::class,
        ``` 
    
        into `aliases` section
3. Run

    ```php
    php artisan vendor:publish --provider="Mnabialek\LaravelModular\Providers\ModularServiceProvider"
    ```
    
    in your console to publish default configuration files, sample app files (published in `app/Core`) and sample stubs files

4. In default seeder `database/seeds/DatabaseSeeder` at the end of `run` method add:

    ```php
     Modular::seed($this);
    ``` 

5. In `app/Providers/RouteServiceProvider.php` at the end of `map` function add

    ```php
    \Modular::loadRoutes($this->app['router'], 'web');
    \Modular::loadRoutes($this->app['router'], 'api');
    ```

6. In `database/factories/ModelFactory.php` (this step applies only to Laravel 5.1+) add at the end of file:

    ```php
    Modular::loadFactories($factory);
    ```

### Getting started

To get started run:

```php
php artisan module:make TestModule
```

This command will create your `TestModule` module structure. Open directory and look at its structure. 
As you see some files uses `app/Core` abstract classes (those file where created during installation).

First decide, whether you want to use `app/Core` files or not. If not, you can remove this directory. Go to `resources/stubs/modular` and look at
sample stubs. You can now alter them depending on your needs (you can remove all usages of `app/Core` files`, create new stubs etc.

Now open `config/modular.php` file, go to `stubs_groups` section and adjust `default` structure - you can specify here what `directories` and `files` should be created for default modules. When you finish, run:

```php
php artisan module:make TestModule2
```

Now this new module will be created according to your needs. Great - you created your first module as you wanted!
 
### Customization

This module is highly customizable. The concept is based on using group stubs and in each group you can define which directories should be created and which files should be created (you can omit directories if you want when in those directories you want to place files - they will be automatically created).

You can create multiple stubs groups, you can configure many things. Just go to `config/modular.php` and look at sample settings and comments in this file - if you want to change them, just do it, generate new sample module (or files) and see what will happen.

For all stubs groups  the following replacements will be done for filename and file content (assuming you haven't changed default `{` and `}` separators to custom ones):
 
* `{module}` - this will be changed into module name
* `{class}` - this will be changed into used name of module/submodule/file
* `{moduleNamespace}` - this will be changed into module namespace
* `{namespace}` - this will be changed into main namespace of modules directory
* `{plural|lower}` - this will be changed into plural name of module (lowercase)
 
#### Routing customization

By default for each module you can load 2 routing files - `web.php` and `api.php` if you followed installation instructions. However, you might also decide to use single routing file for each module. In this case use
  
```
\Modular::loadRoutes($this->app['router']);
```  

only in step 5 of installation.

You can also decide to put the whole routing files in groups as defaults in Laravel 5.3. In this case you can do it also in `RouteServiceProvider` - in such case please remember to alter your routes stubs to not apply same middleware twice because it will cause unexpected issues in your Laravel application.

### Available commands

#### module:make

This command creates new modules. You can create one module or multiple modules at once.

Example usage:

```php
php artisan module:make Product Order
```

You can control what is created when running this command in `config/modular.php` in `stubs_groups` section. You can pass optional stub group name using `--group=test` in case you want to use multiple types of modules.

#### module:files

Allow to create files in module that already exists.
 
Example usage:
 
```php
php artisan module:make Product Camera Radio
```
 
You can control what is created when running this command in `config/simplemodules.php` in `stubs_groups` section. You can pass optional stub group name using `--group=test`.
 
By default it creates "submodule" in existing module.

#### module:create-migration

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
 
#### module:seed

Runs main seeder for given modules. You need to remember that only main seeder will be launched. In case you have multiple seeders in single module, you should manually run extra seeders in main module seeder.

Example usage:
  
```php
php artisan module:seed Product Order
```  

#### migrate

This module register modules paths so when you run default Laravel `migrate` command all migrations will be run (both general and for all active modules)

Example usage:
  
```php
php artisan migrate
```  

#### db:seed

If you done all steps in `Installation` section when you run `db:seed` command all main seeds from active modules will be run

Example usage:
  
```php
php artisan db:seed
```
 
### Optimization

By default (you can customize it in config file), all modules are created with 2 options - `active` and `routes` - by default they are both set to `true`. The general rule for all modules options is like this - if option is set for module in configuration file, it will be used, otherwise some extra checks will be made to calculate. It does not apply to `active` option - in case it's missing it's assumed to be `true`.
 
Available options for modules:
 
 * `active` - whether module is active
 * `provider` - whether module has service provider
 * `factory` - whether module has model factory
 * `routes` - whether module has `routes.php` file
 * `seeder` - whether module has seeder file
 
In addition to use custom routing files, you can use also additional settings:

 * `api_routes` - whether module has `api.php` routing file
 * `web_routes` - whether module has `web.php` routing file 
 
Prefix of option should match the name of routing file, if you would like additional `extra.php` routing file, you could use `extra_routes` option.

Be aware in case option set in configuration file won't be valid, some unexpected situations might happen. For example, if you set `provider` to `false` but later you will add service provider to module, it won't he loaded unless you change `provider` option to `true` or you remove it completely from configuration file for this module.

### Release notes

Please refer to **[Changelog](https://github.com/mnabialek/laravel-modular/blob/master/CHANGELOG.md)**

#### Licence

This package is licenced under the [MIT license](http://opensource.org/licenses/MIT)
