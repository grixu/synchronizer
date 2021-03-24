# Changelog

All notable changes to `synchronizer` will be documented in this file

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
