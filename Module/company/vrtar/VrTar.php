<?php
namespace Company\VrTar;

use splitbrain\PHPArchive\ArchiveIOException;
use splitbrain\PHPArchive\FileInfo;

class VrTar extends \splitbrain\PHPArchive\Tar
{
    /**
     * Extract an existing TAR archive
     * @param string $buffer chunk content file
     */
    public function extractStream2($buffer){
        $this->memory .=  $buffer;

        $len = strlen($this->memory);
        $fileItems = [];
        $fileItem = [];

        for ($s = 0; $s < $len; $s += 512) {
            if ($s + 512 > $len){
                if(!empty($fileItem)){
                    $fileItems[]=$fileItem;
                }
                break;
            }

            //parse header
            if($this->fileinfo == null){
                $fileItem = [];
                $dat = substr($this->memory, $s, 512);

                $header = $this->parseHeader($dat);

                if ($header == false) {
                    continue;
                }
                $this->fileinfo = $this->header2fileinfo($header);

                $fileItem["FILEINFO"] = $this->fileinfo;
                $fileItem["CONTENTS"] = '';
                continue;
            }

            if(empty($fileItem)){
                $fileItem["FILEINFO"] = $this->fileinfo;
                $fileItem["CONTENTS"] = '';
            }

            $dat = substr($this->memory, $s, 512);

            if($this->fileinfo->getSize() - $this->contentLength < strlen($dat)){
                $dat = substr($dat, 0, $this->fileinfo->getSize() - $this->contentLength);
            }

            $fileItem["CONTENTS"] .=  $dat;
            $this->contentLength += strlen($dat);

            //complete file
            if($this->fileinfo->getSize() - $this->contentLength == 0){
                $fileItems[]=$fileItem;
                $this->fileinfo = null;
                $this->contentLength = 0;
            }
        }

        if (($len % 512) != 0) {
            $this->memory = substr($this->memory, -($len % 512));
        }else{
            $this->memory = '';
        }

        return $fileItems;
    }

    /**
     * Extract an existing TAR archive
     * @param string $buffer chunk content file
     */
    public function extractStream($buffer){
        $this->memory .=  $buffer;

        $len = strlen($this->memory);
        $fileItems = [];
        $fileItem = [];
        $offset = 0;
        while ($offset <= $len){
            if($offset == $len){
                if(!empty($fileItem)){
                    $fileItems[]=$fileItem;
                }
                break;
            }

            if($offset + 512 > $len ){
                $this->memory = substr($this->memory, $offset, $len - $offset);
                break;
            }

            //parse header
            if($this->fileinfo == null){
                $fileItem = [];
                $dat = substr($this->memory, $offset, 512);
                $offset += 512;

                $header = $this->parseHeader($dat);

                if ($header == false) {
                    continue;
                }
                $this->fileinfo = $this->header2fileinfo($header);

                $fileItem["FILEINFO"] = $this->fileinfo;
                $fileItem["CONTENTS"] = '';
                continue;
            }

            if(empty($fileItem)){
                $fileItem["FILEINFO"] = $this->fileinfo;
                $fileItem["CONTENTS"] = '';
            }

            if($this->fileinfo->getSize() - $this->contentLength >= $len - $offset){
                $dat = substr($this->memory, $offset);
            }else{
                $dat = substr($this->memory, $offset, $this->fileinfo->getSize() - $this->contentLength);
            }

            $fileItem["CONTENTS"] .=  $dat;
            $offset += strlen($dat);
            $this->contentLength += strlen($dat);
            if($this->fileinfo->getSize() - $this->contentLength == 0){
                if (($this->fileinfo->getSize() % 512) != 0) {
                    $offset += 512 -($this->fileinfo->getSize() % 512);
                }
                $fileItems[]=$fileItem;
                $fileItem = [];
                $this->fileinfo = null;
                $this->contentLength = 0;
            }
        }

        if($offset == $len){
            $this->memory = '';
        }

        return $fileItems;
    }

    /**
     * Add a file to the current TAR archive using the given $data as content
     *
     * @param string|FileInfo $fileinfo either the name to us in archive (string) or a FileInfo oject with all meta data
     * @param string          $data     binary content of the file to add (length divisible by 512 if compressed by parts)
     * @param string          $option   option write data APPEND|NEW_FILE|FULL_FILE
     * @throws ArchiveIOException
     */
    public function addDataStream($fileinfo, $data, $option = "FULL_FILE")
    {
        if (is_string($fileinfo)) {
            $fileinfo = new FileInfo($fileinfo);
        }

        if ($this->closed) {
            throw new ArchiveIOException('Archive has been closed, files can no longer be added');
        }
        $len = strlen($data);

        if($fileinfo->getSize() == 0){
            $fileinfo->setSize($len);
        }

        if ($option == "NEW_FILE" || $option == "FULL_FILE")
            $this->writeFileHeaderStream($fileinfo);

        for ($s = 0; $s < $len; $s += 512) {
            /*            if(strlen(substr($data, $s, 512)) != 512){
                            var_dump(strlen(substr($data, $s, 512)));
                            var_dump($len);die;
                        }*/

            $this->writebytesStream(pack("a512", substr($data, $s, 512)));
        }

        if (is_callable($this->callback)) {
            call_user_func($this->callback, $fileinfo);
        }
    }
}