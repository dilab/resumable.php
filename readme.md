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

## More ##
### Setting custom filename(s) ###

```
// custom filename (extension from original file will be kept)
$resumable->setFilename('someCoolFilename');

// you can also get file information after the upload is complete
if (true === $resumable->isUploadComplete()) { // true when the final file has been uploaded and chunks reunited.
    $extension = $resumable->getExtension();
    $filename = $resumable->getFilename();
}
```

## Testing
```
$ ./vendor/bin/phpunit
```
