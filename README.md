# Synchronizer

Engine for sync data between two systems (eg. Comarch CDN XL and some custom Laravel app or API). This package allows to exclude some fields from synchronization but still, you can get data from excluded fields if local field is empty. There is logging of changes to database - so you can easily check what was changed and on which fields. 

## Installation

You can install the package via composer:

```bash
composer require grixu/synchronizer
```

## Usage

```php
// You can use facade as well
use Grixu\Synchronizer\Synchronizer;

// At first create assoc array which be map of local field name
// in a key, and foreign field name in a value
$map = [
    'name' => 'name',
    'updated_at' => 'updatedAt'
];
// for eg. $language is local model, $languageData is DTO class 
// which contains data from other (foreign) source (API, database etc.)
$synchronizer = new Synchronizer($map, $language, $languageData);
$synchronizer->sync();
```

### Event firing

When Synchronizer detects changes will fire a `SynchronizerDetectChangesEvent`. This event takes
one argument - local model. So you can easily add a listener in your app and for eg. notify 
another microservices about data update.

### Configuration

There are 5 options available in config file to adjust how `synchronizer` should work and behave:
```php
return [
    'send_slack_sum_up' => env('SYNCHRONIZER_SLACK_SUM_UP', true),
    'db_logging' => env('SYNCHRONIZER_DB_LOGGING',true),
    'log_turned_off_fields' => [
        'updated_at'
    ],
    'md5_control' => env('SYNCHRONIZER_MD5_CONTROL', true),
    'md5_local_model_field' => env('SYNCHRONIZER_MD5_FIELD', 'checksum'),
];
```

Option `send_slack_sum_up` should be used to block sending daily sum ups to Slack channel. You could still use Slack notifications in your app, but simply turn off notifications from this package.

`db_logging` flag designed to switch on/off logging changes in local model to database.

In `log_turned_off_fields` array you could define names of fields used as timestamp - which should not be logged as change in local model.

For simplify checking changes between local model and foreign DTO, Synchronizer have a built-in
 feature: comparing md5 generated from last sync with newly generated based on selected fields from
 foreign DTO. In `Synchronizer` class, method `sync()` on the very beginning call method 
 `checkChanges()` to calculate and verify md5 checksums. 

To turn on this feature just set up `SYNCHRONIZER_MD5_CONTROL` and `SYNCHRONIZER_MD5_FIELD` in your
 `.env` file and add a field (name of this field you just set up in `SYNCHRONIZER_MD5_FIELD`)
 contains md5 checksum in every model you use in synchronization process.

### Artisan commands

We have 4 available commands:
- `synchronizer:add` - used to add new excluded field at specified model
- `synchronizer:list` - to list and delete excluded fields
- `synchronizer:send` - for sending daily sum ups about amounts of changes in models
- `synchronizer:set` - to set timestamp (beginning) for daily sum ups. This command should be used after cache purge. Due to last sum ups send timestamp located in a cache. 

### Testing

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
