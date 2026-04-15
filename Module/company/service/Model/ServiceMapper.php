<?php


namespace Company\Service\Model;

use Company\Exception\BadRequestException;

class ServiceMapper extends \Company\SQL\Mapper
{

    const CONTAINER_SERVICE_HASH_KEY = "CONTAINER_SERVICES";
    const SERVICE_CONTROLLER_HASH_KEY = "SERVICE_CONTROLLER";
    const MASTER_NODE_HASH_KEY = "MASTER_NODE";

    function tableName()
    {
        // TODO: Implement tableName() method.
        return "system_service";
    }

    function tableAlias()
    {
        // TODO: Implement tableAlias() method.
        return "system_service";
    }

    function updateService($serviceID, $input) {
        // if update
        if ($serviceID) {

            $this->makeInstance()->filterID($serviceID)->existsOrFail();
            $this->startTrans();
            $this->makeInstance()->filterID($serviceID)->update($input);
            $this->completeTransOrFail();

        } else {

            //validate required
            $required = ['id', 'name', 'command'];
            foreach ($required as $field) {
                if (!strlen(trim($input[$field]))) {
                    throw new BadRequestException("Missing required field: " . $field);
                }
            }
            $this->startTrans();
            $this->insert($input);
            $this->completeTransOrFail();
        }

    }

    function getService($serviceID) {
        return $this->makeInstance()
            ->filterID($serviceID)
            ->getRow();
    }

    function getAllService() {
        return $this->makeInstance()
            ->getAll()->toArray();

    }

    function deleteService($serviceID) {
        $this->makeInstance()->filterID($serviceID)->existsOrFail();
        $this->startTrans();
        $this->filterID($serviceID)->delete();
        $this->completeTransOrFail();
    }
}