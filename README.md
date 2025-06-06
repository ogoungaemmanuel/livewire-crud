# Livewire Crud Generator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ogoungaemmanuel/livewire-crud.svg?style=flat-square)](https://packagist.org/packages/ogoungaemmanuel/livewire-crud)

A livewire CRUD Generation package to help scaffold basic site files. Package is autoloaded as per PSR-4 autoloading in any laravel version `^5.6` so no extra config required. However is has been tested on version `^7 & ^8`. It uses **_auth_** middleware thus installs `laravel/ui` just incase you don't have any other auth mechanism, this does not mean you have to use `laravel/ui`.

## Documentation

More detailed documentation can be found at [livewire-crud](https://ogoungaemmanuel.github.io/#/)

## Installation

You can install the package via [Composer](https://getcomposer.org/):
documentation can be found at [laravel-modules](https://nwidart.com/laravel-modules/v1/installation-and-setup)

```bash
composer require nwidart/laravel-modules
composer require xslain/livewire-crud
```

## Usage

After running `composer require xslain/livewire-crud` command just run:

```bash
php artisan crud:install
composer dump-autoload
```

\*\*This command will perfom below actions:

    * Compile css/js based on `bootstrap and fontawesome/free`.
    * Run `npm install && run dev`
    * Flush *node_modules* files from you folder.

If you choose to scaffold authentication this command will run `php artisan ui:auth`
to generate Auth scaffolds using `laravel/ui` package. You can skip this step if your app has authentication already.

Then generate Crud by:

```bash
php artisan crud:generate {table-name} {module-name}
```

\*\*This command will generate:

    * Livewire Component.
    * Model.
    * Views.
    * Factory.

**Remember to customise your genertaed factories and migrations if you need to use them later
**Remember to customise template Name from Crud Command

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email info@xslain.com instead of using the issue tracker.

## Credits

-   [Ogounga Emmanuel](https://github.com/xslain)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
