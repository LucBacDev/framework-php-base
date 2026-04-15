<?php

namespace Company\File\Model;

use Company\Entity\Entity;
use Company\Exception\NotFoundException;

class FileEntity extends Entity {
    /**
     * @param string $filePath Write to file or null is write to browser
     * @throws NotFoundException
     */
    function outputFile ($filePath = null) {
        if(!$this->id)
            throw new NotFoundException("File not found");
        if(!$this->b64)
            throw new \Exception("file b64 empty");

        ob_end_clean();
        header("Content-type: " . $this->mime);
        header('Content-Disposition: attachment; filename='.$this->name);
        echo base64_decode($this->b64);
        die;
    }
}