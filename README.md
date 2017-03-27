# Change Log
This is the Maleficarum API component implementation. 

## [8.3.0] - 2017-03-27
### Added
- New helper method for the generic controller - one that responds with a 401 response.
- Support for an optional "initialize" call on generic controller classes.

## [8.2.0] - 2017-03-24
### Added
- Added default support for Maleficarum\Rabbitmq in controller builder.

## [8.1.0] - 2017-03-24
### Added
- Added default support for Maleficarum\Redis in controller builder.

## [8.0.0] - 2017-03-23
### Changed
- Moved default initializers for external components into those components - they are no longer defined within this project.
- Removed several hard dependencies like profiler or database from API project - API projects can no properly function without those components if they are not necessary.

## [7.0.0] - 2017-03-22
### Changed
- Removed any database specific functionality from the API project.
- Added a reliance on a specific maleficarum-database component for database functionalities.

## [6.3.0] - 2017-03-20
### Changed
- Removed data structure definitions from the API project and moved them to a separate component repo.
- Changed API to rely on the new data component.
- Moved database collection/model definitions to the Api\Database namespace.

## [6.2.0] - 2017-03-08
### Changed
- Upgraded API to use maleficarum-http-response 2.0
- Changed default response builder to match new http response API. 

## [6.1.0] - 2017-03-08
### Added
- Moved internal initializers to a new namespace (transparent and backwards compatible)
- Added internal builder definitions and a mechanism to skip their loading in specific initializers.

## [6.0.0] - 2017-03-07
### Changed
- Decoupled bootstrap initialization functionalities from the main bootstrap object. As of know when using the boostrap object one can and must provide a list of valid PHP callable types that will be run in order when the initialization process is executed.
- Default bootstrap initializers were separed from the main class as static methods to be used as needed on a case-by-case basis.

## [5.1.0] - 2017-03-06
### Changed
- Bump handler version

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
