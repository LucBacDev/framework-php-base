<?php


namespace Company\SystemMonitoring\Model;

use Company\Exception\BadRequestException;

class SystemMonitoringMapper extends \Company\SQL\Mapper
{

    function tableName()
    {
        // TODO: Implement tableName() method.
        return "system_monitoring";
    }

    function tableAlias()
    {
        // TODO: Implement tableAlias() method.
        return "system_monitoring";
    }

    function updateModule($id, $input) {
        // if update
        if ($id) {

            $this->makeInstance()->filterID($id)->existsOrFail();
            $this->startTrans();
            $this->makeInstance()->filterID($id)->update($input);

        } else {

            //validate required
            $required = ['id', 'name'];
            foreach ($required as $field) {
                if (!strlen(trim($input[$field]))) {
                    throw new BadRequestException("Missing required field: " . $field);
                }
            }
            $this->startTrans();
            $this->insert($input);
        }
        $this->completeTransOrFail();

    }

    function getModule($id) {
        return $this->makeInstance()
            ->filterID($id)
            ->getRow();
    }

    function getAllModule() {
        return $this->makeInstance()
            ->getAll()->toArray();

    }

    function deleteModule($id) {
        $this->makeInstance()->filterID($id)->existsOrFail();
        $this->startTrans();
        $this->filterID($id)->delete();
        $this->completeTransOrFail();
    }
}