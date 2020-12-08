# Changelog

All notable changes to `synchronizer` will be documented in this file

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
