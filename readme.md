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
// optional instanceId to seperate uploads from diffrent users like if two users want to upload untitled.jpg there would be no conflict anymore
$instanceId = session_id();

$resumable = new Resumable($request, $response, $instanceId);
$resumable->tempFolder = 'tmps';
$resumable->uploadFolder = 'uploads';
$status = $resumable->process();

return match ($status){
            200 => ['message' => 'OK'], // Uploading of chunk is complete.
            201 => [
                'message' => 'File uploaded',
                'file' => $_REQUEST['resumableFilename']
            ],// Uploading of whole file is complete.
            204 => ['message' => 'Chunk not found'],
            default => ['message' => 'An error occurred'] //status => 404
        };

```

## More ##
### Setting custom filename(s) ###

```
// custom filename (extension from original file will be magically removed and re-appended)
$originalName = $resumable->getOriginalFilename(Resumable::WITHOUT_EXTENSION); // will gove you "original Name" instead of "original Name.png"

// do some slugification or whatever you need...
$slugifiedname = my_slugify($originalName); // this is up to you, it as ported out of the library.
$resumable->setFilename($slugifiedname);

// process upload as normal
$resumable->process();

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
