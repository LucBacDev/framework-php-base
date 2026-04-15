<?php

use Company\VrTar\VrTar;

$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

error_reporting(-1);
ini_set('display_errors', 'On');

$tar = new \Company\VrTar\VrTar();
$tar->createStream('myfile.tar');
$tar->setCompression(0, \splitbrain\PHPArchive\Archive::COMPRESS_NONE);

// compress by file
$content = file_get_contents("test.dcm");
// add dir/test1.txt
// chunk size chia het 512
var_dump("add test1.dcm");
$file1 = new \splitbrain\PHPArchive\FileInfo();
$file1->setPath("test1.dcm");
$file1->setSize(strlen($content));
$tar->addDataStream($file1, substr($content, 0, 512*2), "NEW_FILE");
$tar->addDataStream($file1, substr($content, 512*2, strlen($content) - 1), "APPEND");
var_dump("add test2.dcm");
$tar->addDataStream("test2.dcm", $content);
file_put_contents("myfile.tar", $tar->getArchiveStream(true));


// decompress
$tar = new VrTar();
$content = file_get_contents("myfile.tar");
$fileItems = $tar->extractStream(substr($content, 0, 512*100));
foreach ($fileItems as $fileItem){
    file_put_contents("output/" . $fileItem["FILEINFO"]->getPath(), $fileItem["CONTENTS"], FILE_APPEND);
}

$fileItems = $tar->extractStream(substr($content, 512*100, strlen($content) - 1));
foreach ($fileItems as $fileItem){
    file_put_contents("output/" . $fileItem["FILEINFO"]->getPath(), $fileItem["CONTENTS"], FILE_APPEND);
}




