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
use Grixu\Synchronizer\Synchronizer;

/** 
 * @param array $inputData 
 * @param \Grixu\Synchronizer\Config\SyncConfig $config 
 * @param string|null $batchId Optional BatchID
 */
$synchronizer = new Synchronizer($inputData, $config, 'batch-id');

$synchronizer->sync();
```

### Advanced use with jobs & dividing to smaller pieces of data (built-in jobs)

```php
use Grixu\Synchronizer\Process\Actions\StartSyncAction;
use Grixu\Synchronizer\Config\SyncConfigFactory;
use Illuminate\Queue\SerializableClosure;

$obj = new StartSyncAction();
$configFactory = new SyncConfigFactory();

$config = $configFactory->make(
    loaderClass: SomeLoaderClass::class,
    parserClass: SomeParserClass::class,
    localModel: Model::class,
    foreignKey: 'fkId',
    jobsConfig: 'default',
    idsToSync: null,
    syncClosure: new SerializableClosure(function ($collection, $config) {}),
    errorHandler: new SerializableClosure(function ($exception) {})
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

#### AbstractParser

To provide support for actions you might have in your application already which parsing `Model` to `DataTransferObject`
we create extra interface `SingleElementParserInterface` and supporting it abstract class `AbstractParser` which have
implemented simple `parse()` method return map of input `Collection` via `parseElement` function.

#### Default sync handler

Synchronizer provide `SyncHandlerInterface` which you can use to create custom class which be returning
SerializableClosure containing code which will be used to handle synchronization in `SyncDataParsedJob`
and `StartSyncAction`. Such Closure should take 2 arguments:

- DtoCollection (`Illuminate\Support\Collection`)
- Config (`Grixu\Synchronizer\Config\SyncConfig`)

#### Default error handler

Also, there is an `ErrorHandlerInterface` that you can use to create custom class which be returning SerializableClosure
containing code which will be fired in catch block of standard sync method in `SyncDataParsedJob`. Such a Closure should
take 1 argument which is `Exception` class.

#### Jobs configuration

You can define you own jobs stack in synchronizer configuration (see more information below).

## Advanced use with own custom jobs

You might have another vision of how many steps synchronization should have. You can still use most Synchronizer
built-in mechanisms. In the config, you can change classes which be called in `StartSyncAction` or from built-in Jobs.

## Configuration

There are 5 options available in config file to adjust how `synchronizer` should work and behave:

```php
return [
    'sync' => [
        'timestamps' => [
            'created_at',
            'updated_at'
        ],

        'default_chunk_size' => env('SYNCHRONIZER_CHUNK_SIZE', 250),
    ],

    'checksum' => [
        'control' => env('SYNCHRONIZER_CHECKSUM_CONTROL', true),
        'field' => env('SYNCHRONIZER_CHECKSUM_FIELD', 'checksum'),
        'timestamps_excluded' => false,
    ],

    'logger' => [
        'db' => env('SYNCHRONIZER_DB_LOGGING', true),

        'notifications' => [
            'slack' => env('SYNCHRONIZER_SLACK_WEBHOOK', null),
        ]
    ],

    'jobs' => [
        'default' => [
            \Grixu\Synchronizer\Process\Jobs\LoadDataToSyncJob::class,
            \Grixu\Synchronizer\Process\Jobs\ParseLoadedDataJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class
        ],
        'load-all-and-parse' => [
            \Grixu\Synchronizer\Process\Jobs\LoadAllAndParseJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class
        ],
        'chunk-load-and-parse' => [
            \Grixu\Synchronizer\Process\Jobs\ChunkLoadAndParseJob::class,
            \Grixu\Synchronizer\Process\Jobs\SyncParsedDataJob::class
        ]
    ],

//    'handlers' => [
//        'error' => \Grixu\Synchronizer\Tests\Helpers\FakeErrorHandler::class,
//        'sync' => \Grixu\Synchronizer\Tests\Helpers\FakeSyncHandler::class
//    ],
];

```

### Sync block

In `timestamps` array you could define names of fields used as timestamp in all your models - those fields would not be
taken into consideration on checksum generation.

### Checksum block

For checking changes between local & input data, Synchronizer use comparing checksum from last sync with new
one. Checksum is generated one from fields which are used in sync and are not timestamps. To use it properly you should
an extra field in your local models which are not in DTO. Pass this field name to `field` in config file.

To turn off this feature just set up `control` as false.

Option `timestamp_excluded` when enabled do not use timestamp fields to generate a checksum.

### Logger block

`SYNCHRONIZER_DB_LOGGING` flag is designed to switch on/off logging changes in a database.

When environment variable `SYNCHRONIZER_SLACK_WEBHOOK` is not empty, batch started in `StartSyncAction` on finish
trigger `CollectionSynchronizedEvent` event. Then attached listener `CollectionSynchronizedListener` will send report
about sync to Slack webhook about changes.

### Jobs block

You can adjust jobs class names which will be used in each step of synchronization. Or even add your own steps or many
configurations. The `default` stack is mandatory to properly running `StartSyncAction`. Each stack could be build with
one or more jobs which each of one should dispatch next.

Take a look for example from `LoadDataToSyncJob`:

```php
if ($this->batch()) {
    $jobs = [];
    $jobClass = $this->config->getNextJob();

    foreach ($dataCollection as $data) {
        $jobs[] = new $jobClass($data, $this->config);
    }

    $this->batch()->add($jobs);
}
```

### Handlers block

By default, this block is disabled because it is not necessary to synchronizer work properly. It gives you possibility
to customize sync process and error handling in `SyncDataParsedJob` and `StartSyncAction`.

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
