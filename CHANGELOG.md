# Changelog

All notable changes to `synchronizer` will be documented in this file

## 5.2.5 - 2021-11-08

- Updated Larastan to `1.0.1`

## 5.2.4 - 2021-11-01

- Unified use of PHP_CS_Fixer, PHPStan & PHP Insights throughout repositories
- Updated CI pipeline
- Unified composer scripts throughout repositories

## 5.2.3 - 2021-10-20

- Bug fixed in fillable & excluded fields parsing in EngineConfig
- Changed way of definition excluded & fillable field to easier one

## 5.2.2 - 2021-10-01

- Another bug fixed in BelongsTo engine 

## 5.2.1 - 2021-10-01

- Bug fixed in BelongsTo engine - when checksum is off it fails when relation key not exist in database.
- Minor bugs fixed in unit test
- Updated `socius-models` to v3.0.0

## 5.2.0 - 2021-09-30

- Simplified config by removing `checksum.timestamps_excluded`, now `AbstractParser` decide to exclude timestamps or not
  based on what is in timestamps field - which could be defined independently in `EngineConfig`

## 5.1.0 - 2021-09-29

- Added `generateChecksum` which gives possibility to changed default way of generating timestamp in Parsers.
- Moved code from `Checksum::generate()` to `generateChecksum` as default implementation of generating checksum
  in `AbstractParser`.
- Added PHPInsights config
- Added dependabot

## 5.0.0 - 2021-08-31

- Huge rebuilt of sync configuration, separated SyncConfig to two separate classes.
- Changed way of declaring config in array form for StartSyncAction
- New API for StartSyncAction, check your code which use it
- Announced creation of dedicated Docs (for now WIP)

## 4.3.1 - 2021-08-25

- Minor bugs fixed

## 4.3.0 - 2021-08-25

- Changed way of providing checksum field name & timestamp fields. Now it's possible to declare separate settings for
  each sync.
- Added PHP Insights
- Added PHPStan
- Added PHP_CS_Fixer
- Added separate pipeline for PHP Insights
- Fixed code styling in whole library

## 4.2.1 - 2021-05-27

- Small optimization in `LoadAllAndParseJob`

## 4.2.0 - 2021-05-25

- Added `ChunkRestParseJob` for cases with more precise control on process of loading data.

## 4.1.0 - 2021-05-24

- Modified `CollectionSynchronizedListener` to be idempotent and queueable

## 4.0.0 - 2021-05-21

- Huge rebuilt of core classes for performance optimization
- Rebuilt structure & synchronization process
- Changed: Files & Classes structure

## 3.3.2 - 2021-05-04

- Bug fixed in LoadDataToSyncJob

## 3.3.3 - 2021-04-30

- Shame on me, typo happen this time...

## 3.3.2 - 2021-04-30

- Bug fixed in `MapFactory`

## 3.3.1 - 2021-04-30

- Updated `socius-models` & `relationship-data-transfer-object`

## 3.3.0 - 2021-04-28

- Added possibility of use extended models without using Attribute
- Added possibility to synchronize collection of arrays (not only DTOs)
- Now using snake_case on model fields - conversion from camelCase used in DTOs or array is automatically
- Socius Models updated to v2 (used in test suites only)
- Change checksum generation method from `md5()` to `Hash::make()`
- Added possibility to access query builder in `AbstractLoader`
- Added 2 new configurations of job processing and 2 new Jobs

## 3.2.1 - 2021-03-31

- Added extra check in `checkRelationType()` method in `RelationshipSynchronizer`

## 3.2.0 - 2021-03-31

- Created `SynchronizeWith` attribute to provide way to make relationship sync on custom(or extended) models than in
  defined in RelationshipData
- Rebuilt method `checkModelClass` which now checking provided in constructor object through `ReflectionClass` is it
  have `SynchronizeWith` attribute with proper model name.
- Updated tests for `RelationshipSynchronizer` & `CollectionSynchronizer`
- Rebuilt the `try..catch` block on `SyncDataParsedJob`. Now all exception thrown by CollectionSynchronizer in this job
  will be handled by provided in `SyncConfig` Closure
- Renamed `SyncDataParsedJob` to `SyncParsedDataJob` (finally!)

## 3.1.2 - 2021-03-30

- Updated dependencies

## 3.1.1 - 2021-03-30

- Bug fixed in `CollectionSynchronizer`. Also added checking is Collection is not empty and filtering to eliminate empty
  entries.
- Bug fixes in `RelationshipSynchronizer`
- Added converting `Closure` to `SerializableClosure` in setters of `syncClosure` and `errorHandler` in `SyncConfig`

## 3.1.0 - 2021-03-29

- New interface: SingleElementParserInterface
- Parser conception is more prepared for other sources that SQL databases like API
- Added new block in configuration which gives possibility to fully replace one of 3 jobs which handling sync process
- Added possibility of define many jobs stack to run
- Added new methods in SyncConfig: `setCurrentJob`, `getCurrentJob` & `getNextJob`
- Added new required parameter in `SyncConfig` constructor & added auto filling it in `SyncConfigFactory`

## 3.0.0 - 2021-03-24

- Improvement in timestamps: added option in configuration though which you can decide to exclude timestamps from a
  checksum. If you do so, models will not update if only timestamps have changed. Due to it is change from default way
  of how synchronizer v2 worked, we decided to keep it off by default.

- DTO field names are no longer checked as timestamps
- Added Jobs fo handling sync in queues
- An updated README with example how to use Jobs to sync big bunch of data in queues.
- A created interfaces to provide customization of data loading & parsing
- Added `AbstractLoader` to DRY work during SQL based data loading using models with just another database connection
- Added `StartSyncAction` which start sync in batch
- Unified events by common abstract base class: `AbstractSynchronizerEvent`
- Created SyncConfig class which contains: loader, parser, local model class names, foreign key values which we want to
  synchronize and sync & error handling closures.

- Created SyncConfigFactory
- Created interfaces for making own factories of sync & error handlers and set them as default ones.
- Refactored config file

## 2.3.1 - 2021-03-17

- Bug fixed in wrong path to Slack webhook url from logging config

## 2.3.0 - 2021-02-23

- Added firing event when model is created or synchronized.

## 2.2.1 - 2021-02-19

- Minor bug fixes

## 2.2.0 - 2021-02-18

- There is no more requirement to use `DataTransferObjectCollection` instead just use `Illuminate\Support\Collection`

## 2.1.2 - 2021-02-02

- Bug fixed: added `grixu/relationship-data-transfer-object` as dependency

## 2.1.1 - 2021-01-27

- Added: not starting sync when there is no foreignKey

## 2.1.0 - 2021-01-27

- New: Relationship synchronizer
- Bug fixed in CollectionSynchronizer
- Added starting relationships sync during Collection synchronization if DTO have `relationships` data.

## 2.0.3 - 2021-01-26

- Bug fixed in CollectionSynchronizer
- Added disabling/enabled constrained checks for time os making sync

## 2.0.2 - 2021-01-26

## 2.0.1 - 2021-01-26

- Bug fix in Logger

## 2.0.0 - 2021-01-26

- Rebuilt package in most part
- New `MapEntry` class which is responsible for keep data about fields, and their in sync or not.
- `Map` class is simple collection (Laravel Collection) of `MapEntry` with 3 getters
- `MapFactory` is factory for `Map`. You can build using array map or based on Dto field names.
- Checksum generation, control, update delegate to `Checksum` class
- Responsibility for sync data from DTO to Model put into `ModelSynchronizer` class
- New feature: collection sync is realized via `CollectionSynchronizer`
- Removed SynchronizerFactory & facade

## 1.2.2 - 2021-01-20

- Updated to compatibility with PHP 8

## 1.2.1 - 2020-12-08

- Bug fixed: Added `illuminate/events` as required package

## 1.2.0 - 2020-12-08

- Added firing event when Synchronizer detects changes
- Optimized tests

## 1.1.0 - 2020-12-07

- Added calculating MD5 from fields of DTO (without timestamps excluded in the config)
- Added method for generate a map without timestamp fields in SynchronizerMap
- New options in the config to turn on md5 checksums checking, and point a field name in models which containing this
  checksum.

## 1.0.2 - 2020-12-04

- Bug fixed: Adding empty log entries to DB
- Updated tests for SynchronizerLogger

## 1.0.1 - 2020-12-04

- Updated tests for SynchronizerLogger

## 1.0.0 - 2020-11-24

- initial release
