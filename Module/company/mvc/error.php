<?php
$message = $argument instanceof \Exception ? $argument->getMessage() : 'Uncaught error';

if ($this->slim->request->headers('content-type') == 'application/json') {
    $this->slim->response->setBody(json_encode(result(false, ['error' => $message])));
    $this->slim->stop();
}

$status = $this->response->getStatus();
$arrStatuses = [
    400 => 'Bad request',
    401 => 'Unauthorized',
    403 => 'Forbidden',
    404 => 'Not found'
];

$arrMessages = [
    400 => 'Dữ liệu đầu vào không hợp lệ.',
    401 => 'Bạn chưa đăng nhập hoặc thông tin đăng nhập không hợp lệ.',
    403 => 'Bạn không có quyền truy cập chức năng này hoặc nghiệp vụ không cho phép.',
    404 => 'Đường dẫn bạn truy cập không đúng. Vui lòng kiểm tra lại URL hoặc quay lại trang chủ.'
];
if (!$message) {
    $message = arrData($arrMessages, $status, 'Xảy ra lỗi hệ thống');
}

//nếu là JSON
if ($this->request->headers('content-type') == 'application/json') {
    $this->response->headers->set('content-type', 'application/json');
    header('Content-type', 'application/json');
    $this->response->setBody(json_encode(result(false, $message)));
    return;
}
?>
<html>
    <head>
        <meta charset="uft8">
    </head>
    <body>
        <style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{display:inline-block;width:65px;}</style>
        <h1><?php echo arrData($arrStatuses, $status, 'Internal server error') ?></h1>
        <p><b>Thông tin lỗi</b>: <?php echo $message ?></p>
        <p><a href="<?php echo url() ?>">Quay lại trang chủ</a> hoặc liên hệ <b>Quản trị hệ thống</b> để biết thêm chi tiết.</p>
    </body>
</html>

