<?php
namespace Dilab;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Dilab\Network\Request;
use Dilab\Network\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Resumable
{
    public $debug = false;

    public $tempFolder = 'tmp';

    public $uploadFolder = 'test/files/uploads';

    // for testing
    public $deleteTmpFolder = true;

    protected $request;

    protected $response;

    protected $params;

    protected $chunkFile;

    protected $log;

    protected $filename;

    protected $originalFilename;

    protected $filepath;

    const SLUGIFY = true;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->log = new Logger('debug');
        $this->log->pushHandler(new StreamHandler('debug.log', Logger::DEBUG));
    }

    public function process()
    {
        if (!empty($this->resumableParams())) {
            if (!empty($this->request->file())) {
                $this->handleChunk();
            } else {
                $this->handleTestChunk();
            }
        }
    }

    /**
     * Set final filename.
     *
     * @param string Final filename
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get final filename.
     *
     * @return string Final filename
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get final filename.
     *
     * @return string Final filename
     */
    public function getOriginalFilename()
    {
        return $this->originalFilename;
    }

    /**
     * Get final filapath.
     *
     * @return string Final filename
     */
    public function getFilepath()
    {
        return $this->filepath;
    }

    /**
     * Makes sure the orginal extension never gets overriden by user defined filename.
     *
     * @param string User defined filename
     * @param string Original filename
     * @return string Filename that always has an extension from the original file
     */
    private function createSafeFilename($filename, $originalFilename)
    {
        $filename = $this->removeExtension($filename);
        $extension = $this->findExtension($originalFilename);

        return sprintf('%s.%s', $filename, $extension);
    }

    public function handleTestChunk()
    {
        $identifier = $this->resumableParam('identifier');
        $filename = $this->resumableParam('filename');
        $chunkNumber = $this->resumableParam('chunkNumber');

        if (!$this->isChunkUploaded($identifier, $filename, $chunkNumber)) {
            return $this->response->header(404);
        } else {
            return $this->response->header(200);
        }
    }

    public function handleChunk()
    {
        $file = $this->request->file();
        $identifier = $this->resumableParam('identifier');
        $filename = $this->resumableParam('filename');
        $chunkNumber = $this->resumableParam('chunkNumber');
        $chunkSize = $this->resumableParam('chunkSize');
        $totalSize = $this->resumableParam('totalSize');

        if (!$this->isChunkUploaded($identifier, $filename, $chunkNumber)) {
            $chunkFile = $this->tmpChunkDir($identifier) . DIRECTORY_SEPARATOR . $this->tmpChunkFilename($filename, $chunkNumber);
            $this->moveUploadedFile($file['tmp_name'], $chunkFile);
        }

        if ($this->isFileUploadComplete($filename, $identifier, $chunkSize, $totalSize)) {
            $this->createFileAndDeleteTmp($identifier, $filename);
        }

        return $this->response->header(200);
    }

    /**
     * Create the final file from chunks
     *
     */
    private function createFileAndDeleteTmp($identifier, $filename)
    {
        $tmpFolder = new Folder($this->tmpChunkDir($identifier));
        $chunkFiles = $tmpFolder->read(true, true, true)[1];

        // save original filename
        $this->originalFilename = $filename;
        
        // if the user has set a filename (or decided to slugify it), change the final filename
        if (null !== $this->filename) {
            $this->filename = $this->createSafeFilename($this->filename, $filename);
        }

        // replace filename reference by the final file
        $this->filepath = $this->uploadFolder . DIRECTORY_SEPARATOR . $this->filename;

        if ($this->createFileFromChunks($chunkFiles, $this->filepath) && $this->deleteTmpFolder) {
            $tmpFolder->delete();
            $this->uploadComplete = true;
        }
    }

    private function resumableParam($shortName)
    {
        $resumableParams = $this->resumableParams();
        if (!isset($resumableParams['resumable' . ucfirst($shortName)])) {
            return null;
        }
        return $resumableParams['resumable' . ucfirst($shortName)];
    }

    public function resumableParams()
    {
        if ($this->request->is('get')) {
            return $this->request->data('get');
        }
        if ($this->request->is('post')) {
            return $this->request->data('post');
        }
    }

    public function isFileUploadComplete($filename, $identifier, $chunkSize, $totalSize)
    {
        if ($chunkSize <= 0) {
            return false;
        }
        $numOfChunks = intval($totalSize / $chunkSize) + ($totalSize % $chunkSize == 0 ? 0 : 1);
        for ($i = 1; $i < $numOfChunks; $i++) {
            if (!$this->isChunkUploaded($identifier, $filename, $i)) {
                return false;
            }
        }
        return true;
    }

    public function isChunkUploaded($identifier, $filename, $chunkNumber)
    {
        $file = new File($this->tmpChunkDir($identifier) . DIRECTORY_SEPARATOR . $this->tmpChunkFilename($filename, $chunkNumber));
        return $file->exists();
    }

    public function tmpChunkDir($identifier)
    {
        $tmpChunkDir = $this->tempFolder . DIRECTORY_SEPARATOR . $identifier;
        if (!file_exists($tmpChunkDir)) {
            mkdir($tmpChunkDir);
        }
        return $tmpChunkDir;
    }

    public function tmpChunkFilename($filename, $chunkNumber)
    {
        return $filename . '.part' . $chunkNumber;
    }

    public function createFileFromChunks($chunkFiles, $destFile)
    {
        $this->log('Beginning of create files from chunks');

        natsort($chunkFiles);

        $destFile = new File($destFile, true);
        foreach ($chunkFiles as $chunkFile) {
            $file = new File($chunkFile);
            $destFile->append($file->read());

            $this->log('Append ', ['chunk file' => $chunkFile]);
        }

        $this->log('End of create files from chunks');
        return $destFile->exists();
    }

    public function moveUploadedFile($file, $destFile)
    {
        $file = new File($file);
        if ($file->exists()) {
            return $file->copy($destFile);
        }
        return false;
    }

    public function setRequest($request)
    {
        $this->request = $request;
    }

    public function setResponse($response)
    {
        $this->response = $response;
    }

    private function log($msg, $ctx = array())
    {
        if ($this->debug) {
            $this->log->addDebug($msg, $ctx);
        }
    }

    private function findExtension($filename)
    {
        $parts = explode('.', basename($filename));

        return end($parts);
    }

    private function removeExtension($filename)
    {
        $parts = explode('.', basename($filename));
        $ext = end($parts); // get extension

        // remove extension from filename if any
        return str_replace(sprintf('.%s', $ext), '', $filename);
    }
}
