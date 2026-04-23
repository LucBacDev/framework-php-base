<?php

namespace Company\User\Controller;

use Company\Auth\Auth;
use Company\Exception\BadRequestException;
use Company\Site\Model\SiteMapper;
use Company\User\Model as M;
use Company\User\Model\UserMapper;
use Company\User\User;

class UserCtrl extends \Company\MVC\Controller {

    /** @var M\UserMapper */
    protected $userMapper;
    protected $auth;

    function init() {
        parent::init();
        $this->userMapper = M\UserMapper::makeInstance();
        $this->auth = Auth::getInstance();
    }

    function updateUser($siteID, $id = null) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $data = $this->input();

            $data['siteFK'] = $siteID;
            $result = $this->userMapper->updateUser($id, $data);

            $this->resp->setBody(json_encode($result));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }

    function changePassword($siteID) {
        $this->auth->requireLogin();
        $this->auth->requireSite($siteID);

        $data = $this->input();
        $account = $data["account"];
        $oldPassword = $data["oldPassword"];
        $newPassword = $data["newPassword"];
        $type = $data["type"];

        if (!UserMapper::checkPasswordStrength($newPassword))
            throw new BadRequestException("New passwod invalid!");

        $user = UserMapper::makeInstance()
            ->setLoadLogin()
            ->filterLogin($type, $account)
            ->getEntityOrFail();

        $dbPass = $user->login[$type]->passwd;

        $verify = password_verify($oldPassword, $dbPass);

        if (!$verify)
            throw new \Company\Exception\BadRequestException("Old password not match");

        M\UserLoginMapper::makeInstance()
            ->where('`type`=? AND account=?', __FUNCTION__)
            ->setParamWhere($type, __FUNCTION__ . 1)
            ->setParamWhere($account, __FUNCTION__ . 2)
            ->update([
                "passwd" => password_hash($newPassword, PASSWORD_BCRYPT)
            ]);

        $this->resp->setBody(json_encode(result(true)));
    }

    function getUser($siteID, $id) {
        $this->auth->requireLogin();
        $user = $this->userMapper->makeInstance()
                ->filterSiteFK($siteID)
                ->filterID($id)
                ->setLoadLogin()
                ->setLoadPrivileges()
                ->setLoadRoles()
                ->setLoadDep()
                ->getEntity();

        $this->resp->setBody(json_encode($user));
    }

    function getUsers($siteID) {
        $mapper = $this->userMapper->makeInstance()
                ->filterDepFK($this->req->get('parentID'))
                ->filterName($this->req->get('fullname'))
                ->filterActive($this->req->get('active'))
                ->filterDeleted()
                ->setLoadPrivileges()
                ->filterSiteFK($siteID);

        if ($this->req->get('loadDep')) {
            $mapper->setLoadDep();
        }

        $users = $mapper->getEntities();

        $this->resp->setBody(json_encode($users->toArray()));
    }

    function deleteUser($siteID, $id) {
        try {
            $this->auth->setSiteID($siteID);
            $this->auth->requireSite($siteID);
            $this->auth->requireAdmin();

            $this->userMapper->deleteUser($siteID, $id);
            $this->resp->setBody(json_encode(result(true)));
        } catch (\Exception $e) {
            $this->resp->setStatus(400);
            $this->resp->setBody(json_encode(['result' => false, 'message' => $e->getMessage()]));
        }
    }


    // lấy danh sách site để chọn
    function getUserSites($siteID, $userID) {
        $this->auth->requireLogin();
        $pageSize = $this->req->get('pageSize', 10);
        $pageNo = $this->req->get('pageNo', 1);

        $privs = $this->auth->getUser()->privileges;

        // là admin tổng sẽ hiện hết tất cả các site
        if (in_array('fullcontrol', $privs)) {
            $sites = SiteMapper::makeInstance()
                ->filterName($this->req->get('name'))
                ->setPage($pageNo, $pageSize)
                ->getPage();
        } else {
            // lấy user đang đăng nhập
            $user = $this->auth->getUser();
            if ($user->id != $userID) {
                throw new E\BadRequestException("Error userID: " . $userID);
            }
            $arrSiteID = \Company\User\Model\UserMapper::makeInstance()->getSitesID($userID);
            // lấy danh sách các site thuộc user đó
            $sites = SiteMapper::makeInstance()
                ->filterID($arrSiteID)
                ->filterName($this->req->get('name'))
                ->setPage($pageNo, $pageSize)
                ->getPage();
        }

        $this->resp->setBody(json_encode($sites));
    }

    // đồng bộ tài khoản
    function updateMergeSite($siteID) {
        $this->auth->requireLogin();

        $accountMerge = $this->input('mainAccount');
        if(!$accountMerge)
            throw new BadRequestException("mainAccount must not empty");
        $accountConnect = $this->input('account');
        if(!$accountConnect)
            throw new BadRequestException("account must not empty");
        $passwordConnect = $this->input('password');

        $user = $this->auth->getUser();
        // tài khoản đang đăng nhập vào hệ thống
        $accountCurrent = $user->login['localdb']->account;
        // check tài khoản kết nối và tài khoản đang đăng nhập
        if ($accountCurrent == $accountConnect) {
            $result = result(false, "Account is login don't connect");
        } else {
            $AccConnect = Auth::getInstance()->authAll(
                $accountConnect
                , $passwordConnect);
            // kiểm tra xem có kết nối thành công đến tài khoản không?
            if ($AccConnect) {
                //check quyền của site kết nối đến
                $privs = $AccConnect->privileges;

                // check site của tài khoản kết nối đến
                $arrSiteID = \Company\User\Model\UserMapper::makeInstance()->getSitesID($AccConnect->id);
                if (in_array($siteID, $arrSiteID)) {
                    $result = result(false, 'Account connect is same site');
                } else {
                    if ($accountConnect == $accountMerge) {
                        $this->userMapper->updateMergeSite($user, $AccConnect);
                    } else {
                        $this->userMapper->updateMergeSite($AccConnect, $user);
                    }
                    $result = result(true);
                }
            } else {
                $result = result(false, 'Account or password is incorrect');
            }
        }

        $this->resp->setBody(json_encode($result));
    }

    function test($siteID) {
        Auth::getInstance()->requireLogin();
        Auth::getInstance()->setSiteID($siteID);
        var_dump(Auth::getInstance()->hasPrivilege('manageRole'));
    }

}
