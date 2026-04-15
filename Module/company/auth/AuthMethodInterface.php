<?php

namespace Company\Auth;

interface AuthMethodInterface {

    /**
     * Tên loại xác thực đăng kí với hệ thống
     */
    static function getName();

    /**
     * Cập nhật CSDL
     * @param type $userID
     * @param type $type
     * @param type $account
     * @param type $passwd
     */
    function handleLoginUpdate($userID, $type, $account, $passwd);

    /**
     * Đăng nhập
     * @param string $account
     * @param string $password
     */
    function auth($account, $password);
}
