# Changelog

All notable changes to `synchronizer` will be documented in this file

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
- New options in the config to turn on md5 checksums checking, and point a field name in models
which containing this checksum.

## 1.0.2 - 2020-12-04

- Bug fixed: Adding empty log entries to DB
- Updated tests for SynchronizerLogger

## 1.0.1 - 2020-12-04

- Updated tests for SynchronizerLogger

## 1.0.0 - 2020-11-24

- initial release
