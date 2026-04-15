<?php

namespace Company\MVC;

class Hardware {

    function diskSignature() {
        $dump = shell_exec("df -h");
        $dump = explode("\n", $dump);
        array_splice($dump, 0, 1);
        $ret = [];

        //format du lieu chuan
        foreach ($dump as &$dumpRow) {
            $dumpRow = explode(' ', $dumpRow);
            $retRow = [];
            foreach ($dumpRow as $dumpValue) {
                if ($dumpValue) {
                    $retRow[] = $dumpValue;
                }
            }
            $ret[] = $retRow;
        }
       
        //lay du lieu khong thay doi
        $signature = '';
        foreach ($ret as $row) {
            if(!$row){continue;}
            $signature .= $row[0] . $row[1] . end($row);
        }
        // file deepcode ignore InsecureHash: not password
        return md5($signature);
    }

    function cpuSignature() {
        $dump = shell_exec("cat /proc/cpuinfo | grep model");
        return md5($dump);
    }
    
    function networkSignature() {
        $dump = shell_exec("cat /host/network/interfaces");
        if(!$dump){
            die("Warning license error");
        }
        return md5($dump);
    }

    function hardwareSignature() {
//        return md5($this->cpuSignature().$this->diskSignature().$this->networkSignature());
        return md5($this->cpuSignature().$this->diskSignature());
    }

}
