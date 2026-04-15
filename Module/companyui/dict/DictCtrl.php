<?php

namespace CompanyUI\Dict;

use Company\MVC\Layout;

class DictCtrl extends \Company\MVC\Controller {

    protected $layout;

    /**
     * @var Module 
     */
    protected $module;

    function init() {
        parent::init();
        $this->layout = Layout::getLayout('admin');
    }

    function DictCollectionList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('Dict.Collection.List');
    }

    function DictItemList($siteID) {
        $this->layout
                ->setSiteID($siteID)
                ->renderReact('Dict.Item.List');
    }

}
