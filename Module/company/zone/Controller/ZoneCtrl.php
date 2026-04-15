<?php

namespace Company\Zone\Controller;

use Company\Auth\Auth;
use Company\MVC\Controller;
use Company\MVC\MvcContext;
use Company\Zone\Model\ContactPointMapper;
use Company\Zone\Model\ZoneMapper;

class ZoneCtrl extends Controller{
    protected $auth;

    function __construct(MvcContext $context)
    {
        parent::__construct($context);
        $this->auth = Auth::getInstance();
    }

    function getZones() {
        $this->auth->requireLogin();
        $mapper = ZoneMapper::makeInstance();

        $filterName = arrData($_GET, 'name');
        if(arrData($_GET, 'name')) {
            $mapper->filterName($filterName);
        }
        $this->resp->setBody($mapper->getEntities()->toJson());
    }

    function getZone($id) {
        $this->auth->requireLogin();
        $zone = ZoneMapper::makeInstance()
            ->filterID($id)
            ->getEntityOrFail();
        $this->resp->setBody($zone->toJson());
    }

    function updateZone($id) {
        $this->auth->requireAdmin();
        $this->auth->requireSite('master');

        $zone = ZoneMapper::makeInstance()->updateZone($id, $this->input());

        $this->resp->setBody(json_encode(result(true, $zone)));
    }

    function deleteZone($id) {
        $this->auth->requireAdmin();
        $this->auth->requireSite('master');

        ZoneMapper::makeInstance()->deleteZone($id);
        $this->resp->setBody(json_encode(result(true)));
    }

    function getContactPoints() {
        $this->auth->requireLogin();

        $mapper = ContactPointMapper::makeInstance();
        $zone = $this->input('zone');
        $address = $this->input('address');

        if($zone)
            $mapper->filterZone($zone);
        if($address)
            $mapper->filterAddr($address);

        $this->resp->setBody($mapper->getEntities()->toJson());
    }

    function getContactPoint($id) {
        $this->auth->requireLogin();

        $resp = ContactPointMapper::makeInstance()
            ->filterID($id)
            ->getEntityOrFail()
            ->toJson();
        $this->resp->setBody($resp);
    }

    function insertContactPoint($id = null) {
        $this->auth->requireAdmin();
        $this->auth->requireSite('master');

        $entity = ContactPointMapper::makeInstance()->insertContactPoint($id, $this->input());
        $this->resp->setBody(result(true, $entity));
    }

    function deleteContactPoint($id) {
        $this->auth->requireAdmin();
        $this->auth->requireSite('master');

        ContactPointMapper::makeInstance()->deleteContactPoint($id);
        $this->resp->setBody(result(true));
    }

    function detectContactPoint() {
        $this->auth->requireLogin();
        $result = ContactPointMapper::makeInstance()->detectContactPoint();
        $this->resp->setBody($result);
    }

    function getHostName() {
        $this->outputJSON(result(true, gethostname()));
    }
}