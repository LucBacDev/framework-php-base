<?php


namespace Company\Service\Model;


use Company\Exception\BadRequestException;

class ProcessMapper extends \Company\SQL\Mapper
{

    const STATUS_RUNNING = "RUNNING";
    const STATUS_STOPPED = "STOPPED";
    function tableName()
    {
        // TODO: Implement tableName() method.
        return "system_process";
    }

    function tableAlias()
    {
        // TODO: Implement tableAlias() method.
        return "system_process";
    }

    function updateProcess($processID, $input) {
        // if update
        if ($processID) {

            $this->makeInstance()->filterID($processID)->existsOrFail();
            $this->startTrans();
            $this->makeInstance()->filterID($processID)->update($input);
            $this->completeTransOrFail();

        } else {

            $this->makeInstance()->filterID($processID)->existsThenFail();
            $this->startTrans();

            $input["id"] = uid();
            if (empty($input["status"])){
                $input["status"] = self::STATUS_STOPPED;
            }
            //validate required
            $required = ['serviceID', 'contactPointID'];
            foreach ($required as $field) {
                if (!strlen(trim($input[$field]))) {
                    throw new BadRequestException("Missing required field: " . $field);
                }
            }

            $this->insert($input);
            $this->completeTransOrFail();
        }

    }

    function getProcess($processID) {
        return $this->makeInstance()
            ->filterID($processID)
            ->getEntityOrFail();
    }

    function getAllProcess($serviceID) {
        $mapper = $this->makeInstance();
        if ($serviceID)
            $mapper->filterServiceID($serviceID);
        return $mapper->getEntities()->toArray();

    }

    function deleteProcess($processID) {
        $this->makeInstance()->filterID($processID)->existsOrFail();
        $this->startTrans();
        $this->filterID($processID)->delete();
        $this->completeTransOrFail();
    }

    function filterServiceID($serviceID) {
        $this->where("serviceID=?", __FUNCTION__)->setParamWhere($serviceID, __FUNCTION__);
        return $this;
    }
}