# Synchronizer

Simple library for sync data between two systems (eg. Comarch CDN XL and yours Laravel app). This package allows to
exclude some fields from synchronization using simple artisan commands. Yet, you can mark option to filling data from
source if your local resource is empty. All changes done on models are logged to database, so you can easily check what
and when was updated.

## Installation

You can install the package via composer:

```bash
composer require grixu/synchronizer
```

## Usage

### Simple synchronization

```php
use Grixu\Synchronizer\CollectionSynchronizer;
use Grixu\Synchronizer\RelationshipSynchronizer;

/** 
 * @param $dtoCollection \Spatie\DataTransferObject\DataTransferObjectCollection
 * @param string $model Model class name
 * @param string $fk Foreign key in DTO used to find your local models 
 */
$synchronizer = new CollectionSynchronizer($dtoCollection, Model::class, 'fk');

/**
* @param array|null $map Assoc array with dtoFieldName => modelFieldName
 */
$synchronizer->sync($map);


// In case you have separated DTO with relationships based on
// RelationshipDataTransferObject you can sync them using
// RelationshipSynchronizer, like below:
$relationshipSynchronizer = new RelationshipSynchronizer($modelInstance);
$relationshipSynchronizer->sync($dtoCollectionWithRelationships);
```

### Advanced use with jobs & dividing to smaller pieces of data

```php
use Grixu\Synchronizer\Actions\StartSyncAction;

$obj = new StartSyncAction();
$config = new \Grixu\Synchronizer\Config\SyncConfig(
    loaderClass: SomeLoaderClass::class,
    parserClass: SomeParserClass::class,
    localModel: Model::class,
    foreignKey: 'fkId',
    idsToSync: null,
    syncClosure: new \Illuminate\Queue\SerializableClosure(function ($collection, $config) {}),
    errorHandler: new \Illuminate\Queue\SerializableClosure(function ($exception) {})
);

$obj->execute($config, 'sync_queue');
```

Quite a bunch of code, let's explain it!

Whole process of sync we divide into 3 steps:

- loading data & splitting it to chunks
- parsing each chunk of loaded data to DTO Collection
- syncing those collections of DTO to local database

To achieve that goal, we create 3 Job classes:

- `LoadDataToSyncJob`
- `ParseLoadedDataJob`
- `SyncDataParsedJob`

An every data source have different origin and way of parsing incoming data to DTO. To provide way of customize those
two factors, we provide two interfaces:

- `LoaderInterface`
- `ParserInterface`

You should create your own loader & parser classes which will implement those interfaces accordingly.

Then you create `SyncConfig` object, which take loader & parser classes as constructor arguments to pass them to Jobs
which handling loading, parsing to use them during whole process. Just run `execute` method to start whole sync in
single batch. You can also provide a custom name of queue which should be used in this process, like in
e.g. `sync_queue`.

Remember: you need to run queue workers by yourself! :)

#### AbstractLoader

If you synchronize data from database to database you can use built-in `AbstractLoader` which is prepared for handling
such type of operations. Just create your own loader class extending `AbstractLoader` and add `buildQuery()` method.

## Configuration

There are 5 options available in config file to adjust how `synchronizer` should work and behave:

```php
return [
    'send_slack_sum_up' => env('SYNCHRONIZER_SLACK_SUM_UP', false),
    'db_logging' => env('SYNCHRONIZER_DB_LOGGING',true),
    'timestamps' => [
        'updated_at'
    ],
    'checksum_control' => env('SYNCHRONIZER_MD5_CONTROL', true),
    'checksum_field' => env('SYNCHRONIZER_MD5_FIELD', 'checksum'),
];
```

Option `send_slack_sum_up` when have true value, `CollectionSynchronizer` will send information to Slack channel about
all creation/update changes.

`db_logging` flag designed to switch on/off logging changes in a database.

In `timestamps` array you could define names of fields used as timestamp - which should not be logged as changes.

For checking changes between local model and foreign DTO, Synchronizer use comparing checksum from last sync with new
one. Checksum is generated one from fields which are used in sync and are not timestamps. To use it properly you should
an extra field in your local models which are not in DTO. Pass this field name to `checksum_field` in config file.

To turn off this feature just set up `checksum_control` as false.

### Artisan commands

We have 2 available commands:

- `synchronizer:add` - used to add new excluded field at specified model
- `synchronizer:list` - to list and delete excluded fields

### Testing

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
