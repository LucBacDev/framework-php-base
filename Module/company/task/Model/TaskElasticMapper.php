<?php

namespace Company\Task\Model;

use Company\ElasticSearch\ElasticMapper;

class TaskElasticMapper extends ElasticMapper {

    public function __construct() {
        parent::__construct();
        $this->from('task');
    }

    /**
     * Lọc theo site
     * @param string $siteID
     * @return $this
     */
    public function filterSiteFK($siteID) {
        $this->where('siteFK', $siteID);
        return $this;
    }

    /**
     * Lọc trạng thái xóa
     * @param int $deleted
     * @return $this
     */
    public function filterDeleted($deleted = 0) {
        $this->where('deleted', $deleted);
        return $this;
    }

    /**
     * Lọc theo trạng thái công việc
     * @param string $status
     * @return $this
     */
    public function filterStatus($status) {
        if ($status !== '') {
            $this->where('status', $status);
        }
        return $this;
    }

    /**
     * Lọc theo mức độ ưu tiên
     * @param string $priority
     * @return $this
     */
    public function filterPriority($priority) {
        if ($priority !== '') {
            $this->where('priority', $priority);
        }
        return $this;
    }

    /**
     * Tìm kiếm text (theo title)
     * @param string $search
     * @return $this
     */
    public function filterSearch($search) {
        if ($search !== '') {
            // Sử dụng wildcard hoặc match tùy vào yêu cầu của Elasticsearch
            $this->where('title', "*$search*", 'wildcard');
        }
        return $this;
    }
}
