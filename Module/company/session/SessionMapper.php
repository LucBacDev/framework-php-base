<?php

namespace Company\Session;

class SessionMapper extends \Company\SQL\Mapper {

    public function tableAlias() {
        return 'ssd';
    }

    public function tableName() {
        return 'session_data';
    }

    /**
     * lưu session vào DB
     * @param string $id
     * @param mixed $data
     * @param string $expireDate
     */
    function save($id, $data, $expireDate) {
       /* $updateData = [
            'id' => $id,
            'session' => serialize($data),
            'expire' => $expireDate
        ];
        if ($this->filterID($id)->isExists()) {
            $this->filterID($id)->update($updateData);
        } else {
            $this->insert($updateData);
        }*/
    }

    /**
     * Load session tu CSDL
     * @param type $id
     * @return array session
     */
    function loadSession($id) {
     /*   $session = $this->makeInstance()->filterID($id)->getEntity();
        //kiểm tra thời gian hết hạn
        $session->expire = \DateTimeEx::create($session->expire);
        $now = \DateTimeEx::create();
        if ($session->expire < $now) {
            return null;
        }
*/
        //parse data
        //return unserialize($session->session);
        return $_SESSION;
    }

}
