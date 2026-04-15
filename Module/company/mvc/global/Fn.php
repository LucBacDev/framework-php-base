<?php

use Company\MVC\Bootstrap;

function app() {
    return Bootstrap::getInstance();
}

function url($path = '', $params = array()) {
    $app = Bootstrap::getInstance();
    $url = ($app->rewriteBase == '/' ? '' : $app->rewriteBase) . $path;
    $sep = '?';
    foreach ($params as $k => $v) {
        $url .= "{$sep}{$k}={$v}";
        $sep = '&';
    }

    return $url;
}

function urlAbsolute($path = '', $params = array()) {
    return Bootstrap::getInstance()->fullSiteAddr . url($path, $params);
}

/**
 * 
 * @param array $arr
 * @param string|array $key
 * @param mixed $default
 * @return mixed
 */
function arrData($arr, $key, $default = null) {
    if (is_string($key) || is_int($key))
        return $arr[$key] ?? $default;

    foreach ($key as $keyPart) {
        if (!isset($arr[$keyPart]))
            return $default;
        $arr = $arr[$keyPart];
    }

    return $arr;

}


/**
 * Processes a chunk of data into JSON format.
 * @param array $chunk The chunk of data to process.
 * @param bool $isFirstChunk Indicates if this is the first chunk of data.
 * @param bool $isLastChunk Indicates if this is the last chunk of data.
 * @param bool $hasPrevious A reference to a flag indicating if there was a previous processed record.
 * @return string The formatted JSON string for the given chunk.
 */
function processChunkAsJson($chunk, $isFirstChunk, $isLastChunk, &$hasPrevious) {
    $jsonOutput = "";

    if ($isFirstChunk) {
        $jsonOutput .= "[";
    }

    foreach ($chunk as $record) {
        if ($hasPrevious) {
            $jsonOutput .= ",";
        }

        $jsonOutput .= json_encode($record);
        $hasPrevious = true;
    }

    if ($isLastChunk) {
        $jsonOutput .= "]";
    }
    return $jsonOutput;
}

/**
 * Debug
 * @param array $data
 * @return mixed
 */

function dd($data,$type = null) {
    if (php_sapi_name() != "cli"){
        echo "<pre style='background-color: #f5f5f5; padding: 10px;'>";
        echo "<span style='color: #333;'>";

        if($type == 1)
            $output = var_dump($data, true);
        else
            $output = print_r($data, true);
//     Màu sắc cho dữ liệu kiểu mảng
    $output = preg_replace('/\[(.+)\]/', '[<span style="color: purple;">$1</span>]', $output);

    // Màu sắc cho dữ liệu kiểu boolean
    $output = preg_replace('/(bool)\((true|false)\)/', '<span style="color: blue;">$1</span>($2)', $output);

    // Màu sắc cho dữ liệu kiểu NULL
    $output = preg_replace('/(NULL)/', '<span style="color: red;">$1</span>', $output);

    // Màu sắc cho dữ liệu kiểu số
    $output = preg_replace('/(\d+)/', '<span style="color: green;">$1</span>', $output);

        echo $output;

        echo "</span>";
        echo "</pre>";
        die();
    }
    else{

        if($type == 1)
            $output = var_dump($data, true);
        else
            $output = print_r($data, true);
        echo $output;

        die();
    }

}

/**
 * Chuyển mảng về tham số get
 * @param array $arr
 * @return string
 */
function encodeForm($arr) {
    $ret = '';
    foreach ($arr as $k => $v) {
        $ret .= $ret ? "&{$k}={$v}" : "{$k}={$v}";
    }
    return $ret;
}

/**
 * Lấy config từ thư mục Config/
 * @param string $fileName
 * @return mixed
 */
function getConfig($fileName) {
    $fullPath = BASE_DIR . '/Config/' . $fileName;
    if (strpos($fileName, '.config.php') !== false) {
        require $fullPath;
        if (!isset($exports)) {
            throw new Exception($fullPath . ' phải có biến $exports');
        }
        return $exports;
    }

    throw new Exception($fullPath . ' không hợp lệ (chỉ hỗ trợ .config.php hoặc .xml)');
}

/**
 * Xử lý SQL inject, XSS bằng tay
 * @param type $str
 * @return type
 */
function escapeStr($str) {
    $str = stripslashes($str);
    $str = str_replace("&", '&amp;', $str);
    $str = str_replace('<', '&lt;', $str);
    $str = str_replace('>', '&gt;', $str);
    $str = str_replace('"', '&#34;', $str);
    $str = str_replace("'", '&#39;', $str);

    return $str;
}

function deEscapeStr($str) {
    $str = stripslashes($str);
    $str = str_ireplace('&amp;', '&', $str);
    $str = str_replace('&lt;', '<', $str);
    $str = str_replace('&gt;', '>', $str);
    $str = str_replace('&#34;', '"', $str);
    $str = str_replace('&#39;', "'", $str);

    return $str;
}

/*
 * Chuyen doi tieng viet co dau thanh khong dau
 * 
 */

function tiengVietKhongDau($str) {
// In thường
    $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
    $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
    $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
    $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
    $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
    $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
    $str = preg_replace("/(đ)/", 'd', $str);
// In đậm
    $str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
    $str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
    $str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
    $str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
    $str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
    $str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
    $str = preg_replace("/(Đ)/", 'D', $str);
    return $str; // Trả về chuỗi đã chuyển
}

function unicode_to_nosign($str) {
    $ret_str = Array();

    $unicode = preg_split("/\,/", 'á,à,ả,ã,ạ,ă,ắ,ằ,ẳ,ẵ,ặ,â,ấ,ầ,ẩ,ẫ,ậ,é,è,ẻ,ẽ,ẹ,ê,ế,ề,ể,ễ,ệ,í,ì,ỉ,ĩ,ị,ó,ò,ỏ,õ,ọ,ô,ố,ồ,ổ,ỗ,ộ,ơ,ớ,ờ,ở,ỡ,ợ,ú,ù,ủ,ũ,ụ,ư,ứ,ừ,ử,ữ,ự,ý,ỳ,ỷ,ỹ,ỵ,đ,Á,À,Ả,Ã,Ạ,Ă,Ắ,Ằ,Ẳ,Ẵ,Ặ,Â,Ấ,Ầ,Ẩ,Ẫ,Ậ,É,È,Ẻ,Ẽ,Ẹ,Ê,Ế,Ề,Ể,Ễ,Ệ,Í,Ì,Ỉ,Ĩ,Ị,Ó,Ò,Ỏ,Õ,Ọ,Ô,Ố,Ồ,Ổ,Ỗ,Ộ,Ơ,Ớ,Ờ,Ở,Ỡ,Ợ,Ú,Ù,Ủ,Ũ,Ụ,Ư,Ứ,Ừ,Ử,Ữ,Ự,Ý,Ỳ,Ỷ,Ỹ,Ỵ,Đ');

    $nosign = preg_split("/\,/", 'a,a,a,a,a,a,a,a,a,a,a,a,a,a,a,a,a,e,e,e,e,e,e,e,e,e,e,e,i,i,i,i,i,o,o,o,o,o,o,o,o,o,o,o,o,o,o,o,o,o,u,u,u,u,u,u,u,u,u,u,u,y,y,y,y,y,d,A,A,A,A,A,A,A,A,A,A,A,A,A,A,A,A,A,E,E,E,E,E,E,E,E,E,E,E,I,I,I,I,I,O,O,O,O,O,O,O,O,O,O,O,O,O,O,O,O,O,U,U,U,U,U,U,U,U,U,U,U,Y,Y,Y,Y,Y,D');

    foreach ($unicode as $key => $val)
        $ret_str[$val] = $nosign[$key];

    return strtr($str, $ret_str);
}

/**
 * 
 * @param bool $status
 * @param mixed $data
 * @return array
 */
function result($status = true, $data = null, $code = null) {
    return [
        'status' => $status,
        'data' => $data,
        'code' => $code
    ];
}

/**
 * Không gọi die, exit mà phải stop mới đúng kiểu
 */
function stop() {
    app()->slim->stop();
}

/**
 * Sinh ra ID độc nhất
 * @return string
 */
function uid() {
    $unformated = str_replace("\n", "", gethostname()) . str_replace(".", "", uniqid("", true));
    $formated = "";
    $countPart = 0;
    for ($i = 0; $i < strlen($unformated); $i++) {
        if ($countPart++ == 8) {
            $formated .= ".";
            $countPart = 1;
        }
        $formated .= $unformated[$i];
    }
    return $formated;
}

function composite_to_unicode($str) {
    ///unicode
    $unicode = preg_split("/\,/", 'á,à,ả,ã,ạ,ă,ắ,ằ,ẳ,ẵ,ặ,â,ấ,ầ,ẩ,ẫ,ậ,é,è,ẻ,ẽ,ẹ,ê,ế,ề,ể,ễ,ệ,í,ì,ỉ,ĩ,ị,ó,ò,ỏ,õ,ọ,ô,ố,ồ,ổ,ỗ,ộ,ơ,ớ,ờ,ở,ỡ,ợ,ú,ù,ủ,ũ,ụ,ư,ứ,ừ,ử,ữ,ự,ý,ỳ,ỷ,ỹ,ỵ,đ,Á,À,Ả,Ã,Ạ,Ă,Ắ,Ằ,Ẳ,Ẵ,Ặ,Â,Ấ,Ầ,Ẩ,Ẫ,Ậ,É,È,Ẻ,Ẽ,Ẹ,Ê,Ế,Ề,Ể,Ễ,Ệ,Í,Ì,Ỉ,Ĩ,Ị,Ó,Ò,Ỏ,Õ,Ọ,Ô,Ố,Ồ,Ổ,Ỗ,Ộ,Ơ,Ớ,Ờ,Ở,Ỡ,Ợ,Ú,Ù,Ủ,Ũ,Ụ,Ư,Ứ,Ừ,Ử,Ữ,Ự,Ý,Ỳ,Ỷ,Ỹ,Ỵ,Đ');

    //unicode to hop
    $composite = preg_split("/\,/", 'á,à,ả,ã,ạ,ă,ắ,ằ,ẳ,ẵ,ặ,â,ấ,ầ,ẩ,ẫ,ậ,é,è,ẻ,ẽ,ẹ,ê,ế,ề,ể,ễ,ệ,í,ì,ỉ,ĩ,ị,ó,ò,ỏ,õ,ọ,ô,ố,ồ,ổ,ỗ,ộ,ơ,ớ,ờ,ở,ỡ,ợ,ú,ù,ủ,ũ,ụ,ư,ứ,ừ,ử,ữ,ự,ý,ỳ,ỷ,ỹ,ỵ,đ,Á,À,Ả,Ã,Ạ,Ă,Ắ,Ằ,Ẳ,Ẵ,Ặ,Â,Ấ,Ầ,Ẩ,Ẫ,Ậ,É,È,Ẻ,Ẽ,Ẹ,Ê,Ế,Ề,Ể,Ễ,Ệ,Í,Ì,Ỉ,Ĩ,Ị,Ó,Ò,Ỏ,Õ,Ọ,Ô,Ố,Ồ,Ổ,Ỗ,Ộ,Ơ,Ớ,Ờ,Ở,Ỡ,Ợ,Ú,Ù,Ủ,Ũ,Ụ,Ư,Ứ,Ừ,Ử,Ữ,Ự,Ý,Ỳ,Ỷ,Ỹ,Ỵ,Đ');

    foreach ($composite as $key => $val)
        $ret_str[$val] = $unicode[$key];

    return strtr($str, $ret_str);
}

function sanitize_text($str){
    // format unicode
    $str = composite_to_unicode($str);

    // clear sign
    $str = unicode_to_nosign($str);

    $str =  preg_replace('/[[:^ascii:]]/', '', $str);

    return trim($str);
}

if (!function_exists('getallheaders')) {

    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

}

//thông tin đăng nhập qua token
if (!function_exists('apache_request_headers')) {

    function apache_request_headers() {
        $arh = array();
        $rx_http = '/\AHTTP_/';
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = array();
                // do some nasty string manipulations to restore the original letter case
                // this should work in most cases
                $rx_matches = explode('_', $arh_key);
                if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val)
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    $arh_key = implode('-', $rx_matches);
                }
                $arh[$arh_key] = $val;
            }
        }
        return( $arh );
    }

}

//function Zip($source, $destination) {
//    if (!extension_loaded('zip') || !file_exists($source)) {
//        return false;
//    }
//
//    $zip = new ZipArchive();
//    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
//        return false;
//    }
//
//    $source = str_replace('\\', '/', realpath($source));
//
//    if (is_dir($source) === true) {
//        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
//
//        foreach ($files as $file) {
//            $file = str_replace('\\', '/', $file);
//
//            // Ignore "." and ".." folders
//            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
//                continue;
//
//            $file = realpath($file);
//
//            if (is_dir($file) === true) {
//                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
//            } else if (is_file($file) === true) {
//                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
//            }
//        }
//    } else if (is_file($source) === true) {
//        $zip->addFromString(basename($source), file_get_contents($source));
//    }
//
//    return $zip->close();
//}

function Zip($source, $destination, $abRootDir = "") {
    if (!extension_loaded('zip') || !file_exists($source)) {
        return false;
    }

    $zip = new ZipArchive();
    if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
        return false;
    }

    $source = str_replace(DIRECTORY_SEPARATOR, '/', realpath($source));
    $rsource = realpath($source) . DIRECTORY_SEPARATOR;

    if (is_dir($source) === true) {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = str_replace(DIRECTORY_SEPARATOR, '/', $file);

// Ignore "." and ".." folders
            if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
                continue;

            $rfile = realpath($file);

            if (is_dir($file) === true) {
                $zip->addEmptyDir(str_replace($rsource, '', $rfile));
            } else if (is_file($file) === true) {
                $basepath = str_replace($rsource, '', $rfile);
                $bfile = str_replace(DIRECTORY_SEPARATOR, '/', $basepath);
                $zip->addFromString($bfile, file_get_contents($rfile));
            }
        }
    } else if (is_file($source) === true) {
        $zip->addFromString(basename($source), file_get_contents($source));
    }

    return $zip->close();
}

function curlHttpPost($url, $arrPost) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, Company\MVC\Json::encode($arrPost));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function iuid() {
    return substr(microtime(true), 0, 14) . "." . hexdec(uniqid());
}

function generate_iuid($root = "1.2.840")
{
    $iuid = iuid();
    return "$root.$iuid";
}

//replace các ký tự gây lỗi chụp ảnh trên nonDicom
function replacePatientName($patientName) {
    $patientName = trim($patientName);
    $character = array("`", "&", "|", "\\", "\"");
    $newPatientName = str_replace($character, "", $patientName);
    return $newPatientName;
}

function removeDir($dir) {
    if (!file_exists($dir))
        return;

    exec("rm -rf {$dir} 2>&1");
}

/**
 * Download file from url
 * @param type $url
 * @param type $toDir
 * @param type $withName
 * @return boolean
 */
function downloadFile($url, $toDir, $withName) {
    // open file in rb mode
    if ($fp_remote = fopen($url, 'rb')) {
        // local filename
        $local_file = $toDir . "/" . $withName;
        // read buffer, open in wb mode for writing
        if ($fp_local = fopen($local_file, 'wb')) {
            // read the file, buffer size 8k
            while ($buffer = fread($fp_remote, 8192)) {

                // write buffer in  local file
                fwrite($fp_local, $buffer);
            }
            // close local
            fclose($fp_local);
        } else {
            // could not open the local URL
            fclose($fp_remote);
            return false;
        }
        // close remote
        fclose($fp_remote);

        return true;
    } else {
        // could not open the remote URL
        return false;
    }
}

function mkdirFromPath($path) {
    $cmd = "mkdir -m 0777 -p " . $path;
    rootexec($cmd);

    if (!file_exists($path)) {
        $paths = explode("/", $path);
        $dirpath = "";
        foreach ($paths as $dir) {
            if (!strlen($dir)) {
                $dirpath .= "/";
                continue;
            }

            $dirpath .= $dir . "/";
            // Kiểm tra có quyền ghi tạo thư mục và file ko
            if (!file_exists($dirpath)) {
                $cmd = "mkdir -m 0777 " . $dirpath;
                rootexec($cmd);
                if (!file_exists($dirpath)) {
                    break;
                }
            }
        }
    }
}

function getFilesFromPath($dir) {
    $files = [];
    $dirs = [];
    if (is_dir($dir)) {
        $files = array_diff(scandir($dir), array(".", ".."));
        $tmp = [];
        foreach ($files as &$file) {
            if (in_array($file, ['.', '..']) || is_dir($dir . "/" . $file)) {
                continue;
            }

            $tmp[] = $dir . "/" . $file;
        }
        $files = $tmp;

        $dirs = scandir($dir);
    }

    foreach ($dirs as $key => $value) {
        if (!in_array($value, ['.', '..'])) {
            if (is_dir($dir . "/" . $value)) {
                $_files = getFilesFromPath($dir . DIRECTORY_SEPARATOR . $value);
                $files = array_merge($files, $_files);
            }
        }
    }

    return $files;
}

function convertImageJPG($originalImage, $outputImage) {
    $exploded = explode('.', $originalImage);
    $ext = strtolower($exploded[count($exploded) - 1]);

    if (preg_match('/jpg|jpeg/i', $ext))
        $imageTmp = imagecreatefromjpeg($originalImage);
    else if (preg_match('/png/i', $ext))
        $imageTmp = imagecreatefrompng($originalImage);
    else if (preg_match('/gif/i', $ext))
        $imageTmp = imagecreatefromgif($originalImage);
    else if (preg_match('/bmp/i', $ext))
        $imageTmp = !function_exists('imagecreatefrombmp') ? createImageFromBmp($originalImage) : imagecreatefrombmp($originalImage);
    else
        return 0;

    // quality is a value from 0 (worst) to 100 (best)
    imagejpeg($imageTmp, $outputImage, 100);
    imagedestroy($imageTmp);

    if (!file_exists($outputImage) || !filesize($outputImage)) {
        $cmd = "ffmpeg -i {$originalImage} $outputImage";
        exec($cmd . " 2>&1");
    }
}

function createImageFromBmp($filename) {
    //Ouverture du fichier en mode binaire
    if (!$f1 = fopen($filename, "rb"))
        return FALSE;
    //1 : Chargement des ent�tes FICHIER
    $FILE = unpack("vfile_type/Vfile_size/Vreserved/Vbitmap_offset", fread($f1, 14));
    if ($FILE['file_type'] != 19778)
        return FALSE;
    //2 : Chargement des ent�tes BMP
    $BMP = unpack('Vheader_size/Vwidth/Vheight/vplanes/vbits_per_pixel' .
            '/Vcompression/Vsize_bitmap/Vhoriz_resolution' .
            '/Vvert_resolution/Vcolors_used/Vcolors_important', fread($f1, 40));
    $BMP['colors'] = pow(2, $BMP['bits_per_pixel']);
    if ($BMP['size_bitmap'] == 0)
        $BMP['size_bitmap'] = $FILE['file_size'] - $FILE['bitmap_offset'];
    $BMP['bytes_per_pixel'] = $BMP['bits_per_pixel'] / 8;
    $BMP['bytes_per_pixel2'] = ceil($BMP['bytes_per_pixel']);
    $BMP['decal'] = ($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] -= floor($BMP['width'] * $BMP['bytes_per_pixel'] / 4);
    $BMP['decal'] = 4 - (4 * $BMP['decal']);
    if ($BMP['decal'] == 4)
        $BMP['decal'] = 0;
    //3 : Chargement des couleurs de la palette
    $PALETTE = array();
    if ($BMP['colors'] < 16777216) {
        $PALETTE = unpack('V' . $BMP['colors'], fread($f1, $BMP['colors'] * 4));
    }
    //4 : Cr�ation de l'image
    $IMG = fread($f1, $BMP['size_bitmap']);
    $VIDE = chr(0);
    $res = imagecreatetruecolor($BMP['width'], $BMP['height']);
    $P = 0;
    $Y = $BMP['height'] - 1;
    while ($Y >= 0) {
        $X = 0;
        while ($X < $BMP['width']) {
            if ($BMP['bits_per_pixel'] == 24)
                $COLOR = unpack("V", substr($IMG, $P, 3) . $VIDE);
            elseif ($BMP['bits_per_pixel'] == 16) {
                $COLOR = unpack("n", substr($IMG, $P, 2));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 8) {
                $COLOR = unpack("n", $VIDE . substr($IMG, $P, 1));
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } elseif ($BMP['bits_per_pixel'] == 4) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 2) % 2 == 0)
                    $COLOR[1] = ($COLOR[1] >> 4);
                else
                    $COLOR[1] = ($COLOR[1] & 0x0F);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            }
            elseif ($BMP['bits_per_pixel'] == 1) {
                $COLOR = unpack("n", $VIDE . substr($IMG, floor($P), 1));
                if (($P * 8) % 8 == 0)
                    $COLOR[1] = $COLOR[1] >> 7;
                elseif (($P * 8) % 8 == 1)
                    $COLOR[1] = ($COLOR[1] & 0x40) >> 6;
                elseif (($P * 8) % 8 == 2)
                    $COLOR[1] = ($COLOR[1] & 0x20) >> 5;
                elseif (($P * 8) % 8 == 3)
                    $COLOR[1] = ($COLOR[1] & 0x10) >> 4;
                elseif (($P * 8) % 8 == 4)
                    $COLOR[1] = ($COLOR[1] & 0x8) >> 3;
                elseif (($P * 8) % 8 == 5)
                    $COLOR[1] = ($COLOR[1] & 0x4) >> 2;
                elseif (($P * 8) % 8 == 6)
                    $COLOR[1] = ($COLOR[1] & 0x2) >> 1;
                elseif (($P * 8) % 8 == 7)
                    $COLOR[1] = ($COLOR[1] & 0x1);
                $COLOR[1] = $PALETTE[$COLOR[1] + 1];
            } else
                return FALSE;
            imagesetpixel($res, $X, $Y, $COLOR[1]);
            $X++;
            $P += $BMP['bytes_per_pixel'];
        }
        $Y--;
        $P += $BMP['decal'];
    }
    //Fermeture du fichier
    fclose($f1);
    return $res;
}


function execCqlDir($module) {
    $cqlDir = $module->getmoduleDir() . '/cql';
    if (file_exists($cqlDir)) {
        $items = scandir($cqlDir);
        sort($items);
        foreach ($items as $item) {
            if (strpos($item, '.cql') === false) {
                continue;
            }
            $path = $cqlDir . '/' . $item;

            Company\Cassandra\Mapper::importCQL($path);

        }
    }
}

function createElasticIndex($module) {
    $dir = $module->getmoduleDir() . '/elastic';
    if (file_exists($dir)) {
        $items = scandir($dir);
        sort($items);
        foreach ($items as $item) {
            if (strpos($item, 'mapping.json') === false) {
                continue;
            }

            $mappingFile = $dir . '/' . $item;

            $content = json_decode(file_get_contents($mappingFile), true);
            $elasticConn = \Company\ElasticSearch\ElasticMapper::makeInstance()->getConn();

            // nhung file can update phai co tien to update o dau
            if (strpos($item, 'update') === false) {
                if (!$elasticConn->indices()->exists(["index" => $content["index"]]))
                    $elasticConn->indices()->create([
                        "index" => $content["index"],
                        "body" => [
                            "mappings" => $content["mappings"],
                            "settings" => \Company\ElasticSearch\DB::getSettings()
                        ]
                    ]);
            } else {
                $elasticConn->indices()->putMapping([
                    "index" => $content["index"],
                    "body" => [
                        '_source' => [
                            'enabled' => true
                        ],
                        "properties" => $content["properties"],
                    ]
                ]);
            }

        }
    }
}

function setNestedKey(&$data, $array_key, $value) {
    $temp = &$data;
    foreach($array_key as $key) {
        $temp = &$temp[$key];
    }
    $temp = $value;
    unset($temp);
}

/**
 * Recursively computes the intersection of arrays using keys for comparison.
 *
 * @param   array $array1 The array with master keys to check.
 * @param   array $array2 An array to compare keys against.
 * @return  array associative array containing all the entries of array1 which have keys that are present in array2.
 **/
function array_intersect_key_recursive(array $array1, array $array2) {
    $array1 = array_intersect_key($array1, $array2);
    foreach ($array1 as $key => &$value) {
        if (is_array($value) && is_array($array2[$key])) {
            $value = array_intersect_key_recursive($value, $array2[$key]);
        }
    }
    return $array1;
}

function isJson( $raw_json ){
    return is_array(json_decode( $raw_json , true ));
}

function isDatetime($date, $format = 'Y-m-d H:i:s')
{
    $d = date_create($date);
    // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
    return $d && $d->format($format) === $date;
}


define ('DATE_RFC3339_EXTENDED_2', "Y-m-d\TH:i:s.vO");
define ('DATE_NORMAL', "Y-m-d H:i:s");

function convertMicrotimeToDate($microtime, $format = DATE_RFC3339_EXTENDED_2) {
    $utime = sprintf('%.4f', $microtime);
    $raw_time = \DateTime::createFromFormat('U.u', $utime);
    $raw_time->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $datetime = $raw_time->format($format);

    //neu format chua timezone thi phai chuan hoa vi co truong hop loi ( .0000 )
    if (strpos($format, "O") !== false or strpos($format, "P") !== false) {
        $timezoneBegin = strpos($datetime, "+") ?: strripos($datetime, "-");
        if ($timezoneBegin !== 23)
            $datetime = substr($datetime, 0, 23) . substr($datetime, $timezoneBegin, strlen($datetime));

    }

    return $datetime;
}

// get micro digits
function getMicro($microtime) {
    return sprintf("%06d",($microtime - floor($microtime)) * 1000000);
}

// Converting json to array recursive
function json_decode_array($input) {
    $from_json =  json_decode($input, true);
    return $from_json ?: $input;
}

function buildEnvArray() {
    $configExampleFile =  ENV_DIR . "/config.example.json";

    $data = json_decode(file_get_contents($configExampleFile), true);
    array_walk($data, function(&$value, $key){
        $env = getenv($key);
        if ($env !== false && $env != "") {
            $value = $env;
        }
    });

    return $data;
}

function env($key, $defaultValue = null) {
    return arrData($_ENV, $key, $defaultValue);
}

function output($buffer) {
    \Pacs\Web\Lib\Response::render($buffer);
}

function set_status($statusCode) {
    \Pacs\Web\Lib\Response::setStatus($statusCode);
}

function set_header($key, $value) {
    \Pacs\Web\Lib\Response::setHeader($key, $value);
}

function get_header($key, $defaultValue = null) {
    return \Pacs\Web\Lib\Request::getHeader($key, $defaultValue);
}

function get_param($key = null, $defaultValue = null) {
    if (!$key)
        return $_GET;

    return arrData($_GET, $key, $defaultValue);
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}