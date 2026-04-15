<?php

namespace Company\VrZip;


class VrZip {
    protected $zipName;
    private $outputStream;

    private $ofs = 0;
    private $cdr_ofs = 0;

    private $cdrFiles = [];

    private $fileAppend;

    private $largeFileSize = 20 * 1024 * 1024;

    public function __construct($zipName) {
        $this->zipName = $zipName;
        $this->outputStream = fopen('php://output', 'wb');
//        $this->outputStream = fopen($zipName, 'wb');
    }

    public function addFileFromPath($name, $path) {
        if (!is_file($path))
            throw new Exception("Path: $path is not file");

        if (!$this->isLargeFile($path)) {
            $file = new File($this, $name);
            $content = file_get_contents($path);
            $file->compressData($content);
        } else {
            $this->startFileAppend($name);
            $fileHandler = fopen($path, 'r');
            $chunk_size = 1024 * 1024 * 10;
            while (!feof($fileHandler)) {
                $chunk = fread($fileHandler, $chunk_size);
                $this->append($chunk);
            }
            $this->finishFileAppend();
        }

    }

    public function addFileFromString($name, $string) {
        $file = new File($this, $name);
        $file->compressData($string);
    }

    public function startFileAppend($name) {
        $this->fileAppend = new File($this, $name);
        $this->fileAppend->addFileHeader();
    }

    public function append($data) {
        if (!$this->fileAppend)
            throw new Exception("must start file append before append data");

        $this->fileAppend->compressChunk($data);
    }

    public function finishFileAppend() {
        $this->fileAppend->compressChunk("", true);
        $this->fileAppend->addFileFooter();
    }

    private function isLargeFile($path) {
        return $this->largeFileSize < filesize($path);
    }

    public function send($str) {
        fwrite($this->outputStream, $str);

        $status = ob_get_status();
        if (isset($status['flags']) && ($status['flags'] & PHP_OUTPUT_HANDLER_FLUSHABLE)) {
            ob_flush();
        }

        // Flush system buffers after flushing userspace output buffer
        flush();
    }

    public function addToCdr(File $file): void
    {
        $file->setOfs($this->ofs);
        $this->ofs += $file->getTotalLength();
        $this->cdrFiles[] = $file->getCdrFile();
    }

    public function finish() {
        foreach ($this->cdrFiles as $cdrFile) {
            $this->send($cdrFile);
            $this->cdr_ofs += strlen($cdrFile);
        }

        $this->addCdr64Eof();
        $this->addCdr64Locator();

        $this->addCdrEof();
    }

    private function addCdr64Eof()
    {
        $num_files = count($this->cdrFiles);
        $cdr_length = $this->cdr_ofs;
        $cdr_offset = $this->ofs;

        $ret = pack("V", 0x06064b50); // ZIP64 end of central file header signature
        $ret.= pack("P", 44); // Length of data below this header (length of block - 12) = 44
        $ret.= pack("v", 0x603); // Made by version
        $ret.= pack("v", "\x2d\x00"); // Extract by version
        $ret.= pack("V", 0x00); // disk number
        $ret.= pack("V", 0x00); // no of disks
        $ret.= pack("P", $num_files); // no of entries on disk
        $ret.= pack("P", $num_files); // no of entries in cdr
        $ret.= pack("P", $cdr_length); // CDR size
        $ret.= pack("P", $cdr_offset); // CDR offset
        
        $this->send($ret);
    }

    private function addCdr64Locator()
    {
        $this->ofs += $this->cdr_ofs;
        $cdr_offset = $this->ofs;

        $ret = pack("V", 0x07064b50); // ZIP64 end of central file header signature
        $ret.= pack("V", 0x00); // Disc number containing CDR64EOF
        $ret.= pack("P", $cdr_offset); // CDR offset
        $ret.= pack("V", 1); // Total number of disks

        $this->send($ret);
    }

    private function addCdrEof()
    {
        $ret = pack('V', 0x06054b50); // end of central file header signature
        $ret.= pack("v", 0xffff); // disk number
        $ret.= pack("v", 0xffff); // no of disks
        $ret.= pack("v", 0xffff); // no of entries on disk
        $ret.= pack("v", 0xffff); // no of entries in cdr
        $ret.= pack("V", 0xffffffff); // CDR size
        $ret.= pack("V", 0xffffffff); // CDR offset
        $ret.= pack("v", 0); // Zip Comment size
//        echo bin2hex($ret);die();
//        var_dump($ret);
        $this->send($ret);
    }
}