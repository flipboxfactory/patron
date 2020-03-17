Changelog
=========

## 2.2.2 - 2020-03-17
### Fixed
- Merged in settings update provided by [PR #6](https://github.com/flipboxfactory/patron/pull/6).  Thanks Mark.

## 2.2.1 - 2019-01-07
### Fixed
- Admins were unable to create a new token via the admin panel when admin changes are not allowed.  
- Removed button / forms when admin changes are not allowed

## 2.2.0 - 2019-12-30
### Changed
- `patronProviders` project config key is now saved under 'plugins.patron.providers'.  When upgrading, manually moving this key is recommended.
- `patronTokens` project config key is now saved under 'plugins.patron.tokens'.  When upgrading, manually moving this key is recommended.
- Tokens can be saved even if saving to project config is disabled.

## 2.1.3 - 2019-05-14
### Fixed
- Check if columns already exist prior to adding them via migration.

## 2.1.2 - 2019-05-13
### Fixed
- Error setting project config event on Craft before v3.1.20

## 2.1.1 - 2019-05-08
### Fixed
- Check if columns already exist prior to adding them via migration.

## 2.0.0 - 2019-01-10
### Changed
- Updated dependencies and moved class structures

## 1.0.0.1 - 2018-11-01
### Fixed
- Migration issue when multiple providers exist.

## 1.0.0 - 2018-10-30
### Added
- Provider instances.  A provider can have more than one configuration (think dev/uat/prod).
- Provider locking.  A third party plugin can lock a provider from accidentally getting deleted.
- Settings::$encryptStorageData which encrypts the Provider's Client Secret (enabled by default).

## 1.0.0-rc.6 - 2018-08-30
### Fixed
- Table alias was not set on Active Query.  Ref: [Issue 2](https://github.com/flipboxfactory/patron/issues/2)
- Migrations were not included on fresh install.

## 1.0.0-rc.5 - 2018-07-17
### Added
- Providers and tokens can be associate to an 'environment' for better multi-environment support.

## 1.0.0-rc.4 - 2018-06-28
### Added
- `flipbox\patron\events\PersistToken` event is triggered before and after storing a new token. 
- testing framework and docs framework

## 1.0.0-rc.3 - 2018-04-26
### Fixed
- Provider switching was calling the wrong endpoint

## 1.0.0-rc.2 - 2018-04-06
### Added
- `flipbox\patron\services\ManageTokens::find()` will find a token based on another AccessToken

## 1.0.0-rc.1 - 2018-04-04
### Fixed
- Bugs when attempting to get a token from an `AbstractProvider`

## 1.0.0-rc - 2018-03-29
Initial release.
