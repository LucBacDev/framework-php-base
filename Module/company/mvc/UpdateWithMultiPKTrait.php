<?php

namespace Company\MVC;

trait UpdateWithMultiPKTrait
{
    /**
     * Limit default=1 để tránh update toàn bộ bảng
     * @param array $updateData
     * @return int so ban ghi duoc update
     */
    function update($updateData) {
        //tránh việc update nhầm toàn bộ bảng
        if (empty($this->join) && empty($this->where)) {
            throw new \Exception("Update all rows is forbidden");
        }

        $where = implode(" AND ", $this->where);

        return $this->db->update($this->tableName(), $updateData, $where, $this->getAllParams());
    }
}