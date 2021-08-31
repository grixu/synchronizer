# Synchronizer

Simple library for sync data between two data sources (database to database or API to database). 

* 📈 **Asynchronous & Scalability**: prepared to work async by using Laravel Queues system
* 🔒 **Strict data types**: assumes to use DTO objects as input data. Using of spatie/data-transfer-objects is highly recommended.
* 🔩 **Adaptable**: providing interfaces for customizing loading, parsing, pre-sync & error reporting.
* 📂 **Flexibility**: library structure allows using just synchronization engine
* 🎛 **Configurable**: Wide possibilities of configuration for each synchronization separately
* 📚 **Prepared for different scenarios**: have 3 different loading-parsing-syncing scenarios on-board.
* 📝 **Loggable**: it allows save synchronization' logs to database
* 📲 **Notifiable**: it provides Slack notifications after sync is done

## Installation

You can install the package via composer:

```bash
composer require grixu/synchronizer
```

## Usage

Details will be described in [Synchronizer Docs](https://grixu.github.io/synchronizer/docs) (both in Polish and English)

## Testing

Before you start running test, please create .sqlite database which contains example tables from a database you would
like to sync. More information about it, you can find in: `tests/Helpers/SyncTestCase`. Test which extending
that `SyncTestCase` need access to this database.

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email mg@grixu.dev instead of using the issue tracker.

## Credits

- [Mateusz Gostański](https://github.com/grixu)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
