Changelog
=========
## 1.0.0-rc.4 - 2018-06-28
### Added
- `flipbox\patron\events\PersistTokenEvent` event is triggered before and after storing a new token. 
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
