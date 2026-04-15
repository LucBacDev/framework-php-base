<?php

namespace Company\Auth;

use Company\Cache\CacheDriver;
use Company\Exception as Ex;
use Company\License\Model\LicenseMapper;
use Company\MVC\License;
use Company\Session\Session;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Pacs\Web\Lib\RemoteAddress;
use Company\User\Model\UserMapper;
use mysql_xdevapi\Result;
use Pacs\Setting\Model\SettingMapper;

class Auth {

    const PRIV_ACCESS_ADMIN = 'accessAdmin';
    const PRIV_FULL_CONTROL = 'fullcontrol';

    /**
     *
     * @var AuthMethodInterface 
     */
    static protected $loginMethods = [];
    static protected $instance;
    protected $siteID = '';
    protected $license;
    protected $listLicese = '';

    /**
     * 
     * @param int $userUpdateInterval gian phải load lại thông tin user vào session
     */
    static function config($userUpdateInterval) {
        Auth::$userUpdateInterval = $userUpdateInterval;
    }

    /** @var int thời gian phải load lại thông tin user vào session */
    static protected $userUpdateInterval = 60;
    protected $user;

    /**
     * Đăng kí login Method mới
     * @param AuthMethodInterface $handler
     */
    static function registerAuthMethod(AuthMethodInterface $handler) {
        Auth::$loginMethods[$handler->getName()] = $handler;
    }

    /**
     * 
     * @return Auth
     */
    static function getInstance() {
        if (!static::$instance) {
            Auth::$instance = new static;
        }
        return Auth::$instance;
    }

    function __construct() {
        if (php_sapi_name() == "cli") {
            // load user session chỉ cần đố với browser request , cli ko cần
            return;
        }
    }

    /**
     * 
     * @param string $name
     * @return AuthMethodInterface
     * @throws \Exception
     */
    function getAuthMethod($name) {
        if (!isset(Auth::$loginMethods[$name])) {
            throw new \Exception("Login method not registered: $name");
        }
        return Auth::$loginMethods[$name];
    }

    /**
     * Gọi các hàm xử lý xác thực NSD
     * @param string $type
     * @param string $input
     * @param string $password
     * @return array
     */
    function auth($type, $account, $password, $captcha) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
//        $_SESSION = [];
//        session_destroy();

        $authMethod = $this->getAuthMethod($type);
        $cache = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
        $cacheKey = "account/$account";

        if ($cache->contains("lock_account/$account"))
            if (($lockTime = (int) $cache->get("lock_account/$account")) > time())
                return result(false, ["error" => "Nhập tài khoản/mật khẩu sai quá 10 lần, khoá tài khoản trong 1 phút", "lockTime" => $lockTime], 2);
            else {
                unset($_SESSION["captcha_code"]);
            }

        $count = (int) $cache->get($cacheKey);
        try {
            $user = $authMethod->auth($account, $password);

            // neu co captcha thi check
            if (isset($_SESSION["captcha_code"]) && $count >= 3)
                if ($_SESSION["captcha_code"] != $captcha)
                    throw new Ex\NotFoundException("Captcha Error", 100);

            // neu thanh cong
            unset($_SESSION["captcha_code"]);
            $cache->delete($cacheKey);
        } catch (Ex\NotFoundException $ex) {
            if (!$cache->contains($cacheKey))
                $cache->set($cacheKey, 0, 60);

            $cache->incr($cacheKey);
            ++$count;
            if ($count >= 3) {
                if ($count == 10) {
                    $cache->set("lock_account/$account", time() + 60, 60);
                    $cache->delete($cacheKey);
                }

                if ($ex->getCode() == 100)
                    return result(false, ["error" => "Nhập sai captcha"], 100);

                return result(false, ["error" => "Nhập tài khoản/mật khẩu sai quá nhiều lần"], 1);
            }

            return result(false, ["error" => $ex->getMessage()], 0);
        }
        $session = Session::getInstance();
        if ($user) {
            $session->set('user', $user);
        }

        return result(true, [
            'sessionID' => $session->getID(),
            'user' => $user
        ]);
    }

    function logout() {
        Session::getInstance()->clearSession();
    }

    /**
     * Thử xác thực tất cả method
     * @param string $account
     * @param string $password
     * @return type $user
     */
    function authAll($account, $password) {
        foreach (Auth::$loginMethods as $method) {
            try {
                $user = $method->auth($account, $password);
                return $user;
            } catch (\Exception $ex) {
                
            }
        }
    }

    protected function basicAuthEncode($user, $passwd) {
        return base64_encode($user . ':' . $passwd);
    }

    function loadUserFromSession() {
        $slim = \Company\MVC\Bootstrap::getInstance()->slim;
        $session = Session::getInstance();
        //kiểm tra thông tin đăng nhập qua GET

        if (isset($_GET['user']) && isset($_GET['passwd'])) {
            //cache trong bang session
            $username = base64_decode($_GET['user']);
            $passwd = base64_decode($_GET['passwd']);
            $sessionID = $this->basicAuthEncode($username, $passwd);
            $session->setID($sessionID);
            $user = $session->get('user');
            if (!$user || !$user->id) {
                $user = $this->authAll($username, $passwd);
            }
            if ($user && $user->id) {
                return $user;
            }
        }
        //thông tin đăng nhập qua token
        $headers = apache_request_headers();
        $headers = array_change_key_case($headers, CASE_UPPER);
        $authHeader = arrData($headers, 'AUTHORIZATION');

        if (strpos($authHeader, 'Token ') !== false) {
            $token = str_replace('Token ', '', $authHeader);
            $session->setID($token);
            $user = $session->get('user');
            if ($user && $user->id) {
                return $user;
            }
        }

        //thông tin đăng nhập basic
        if (strpos($authHeader, 'Basic ') !== false) {
            $token = str_replace('Basic ', '', $authHeader);
            $session->setID($token);
            //trường hợp đã cache tại session
            $user = $session->get('user');
            if (!$user || !$user->id) {
                //trường hợp chưa cache tại session
                //giải mã user/password
                $token = explode(':', base64_decode($token));

                $user = $this->authAll($token[0], $token[1]);
            }

            if ($user && $user->id) {
                $session->set('user', $user);
                return $user;
            }
        }

        if (isset($_COOKIE['session'])) {
            $session->setID($_COOKIE['session']);
            $user = $session->get('user');
            if ($user && $user->id) {
                return $user;
            }
        }
    }

    /**
     * 
     * @param \Company\SQL\Entity $user
     * @throws Ex\UnauthorizedException
     */
    function requireLogin($user = null) {
        $user = $user ? $user : $this->getUser();

        if (!$user || !$user->id) {
            if(app()->isRest())
                throw new Ex\UnauthorizedException();
            else {
                header('Location: ' . url('/auth/login'));
                die;
            }
        }
        return $this;
    }

    function getUser() {
        if (!$this->user) {
            $this->user = $this->loadUserFromSession();
        }
        if ($this->user) {
            $needUpdate = \DateTimeEx::create(Session::getInstance()->get('needUpdateAfter'));
            $now = \DateTimeEx::create();
            if ($needUpdate <= $now) {
                //cần load lại thông tin user
                $cache = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
                $userID = $this->user->id;
                $this->user = unserialize($cache->get('user/' . $userID));

                if (!$this->user || !$this->user->id) {
                    $this->user = UserMapper::makeInstance()
                            ->filterActive()
                            ->setLoadRoles()
                            ->setLoadPrivileges()
                            ->setLoadLogin()
                            ->setLoadDep()
                            ->filterID($userID)
                            ->getEntity();

                    $cache->set('user/' . $this->user->id, serialize($this->user), 60);
                }

                Session::getInstance()->set('needUpdateAfter', $now->addSecond(Auth::$userUpdateInterval)->toIsoString());
            }

            Session::getInstance()->set('user', $this->user);
        }
        if (!$this->license) {
            $this->license = new License();
        }
        return $this->user;
    }

    static function getUsername($user) {
        if (!$user)
            return "";

        if (is_object($user->login["localdb"]))
            return $user->login["localdb"]->account;

        if (is_array($user->login["localdb"]))
            return arrData($user->login, ["localdb", "account"]);

        return [];
    }

    /**
     * 
     * @param \Company\SQL\Entity $user
     * @return type
     */
    function isAdmin($user = null) {
        return $this->hasPrivilege(static::PRIV_ACCESS_ADMIN, $user);
    }

    /**
     * 
     * @param \Company\SQL\Entity $user
     */
    function requireAdmin($user = null) {
        $this->requireLogin($user);
        $user = $user ?: $this->getUser();
        $this->requirePrivilege(static::PRIV_ACCESS_ADMIN, $user);
        return $this;
    }

    /**
     * Lấy danh sách license lưu trong db
     * @param type $siteID
     * @param type $productName
     * @return type
     */
    function getLicenses($siteID, $productName = null) {
        if (!$this->listLicese) {
            $mapper = LicenseMapper::makeInstance()
                    ->filterSiteFK($siteID);

            if ($productName) {
                $mapper->filterProductName($productName);
            }
            $this->listLicese = $mapper->getEntities()->toArray();
        }
        return $this->listLicese;
    }

    /**
     * @param type $siteID
     * @param type $productName
     * @return result($status, $data) $status is true or false
     */
    function requireLicense($siteID, $productName) {
        $licenses = $this->getLicenses($siteID, $productName);
        $result = result(true);
        foreach ($licenses as $license) {
            $check = $this->license->requireLicense($license);
            if (!$check['status']) {
                $result = $check;
                break;
            }
        }

        return $result;
    }

    /**
     * 
     * @param type $siteID
     * @param type $module 
     * @return type
     */
    function requireLicenseModule($siteID, $module) {
        $licenses = $this->getLicenses($siteID);
        $result = result(false);
        foreach ($licenses as $license) {
            $check = $this->license->requireLicenseModule($license, $module);
            if ($check['status']) {
                $result = $check;
                break;
            }
        }
        return $result;
    }

    /**
     * check is privilege fullcontrol
     * @param type $user
     * @return true, false
     */
    function isFullControl($user = null) {
        $user = $user ?: $this->user;

        return in_array(static::PRIV_FULL_CONTROL, $this->getAllUserPrivs($user));
    }

    /**
     * Kiểm tra user có thuộc danh sách site được truy cập?
     * @param string $siteID
     * @param type $user
     * @throws type
     */
    function requireSite($siteID = null, $user = null) {
        if(!$siteID)
            $siteID = $this->siteID;
        if(!$siteID)
            throw new \Exception("siteID must be passed in param or use setSiteID method");
        $user = $user ?: $this->getUser();
        $session = Session::getInstance();
        //check siteID có trong hệ thống
        $cache = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
        $site = unserialize($cache->get("site/$siteID"));

        if (!$site) {
            $site = \Company\Site\Model\SiteMapper::makeInstance()->filterID($siteID)->getEntity();

            $cache->set("site/$siteID", serialize($site), 3600);
        }

        if (!$site->id) {
            throw new Ex\ForbiddenException("not found site in system");
        }

        $arrSiteID = $this->getAllSite($user);

        if (!$this->isFullControl() && !in_array($siteID, $arrSiteID)) {
            $ex = ini_get('display_errors') ? new Ex\ForbiddenException("siteID don't allow: $siteID") : new Ex\ForbiddenException();
            throw new $ex;
        }
        return $this;
    }
    
    public function getAllSite($user = null) {
        $user = $user ?: $this->getUser();
        $session = Session::getInstance();
        if (empty($session->get('arrSiteID'))) {
            $arrSiteID = UserMapper::makeInstance()->getSitesID($user->id);
            $session->set('arrSiteID', $arrSiteID);
        }
        return $session->get('arrSiteID')??[];
    }

    function setSiteID($siteID) {
        $this->siteID = $siteID;
        return $this;
    }

    /**
     * Kiểm tra NSD có quyền không
     * @param string $priv
     * @param \Company\SQL\Entity $user
     * @return bool
     */
    function hasPrivilege($priv, $user = null) {
        if(!$user)
            $user = $this->getUser();
        return in_array(static::PRIV_FULL_CONTROL, $this->getAllUserPrivs($user)) || in_array($priv, $this->getAllUserPrivs($user));
    }

    /**
     * Nếu không có quyền sẽ throw Exception
     * @param string $priv
     * @param \Company\SQL\Entity $user
     */
    function requirePrivilege($priv, $user = null) {
        $user = $user ?: $this->getUser();

        $this->requireLogin($user);

        if (!$this->hasPrivilege($priv, $user)) {

            $ex = ini_get('display_errors') ? new Ex\ForbiddenException("require privilege: $priv") : new Ex\ForbiddenException();
            throw new $ex;
        }
    }

    function hasRole($roleID, $user = null) {
        $user = $user ?: $this->getUser();
        if (isset($user->roles)) {
            foreach ($user->roles as $role) {
                if ($role->id == $roleID) {
                    return true;
                }
            }
        }

        if (in_array(static::PRIV_FULL_CONTROL, $this->getAllUserPrivs($user))) {
            return true;
        }
        return false;
    }

    function requireRole($roleID, $user = null) {
        $this->requireLogin($user);
        if (!$this->hasRole($roleID, $user)) {
            $ex = ini_get('display_errors') ? new Ex\ForbiddenException("require role: $roleID") : new Ex\ForbiddenException();
            throw new $ex;
        }
    }

    /**
     * Trả về tất cả quyền của user và group
     * @param type $user
     * @return type
     */
    function getAllUserPrivs($user = null) {
        if (empty($user)) {
            return [];
        }
        $user = $user ?: $this->getUser();
        $privs = $user->privileges;
        if (!in_array('fullcontrol', $privs)) {
            $user = UserMapper::makeInstance()
                    ->filterUserLinkID($user->id)
                    ->filterSiteFK($this->siteID)
                    ->filterActive()
                    ->setLoadRoles()
                    ->setLoadPrivileges()
                    ->getEntity();
        }
        if (isset($user->allPrivs)) {
            return $user->allPrivs;
        }

        if (empty($user) || !isset($user->privileges)) {
            return [];
        }

        $user->allPrivs = isset($user->privileges) ? $user->privileges : [];
        if (isset($user->roles)) {
            foreach ($user->roles as $role) {
                if (is_array($role))
                    $privileges = $role["privileges"];
                else
                    $privileges = $role->privileges;

                foreach ($privileges as $priv) {
                    if (!in_array($priv, $user->allPrivs)) {
                        $user->allPrivs[] = $priv;
                    }
                }
            }
        }

        return $user->allPrivs;
    }

    function getDefaultRole($user = null) {
        $user = $user ? $user : $this->getUser();
        $dfRole = new \stdClass(['id' => 0]);
        if (isset($user->roles)) {
            $exist = false;
            foreach ($user->roles as $role) {
                if ($role && isset($role->default) && $role->default) {
                    $exist = true;
                    $dfRole = $role;
                    break;
                }
            }

            if (!$exist && count($user->roles)) {
                $dfRole = $user->roles[0];
            }
        }

        return $dfRole;
    }

    function checkLocalIP() {
        $ra = new RemoteAddress();
        $ra->setUseProxy();
        $cache = CacheDriver::getInstance(CacheDriver::PRIVATE_MEMORY_CACHE);

        $localIPSetting = unserialize($cache->get("localIPSetting"));
        if (!$localIPSetting) {
            $localIPSetting = SettingMapper::makeInstance()->filterID("localIP")->getRow();
            $localIPSetting = json_decode($localIPSetting["value"], true);
            $cache->set("localIPSetting", serialize($localIPSetting), 60);
        }

        if ($localIPSetting["localIP"])
            $localIPs = explode(",", $localIPSetting["localIP"]);
        else
            $localIPs = [];

        if (arrData($localIPSetting, "autoCreateIP") == 1)
            $cache->delete("localIPSetting");

        foreach ($localIPs as $localIP)
            if (strpos($ra->getIpAddress(), $localIP) !== false)
                return true;

        if (arrData($localIPSetting, "autoCreateIP") == 1) {
            $localIPs[] = $ra->getIpAddress();
            SettingMapper::makeInstance()->filterID("localIP")->update([
                "value" => json_encode([
                    "localIP" => implode(",", $localIPs),
                    "autoCreateIP" => 1
                ])
            ]);
            // autoCreateIP thi ko dung cache de tranh conflict data

            return true;
        }

        return false;
    }

    function verifyCrsfToken() {
        return isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }

    static function generateCrsfToken() {
        if (!isset($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }
    }

    static function verifyBearerToken() {
        $bearer = $_SERVER["HTTP_AUTHORIZATION"];
        $secretKey = env("SECRET_KEY");

        return JWT::decode(arrData(explode(" ", $bearer) ,1, ""), new Key($secretKey, "HS256"));
    }
}
