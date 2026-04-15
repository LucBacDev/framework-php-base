<?php


namespace Company\VrZip;


class File
{
    private $fileName;
    private $vrZip;

    private $originalLength;
    private $compressedLength;

    private $totalLength = 0;

    private $crc = 0;

    private $headerLength;

    private $datetime;

    private $ofs;

    private $deflateContext;
    private $hash;

    public $deflateLevel = 6;

    public function __construct($vrZip, $fileName)
    {
        $this->fileName = $fileName;
        $this->vrZip = $vrZip;

        $dtime    = dechex($this->unix2DosTime(0));
        $hexdtime = '\x'. $dtime[6] . $dtime[7]
            .'\x'. $dtime[4] . $dtime[5]
            .'\x'. $dtime[2] . $dtime[3]
            .'\x'. $dtime[0] . $dtime[1];
        eval('$hexdtime = "'. $hexdtime .'";');
        $this->datetime = $hexdtime;
    }

    /**
     * @return int
     */
    public function getTotalLength(): int
    {
        return $this->totalLength;
    }

    /** set local header offset
     * @param mixed $ofs
     */
    public function setOfs($ofs): void
    {
        $this->ofs = $ofs;
    }

    public function compressData($data) {
        $this->originalLength = strlen($data);
        $this->crc = crc32($data);

        $data = gzdeflate($data, $this->deflateLevel);

        $this->compressedLength = strlen($data);

        $this->addFileHeader();
        $this->vrZip->send($data);
        $this->addFileFooter();
    }

    public function compressChunk($data, $isLastChunk=false) {

        // init context

        if (!$this->deflateContext) {
            $this->deflateContext = deflate_init(ZLIB_ENCODING_RAW, ['level' => $this->deflateLevel]);
            if ($this->deflateContext === false)
                throw new Exception("could not init deflate context");
        }

        if (!$this->hash) {
            $this->hash = hash_init("crc32b");
            if ($this->hash === false) {
                throw new RuntimeException('could not initialize hashing context!');
            }
        }

        $this->originalLength += strlen($data);

        // update crc
        hash_update($this->hash, $data);

        // compress
        $data = deflate_add(
            $this->deflateContext,
            $data,
            $isLastChunk
                ? ZLIB_FINISH
                : ZLIB_NO_FLUSH
        );

        // update compress length
        $this->compressedLength += strlen($data);

        $this->vrZip->send($data);

        // convert to crc
        if ($isLastChunk)
            $this->crc = hexdec(hash_final($this->hash));
    }

    function unix2DosTime($unixtime = 0) {
        $timearray = ($unixtime == 0) ? getdate() : getdate($unixtime);

        if ($timearray['year'] < 1980) {
            $timearray['year']    = 1980;
            $timearray['mon']     = 1;
            $timearray['mday']    = 1;
            $timearray['hours']   = 0;
            $timearray['minutes'] = 0;
            $timearray['seconds'] = 0;
        } // end if

        return (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) |
            ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
    } // end of the 'unix2DosTime()' method

    private function buildZip64ExtraBlock() {
        return
            pack("v", 0x0001). // 64 bit extension
            pack("v", 16). // Length of data block
            pack("P", $this->originalLength ?? 0). // Length of original data
            pack("P", $this->compressedLength ?? 0); // Length of compressed data
//            pack("P", $this->ofs ?? 0).
//            pack("V", 0); // Offset of local header record;
    }

    public function addFileHeader() {
        $name     = str_replace('\\', '/', $this->fileName);

        $fr   = "\x50\x4b\x03\x04";
        $fr   .= "\x2d\x00";            // ver needed to extract (Zip64)
        $fr   .= "\x08\x00";            // gen purpose bit flag
        $fr   .= "\x08\x00";            // compression method
        $fr   .= $this->datetime;             // last mod time and date

        $zip64ExtendedField = $this->buildZip64ExtraBlock();

//        var_dump($zip64ExtendedField);die();
        $fr      .= pack('V', 0);             // crc32
        $fr      .= pack('V', 0xffffffff);           // compressed filesize
        $fr      .= pack('V', 0xffffffff);         // uncompressed filesize
        $fr      .= pack('v', strlen($name));    // length of filename
        $fr      .= pack('v', strlen($zip64ExtendedField));                // extra field length
        $fr      .= $name;
        $fr      .= $zip64ExtendedField;

        $this->vrZip->send($fr);

        $this->headerLength = strlen($fr);
    }

    public function addFileFooter() {

        $footer =
            pack("V", 0x08074b50). // data descriptor
            pack("V", $this->crc). //crc32
            pack("P", $this->compressedLength). // Length of compressed data
            pack("P", $this->originalLength); // Length of original data

//        var_dump(bin2hex($footer));die();
        $this->vrZip->send($footer);

        $this->totalLength = $this->headerLength + $this->compressedLength + strlen($footer);
//        $this->totalLength = $this->headerLength + $this->compressedLength;
        $this->vrZip->addToCdr($this);
    }

    public function getCdrFile() {
        $zip64ExtendedField = $this->buildZip64ExtraBlock();

        $name = str_replace('\\', '/', $this->fileName);

        $header = pack("V", 0x02014b50); // CDR_FILE_SIGNATURE
        $header.= pack("v", 0x603); // ZIP_VERSION_MADE_BY
        $header.= "\x2d\x00"; // ver needed to extract (Zip64)
        $header.= "\x08\x00"; // gen purpose bit flag
        $header.= "\x08\x00"; // compression method
        $header.= $this->datetime; // last mod time and date
        $header.= pack("V", $this->crc); // CRC32
        $header.= pack("V", 0xffffffff); // Compressed Data Length
        $header.= pack("V", 0xffffffff); // Original Data Length
        $header.= pack("v", strlen($name)); // Length of filename
        $header.= pack("v", strlen($zip64ExtendedField)); // Extra data len
        $header.= pack("v", 0); // Length of comment
        $header.= pack("v", 0); // Disk number
        $header.= pack("v", 0); // Internal File Attributes
        $header.= pack("V", 32); // External File Attributes
        $header.= pack("V", $this->ofs); // Relative offset of local header

        return $header . $name . $zip64ExtendedField;
    }
}