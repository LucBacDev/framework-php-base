<?php

namespace Company\Auth;

use Company\Exception as Ex;

class TestCtrl extends \Company\MVC\Controller {

    /**
     * Biến mock giả dữ liệu để test
     * @var type 
     */
    protected $user;

    function __construct(\Company\MVC\MvcContext $context) {
        parent::__construct($context);
        $this->user = new \Company\SQL\Entity([
            'name' => 'Test',
            'id' => 'test',
            'privileges' => ['priv1', 'priv2'],
            'roles' => [
                new \Company\SQL\Entity([
                    'id' => 'role1',
                    'name' => 'role1',
                    'privileges' => ['priv3']
                        ])
            ],
        ]);

        $this->admin = new \Company\SQL\Entity([
            'name' => 'Test',
            'id' => 'test',
            'privileges' => [Auth::PRIV_FULL_CONTROL]
        ]);
        
        $this->admin2 = new \Company\SQL\Entity([
            'name' => 'Test',
            'id' => 'test',
            'privileges' => [],
            'roles' => [
                new \Company\SQL\Entity([
                    'id' => 'role1',
                    'name' => 'role1',
                    'privileges' => ['fullcontrol']
                        ])
            ],
        ]);
    }

    function test() {
        $auth = Auth::getInstance();
        //expect noexception
        $auth->requireAdmin($this->admin);
        $auth->requireLogin($this->user);
        $auth->requirePrivilege('priv1', $this->user);
        $auth->requirePrivilege('priv3', $this->user);
        $auth->requirePrivilege('priv3', $this->admin);
        $auth->requirePrivilege('priv3', $this->admin2);
        $auth->requireRole('role1', $this->user);
        $auth->requireRole('role1', $this->admin);


        //excpect throw exception
        try {
            $auth->requirePrivilege('not-exists', $this->user);
            $auth->requireRole('not-exists', $this->user);

            throw new \Exception("expect these function throw exception, nothing happened");
        } catch (Ex\ForbiddenException $ex) {
            //if throw, everthing is ok
        }

        $this->resp->setBody(json_encode(result(true)));
    }

}
