<?php
namespace Dilab;

use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Dilab\Network\Request;
use Dilab\Network\Response;

class Resumable
{

    public $tempFolder = 'tmp';

    public $uploadFolder = 'test/files/uploads';

    // for testing
    public $deleteTmpFolder = true;

    protected $request;

    protected $response;

    protected $params;

    protected $chunkFile;

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
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

    public function handleTestChunk()
    {
        $identifier = $this->_resumableParam('identifier');
        $filename = $this->_resumableParam('filename');
        $chunkNumber = $this->_resumableParam('chunkNumber');

        if (!$this->isChunkUploaded($identifier,$filename,$chunkNumber)) {
            return $this->response->header(404);
        } else {
            return $this->response->header(200);
        }
    }

    public function handleChunk()
    {
        $file = $this->request->file();
        $identifier = $this->_resumableParam('identifier');
        $filename = $this->_resumableParam('filename');
        $chunkNumber = $this->_resumableParam('chunkNumber');
        $chunkSize = $this->_resumableParam('chunkSize');
        $totalSize = $this->_resumableParam('totalSize');

        if (!$this->isChunkUploaded($identifier,$filename,$chunkNumber)) {
           $chunkFile = $this->tmpChunkDir($identifier).DIRECTORY_SEPARATOR.$this->tmpChunkFilename($filename, $chunkNumber);
           $this->moveUploadedFile($file['tmp_name'], $chunkFile);
        }

        if ($this->isFileUploadComplete($filename,$identifier,$chunkSize, $totalSize)) {
            $tmpFolder = new Folder($this->tmpChunkDir($identifier));
            $chunkFiles = $tmpFolder->read(true,true,true)[1];
            $this->createFileFromChunks($chunkFiles, $this->uploadFolder.DIRECTORY_SEPARATOR.$filename);
            if ($this->deleteTmpFolder) {
                $tmpFolder->delete();
            }
        }

        return $this->response->header(200);
    }

    private function _resumableParam($shortName) {
        $resumableParams = $this->resumableParams();
        if (!isset($resumableParams['resumable'.ucfirst($shortName)])) {
            return null;
        }
        return $resumableParams['resumable'.ucfirst($shortName)];
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
        return $this->tempFolder . DIRECTORY_SEPARATOR . $identifier;
    }

    public function tmpChunkFilename($filename, $chunkNumber)
    {
        return $filename . '.part' . $chunkNumber;
    }

    public function createFileFromChunks($chunkFiles, $destFile)
    {
        $destFile = new File($destFile, true);
        foreach ($chunkFiles as $chunkFile) {
            $file = new File($chunkFile);
            $destFile->append($file->read());
        }
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

}
