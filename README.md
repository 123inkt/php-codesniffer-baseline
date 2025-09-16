[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-8892BA)](https://php.net/)
![Run tests](https://github.com/123inkt/php-codesniffer-baseline/workflows/Run%20checks/badge.svg)

# PHP_Codesniffer baseline

To be able to add PHP_Codesniffer or adding new rules to an existing project, it is not always possible to solve
all the new issues that appear. As PHPCodesniffer doesn't have a baseline mechanism and while 
[Issue:137](https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/137) has not reached a final solution yet, this package can be used to
baseline your projects current issues.

## Getting Started

```bash
composer require --dev digitalrevolution/php-codesniffer-baseline
```

## Create baseline
Create the baseline by using phpcs regularly and writing the report with the Baseline report class. You must write the baseline
to the root of the project and name it `phpcs.baseline.xml`.
```bash
php vendor/bin/phpcs src tests --report=\\DR\\CodeSnifferBaseline\\Reports\\Baseline --report-file=phpcs.baseline.xml --basepath=.
```

## Usage
Use phpcs like you normally would. With `phpcs.baseline.xml` in the root of your project, the baseline extension will automatically read the config 
file and skip errors that are contained within the baseline.

## Under the hood

As PHP_Codesniffer doesn't have a nice and clean way to add an extension, this package will inject a single line of code
into the `/vendor/squizlabs/php_codesniffer/src/Files/File.php` upon `composer install` or `composer update`. While this
is a fragile solution, this is only until [Issue:137](https://github.com/PHPCSStandards/PHP_CodeSniffer/issues/137) has reached an acceptable
solution and implementation.

## About us

At 123inkt (Part of Digital Revolution B.V.), every day more than 50 development professionals are working on improving our internal ERP 
and our several shops. Do you want to join us? [We are looking for developers](https://www.werkenbij123inkt.nl/zoek-op-afdeling/it).
