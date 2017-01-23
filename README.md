# Change Log
This is the Maleficarum API component implementation. 

## [5.0.0] - 2017-01-23
### Changed
- Add return and argument types declaration

## [4.0.0] - 2017-01-10
### Changed
##### Move components listed below to the external repositories:
- Worker
- RabbitMQ

### Fixed
- Set default route filename for root path

## [3.2.2] - 2016-12-22
### Added
- Add security checks skipping for specified routes

## [3.2.1] - 2016-12-20
### Changed
- Move security check after database initialization

## [3.2.0] - 2016-10-24
### Added
- Add sending not found response via controller method

## [3.1.1] - 2016-10-21
### Added
- Added trait for logger

## [3.1.0] - 2016-10-21
### Added
- Added monolog logger

## [3.0.0] - 2016-10-18
### Changed
- Move handler and exception components to the external repository

## [2.0.0] - 2016-10-16
### Changed
##### Move components listed below to the external repositories:
- Config
- Profiler
- Environment
- Request
- Response

## [1.2.1] - 2016-10-07
### Fixed
- Set default request parser if Content-Type is not defined

## [1.2.0] - 2016-10-06
### Added
- Added application/x-www-form-urlencoded request handling
- Added exception for unsupported media type

## [1.1.0] - 2016-10-04
### Added
- Added new method for fetching all GET or POST parameters

## [1.0.3] - 2016-10-03
### Changed
- Changed repository URL

## [1.0.2] - 2016-09-26
### Added
- Added controller fallback class

## [1.0.1] - 2016-09-23
### Fixed
- Fixed API error handling

## [1.0.0] - 2016-09-23
### Added
- This was an initial release based on the code written by pharaun13 and added to the repo by me
