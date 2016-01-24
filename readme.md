# PHP backend for resumable.js


## Installation

To install, use composer:

``` composer require dilab/resumable.php ```


## How to use
**upload.php**

```
<?php
include 'vendor/autoload.php';

use Dilab\Network\SimpleRequest;
use Dilab\Network\SimpleResponse;
use Dilab\Resumable;

$request = new SimpleRequest();
$response = new SimpleResponse();

$resumable = new Resumable($request, $response);
$resumable->tempFolder = 'tmps';
$resumable->uploadFolder = 'uploads';
$resumable->process();

```

## Options ##
### Setting custom filename ###

```
// custom filename (extension from original file will be kept)
// @todo Add resumable->getExtension() method
$resumable->setFilename('myfile');

// automatically slugified filename
// @todo Not yet working, better use as 3d party library https://github.com/cocur/slugify
$resumable->setFilename(RESUMABLE::SLUGIFY);
```

## Testing
```
$ ./vendor/bin/phpunit
```
