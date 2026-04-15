<?php

// add config /etc/supervisor.d
if (!file_exists("/etc/supervisor.d/service_manager.conf")) {
    if (!copy(dirname(__DIR__)."/Config/supervisor.d/service_manager.conf", "/etc/supervisor.d/service_manager.conf")) {
        echo "failed to copy service_manager.conf\n";
    }
}