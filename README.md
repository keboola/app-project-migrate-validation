# Project Migration Validation

[![Build Status](https://travis-ci.com/keboola/app-project-migrate-validation.svg?branch=master)](https://travis-ci.com/keboola/app-project-migrate-validation)

The application checks if KBC project can be migrated into another KBC region.
Application validates the project in which it is executed. If everything is ok job ends with success,
otherwise validation issues are printed into log and job ends with error.

Performed validations:

- Legacy components used
- MySQL transformations
- Redshift transformations
- GoodData writers with custom authorization tokens

## Development
 
Clone this repository and init the workspace with following command:

```
git clone https://github.com/keboola/my-component
cd my-component
docker-compose build
docker-compose run --rm dev composer install --no-scripts
```

Run the test suite using this command:

```
docker-compose run --rm dev composer tests
```
 
# Integration

For information about deployment and integration with KBC, please refer to the [deployment section of developers documentation](https://developers.keboola.com/extend/component/deployment/) 
