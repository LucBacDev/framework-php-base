<?php

namespace Company\Theme;

use Company\Exception as Ex;
use Company\Exception\BadRequestException;
use Company\Exception\NotFoundException;
use Company\MVC\Theme;
use Company\Cache\CacheDriver;

class ThemeCtrl extends \Company\MVC\Controller {

    function themeContent($theme, $filePath) {
        if (!is_array($filePath)) {
            $filePath = [$filePath];
        }
        $themeObj = $this->getTheme($theme);
        $file = $themeObj->getThemeDir() . '/public/' . implode('/', $filePath);

        if (!file_exists($file)) {
            throw new Ex\NotFoundException();
        }

        header('Content-type: ' . $this->mime_content_type($file));
        if(app()->config['production']) {
            header('Cache-Control: max-age=31536000, public');
        } else {
            header('Cache-Control: no-cache, must-revalidate');
        }
        require($file);
        exit;
    }

    function moduleContent($vendor, $comp, $filePath) {
        if (!is_array($filePath)) {
            $filePath = [$filePath];
        }
        $moduleObj = \Company\MVC\Module::getInstance("$vendor/$comp");
        $file = $moduleObj->getmoduleDir() . '/public/' . implode('/', $filePath);
        if (!file_exists($file)) {
            throw new Ex\NotFoundException();
        }

        header('Content-type: ' . $this->mime_content_type($file) . '; charset=utf-8');
        if(app()->config['production']) {
            header('Cache-Control: max-age=31536000, public');
        } else {
            header('Cache-Control: no-cache, must-revalidate');
        }
       
        require($file);
        exit;
    }

    protected function getTheme($theme) {
        $themeObj = Theme::getTheme($theme);
        if (!$themeObj) {
            throw new Ex\NotFoundException("Theme not found: $theme");
        }

        return $themeObj;
    }

    protected function mime_content_type($filename) {
        $mime_types = array(
            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',
            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',
            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',
            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',
            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',
            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $fileParts = explode('.', $filename);
        $ext = strtolower(array_pop($fileParts));
        if ($ext == 'php') {
            //trường hợp *.js.php, *.css.php
            $ext = 'js';
        }
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        } elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        } else {
            return 'application/octet-stream';
        }
    }

    function lang($vendor, $module, $lang) {
        $moduleObj = \Company\MVC\Module::getInstance("$vendor/$module"); 
        $file = $moduleObj->getmoduleDir() . '/lang/' . $lang . '.json';
        if(!file_exists($file)) {
            throw new Ex\NotFoundException();
        }

        header("Content-type: application/json; charset=utf-8");
        if(app()->config['production']) {
            header('Cache-Control: max-age=31536000, public');
        } else {
            header('Cache-Control: no-cache, must-revalidate');
        }
        readfile($file);
        die;
    }

    function babelLoader($vendor, $module) {
         // Sanitize $vendor and $module to allow only alphanumeric and underscore
        $vendor = preg_replace('/[^a-zA-Z0-9_]/', '', $vendor);
        $module = preg_replace('/[^a-zA-Z0-9_]/', '', $module);
        if(!$this->req->get('path')) {
            throw new BadRequestException("path cannot be null");
        }
       
        $rootPath = BASE_DIR . "/Module/$vendor/$module/public/";
        if(!file_exists($rootPath)) {
            throw new BadRequestException("Invalid path"); 
        }
        $path = realpath($rootPath . trim($this->req->get('path'), '/'));
        if(strpos($path, $rootPath) !== 0) {  //handle path traversal, path must begins with $rootPath
            throw new BadRequestException("Invalid path");
        } 

        if(!file_exists($path)) {
            throw new NotFoundException("Path not found");
        }
        $cachedJS = app()->config['tmp']['local'] . md5($path) . '.js';
        if (file_exists($cachedJS) && app()->config['production']) {
            header('Content-type: application/json');
            readfile($cachedJS);
            die;
        }
        //path can be standalone JS file or autoload.json
        if(strpos($path, 'autoload.json')) {
            $dir = BASE_DIR . "/Module/$vendor/$module/public/";
            //autoload.json
            $autoloadDeclare = json_decode(file_get_contents($path), true);
            $jsx = '';
            foreach ($autoloadDeclare as $path => $type) {
                $path = trim($path, '/');
                switch ($type) {
                    case 'dir':
                        
                        $jsx .= $this->outputDir($dir . $path);
                        break;
                    case 'file':
                        $jsx .= $this->outputFile($dir, $path);
                        break;
                    default:
                        throw new \Exception("invalid autoload.json");
                }
            }
        } else {
            //standalone file
            $jsx = file_get_contents($path);
        }

        $tmpJsx = app()->config['tmp']['local'] . md5($cachedJS) . '.jsx';
        if (app()->config["production"] == 0 && isset($_GET['debug'])){
            var_dump($tmpJsx, $jsx);
        }
        
        if (!file_put_contents($tmpJsx, $jsx)) {
            throw new \Exception("Cannot write temp jsx");
        }
        
        $cmd = "babel --presets react,es2015,stage-0 $tmpJsx 2>&1";
        
        //check if need to recompile by comparing byte size
       
        if (!file_exists($tmpJsx)){
            throw new \Exception("Cannot find temp jsx");
        }
        //cache using hash when dev
        if(app()->config["production"] == 0)
            $cachedJS = app()->config['tmp']['local'] . md5_file($tmpJsx) . '.js';
        if(file_exists($cachedJS)) {
            header('Content-type: application/json');
            readfile($cachedJS);
            die;
        }

//        unlink($tmpJsx);
        $compiledJS = shell_exec($cmd);

        if ($compiledJS) {
            file_put_contents($cachedJS, $compiledJS);
        }
        header('Content-type: application/json');
        echo $compiledJS;
        die;
    }

    protected function outputDir($dir, $firstFiles = [], $lastFiles = []) {
        if (!file_exists($dir)) {
            return "\n//dir not found,skip\n";
        }
        $exclude = array_merge($firstFiles, $lastFiles);
        $jsx = '';
        foreach ($firstFiles as $item) {
            $jsx .= $this->outputFile($dir, $item);
        }
        foreach (scandir($dir) as $item) {
            if (strpos($item, '.js') !== false && !in_array($item, $exclude)) {
                $jsx .= $this->outputFile($dir, $item);
            }
        }
        foreach ($lastFiles as $item) {
            $jsx .= $this->outputFile($dir, $item);
        }
        return $jsx;
    }

    protected function outputFile($dir, $item) {
        $jsx = "\n\n//$item\n";
        $path = $dir . '/' . $item;
        if (file_exists($path)) {
            return $jsx . file_get_contents($dir . '/' . $item);
        }
    }

}
