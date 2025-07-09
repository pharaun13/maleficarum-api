# Change Log
This is the Maleficarum API component implementation. 

## [15.0.0] - 2025-07-09
### Changed
- Improve environment matching

## [14.1.1] - 2021-02-04
### Changed
- Allow for uat-* environments

## [14.1.0] - 2020-07-22
### Changed
- Added option to skip routes authentication based on reg ex'es by using skip_regex_routes config parameter 

## [14.0.0] - 2020-04-29
### Changed
- Updated to depend on and work with Phalcon 4.0.X
- Updated to depend on and work with Maleficarum\Response 6.0.X
- Updated to depend on and work with Maleficarum\Request 6.0.X

## [13.2.1] - 2020-11-17
### Changed
- - Add support for multiple UAT environments

## [13.2.0] - 2020-07-22
### Changed
- - Added option to skip routes authentication based on reg ex'es by using skip_regex_routes config parameter

## [13.1.2] - 2019-04-25
### Changed
- Added sandbox environment

## [13.1.1] - 2019-04-23
### Changed
- Added graceful shutdown when parse_url function fails due to a PHP bug (https://bugs.php.net/bug.php?id=75041)

## [13.1.0] - 2019-03-12
### Changed
- Changed "sort" errors format in \Controller\Generic.

## [13.0.1] - 2018-12-03
### Changed
- Allow use of older data component versions

## [13.0.0] - 2018-09-24
### Changed
- Upgraded IoC component to version 3.x and dependant components   
- Removed repositories section from composer file   

## [12.0.0] - 2018-09-11
### Changed
- Upgraded maleficarum\data dependency to 4.X

## [11.0.0] - 2018-08-30
### Fixed
- Bump data component

## [10.0.1] - 2018-07-02
### Fixed
- Fixed queue manager injection

## [10.0.0] - 2018-04-11
### Changed
- Changed errors format in \Controller\Generic.

## [9.3.0] - 2017-10-06
### Added
- Added routes version handling

## [9.2.1] - 2017-09-19
### Fixed
- Incorrect return type in \Controller\Generic

## [9.2.0] - 2017-09-19
### Added
- Added sorting/pagination validation to generic controller implementation.

## [9.1.0] - 2017-09-06
### Changed
- Upgraded maleficarum request dependency.

## [9.0.0] - 2017-08-03
### Changed
- Bump phalcon version
- Bump php version
- Bump maleficarum components

## [8.6.0] - 2017-04-07
### Changed
- Changed how security check skips are defined. As of now you can skip a route on all methods, just one methods or you can skip checks on all routes regardless of anything.

## [8.5.0] - 2017-04-07
### Changed
- Security check fails will now return a proper 403 Forbidden response.

## [8.4.1] - 2017-03-28
### Fixed
- Call setAuth method instead of setQueue in basic api builder

## [8.4.0] - 2017-03-27
### Added
- New generic controller hook added to default builder - setAuth.

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
