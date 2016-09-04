## Release notes

### v0.2.3 (2016-09-04)

- fix using absolute paths to verify whether files exists
- use require instead of require_once to allow usage in PhpUnit tests
- fix in loading factories

### v0.2.2 (2016-09-01)

- fix invalid path for created migrations

### v0.2.1 (2016-08-30)

- fix invalid route prefix usage

### v0.2 (2016-08-24)

- complete code rewrite
- added unit tests
- using explicit module names (exact they were typed, case sensitive)
- optimization for Laravel 5.3 (easier installation and using migrations from custom paths)
- removed `module:migrate` command
- allow to use multiple routes for module
- change package name from `mnabialek/laravel-simple-modules` to `mnabialek/laravel-modular`
- updated documentation with extra details about modules settings
- fixed publishing stubs files into invalid directory when config file does not exist yet

### v0.1.1 (2016-08-11)

- change package name from `mnabialek/laravel-simple-modules` to `mnabialek/laravel-modular`
- fixed publishing stubs files into invalid directory when config file does not exist yet

### v0.1 (2016-04-21)

- initial release
 
