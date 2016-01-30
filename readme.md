# Harmonizer [![Build Status](https://travis-ci.org/schnittstabil/harmonizer.svg?branch=master)](https://travis-ci.org/schnittstabil/harmonizer) [![Coverage Status](https://coveralls.io/repos/schnittstabil/harmonizer/badge.svg?branch=master&service=github)](https://coveralls.io/github/schnittstabil/harmonizer?branch=master) [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/schnittstabil/harmonizer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/schnittstabil/harmonizer/?branch=master) [![Code Climate](https://codeclimate.com/github/schnittstabil/harmonizer/badges/gpa.svg)](https://codeclimate.com/github/schnittstabil/harmonizer)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/3d29fc53-defc-464f-b0ea-26ae3aa513cf/big.png)](https://insight.sensiolabs.com/projects/3d29fc53-defc-464f-b0ea-26ae3aa513cf)

> Harmonize PHP module, CGI and FCGI/FastCGI environments by infering missing `$_SERVER` variables like `REMOTE_USER` and `HTTP_AUTHORIZATION`.


## Install

```
$ composer require schnittstabil/harmonizer
```


## Usage

```php
require __DIR__.'/vendor/autoload.php';

Schnittstabil\Harmonizer::harmonize($_SERVER);

echo 'Hello '.$_SERVER['REMOTE_USER'];
```


## API

### Schnittstabil\Harmonizer::harmonize(&$server)

Infering missing variables in `$server`:

```php
Schnittstabil\Harmonizer::harmonize($_SERVER);

// $_SERVER['.*'] from $_SERVER['REDIRECT_.*'], eg.
$_SERVER['HTTP_AUTHORIZATION']  // from $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
$_SERVER['GEOIP_LATITUDE']      // from $_SERVER['REDIRECT_REDIRECT_GEOIP_LATITUDE'];

// unify user variables, if needed
$_SERVER['REMOTE_USER']   // from $_SERVER['PHP_AUTH_USER']
$_SERVER['PHP_AUTH_USER'] // from $_SERVER['REMOTE_USER']

// from $_SERVER['HTTP_AUTHORIZATION']:
$_SERVER['AUTH_TYPE']       // 'Basic' or 'Digest'
$_SERVER['REMOTE_USER']     // if needed
$_SERVER['PHP_AUTH_USER']   // if needed
$_SERVER['PHP_AUTH_PW']     // if $_SERVER['AUTH_TYPE'] === 'Basic'
$_SERVER['PHP_AUTH_DIGEST'] // if $_SERVER['AUTH_TYPE'] === 'Digest'
```


## License

MIT © [Michael Mayer](http://schnittstabil.de)
