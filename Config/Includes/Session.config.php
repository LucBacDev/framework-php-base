<?php

$exports['session'] = [
    //tự hết hạn sau 1h nếu không refresh, tính bằng giây
    'expire' => 3600,
    //thời gian cần update lại thông tin user trong session
    'updateInterval' => 60,
];
