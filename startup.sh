#!/bin/bash

# check install service manager
FILE_SERVICE_MANAGER_CONFIG=/etc/supervisor.d/service_manager.conf
if [ ! -f "$FILE_SERVICE_MANAGER_CONFIG" ]; then
    php -f /var/www/html/Module/company/service/Exec/copyConfig.php
fi