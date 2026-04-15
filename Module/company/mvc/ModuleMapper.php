<?php

namespace Company\MVC;

class ModuleMapper extends \Company\SQL\Mapper {

    public function tableAlias() {
        return 'mdl';
    }

    public function tableName() {
        return 'system_module';
    }

}
