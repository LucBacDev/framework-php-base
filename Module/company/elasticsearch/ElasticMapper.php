<?php

namespace Company\ElasticSearch;

use Company\Entity;
use Company\Queue\QueueConsumer;
use Company\Queue\QueueProducer;
use Company\Zone\Model\ZoneMapper;
use Elasticsearch\Client;

class ElasticMapper {

    const METHOD_INSERT = "INSERT";
    const METHOD_UPDATE = "UPDATE";
    const METHOD_PARTIAL_UPDATE = "PARTIAL_UPDATE";
    const METHOD_SCRIPT_UPDATE = "SCRIPT_UPDATE";
    const METHOD_DELETE = "DELETE";
    const METHOD_UPDATE_BY_QUERY = "UPDATE_BY_QUERY";
    const METHOD_DELETE_BY_QUERY = "DELETE_BY_QUERY";

    /**
     * @var Client
     */
    protected $conn;
    protected $index;
    protected $type = '_doc';
    protected $query = [];
    protected $mustNotQuery = [];
    protected $sort = [];
    protected $agg = [];
    protected $range = [];
    protected $terms = [];
    protected $select;
    protected $limit = 10000;
    protected $offset;
    protected $pageSize = 20;
    protected $pageNo;
    protected $fuzzy;
    protected $totalRecord = 0;
    protected $scrollID = '';
    protected $searchAfter = null;
    protected $queryString = null;
    protected $multimatch = null;
    protected $operators = [];
    protected $keyPartition = null;
    protected $execImmediately = false;
    protected $batchSize = 20;
    protected $collapse = null;
    protected $refresh = false;

    /**
     * @var type kết quả query, cache giúp tiết kiệm thời gian
     */
    protected $result;

    function getConn() {
        return DB::getInstance();
    }

    function __construct() {
        $this->conn = $this->getConn();
    }

    static function makeInstance() {
        return new static;
    }

    function makeEnity($rawData) {
        return new Entity\Entity($rawData);
    }

    function from($index) {
        $this->index = $index;
        return $this;
    }

    function execImmediately($value = true) {
        $this->execImmediately = $value;
        return $this;
    }

    function setBatchSize($batchSize) {
        $this->batchSize = $batchSize;
        return $this;
    }

    function setRefresh($refresh) {
        $this->refresh = $refresh;
        return $this;
    }

    function collapse($field) {
        $this->collapse = $field;
        return $this;
    }

    function filterID($id) {
        $this->where('id', $id);
        return $this;
    }

    function get($id) {
        return $this->conn->get([
            'index' => $this->index,
            'id' => $id,
            'type' => $this->type
        ]);
    }

    /**
     *
     * @param int $limit nếu tính sum, avg thì limit = 0
     * @param int $offset
     * @return $this
     */
    function limit($limit, $offset = 0) {
        if ($limit !== null) {
            $this->limit = (int) $limit;
        } else {
            $this->limit = null;
        }
        if ($offset) {
            $this->offset = (int) $offset;
        } else {
            $this->offset = 0;
        }

        return $this;
    }

    /**
     * Insert/update
     * Cần update đủ thông tin, không giống mysql có thể update các trường riêng lẻ
     * @param string $id
     * @param array $data
     */
    function insert($id, $data) {
        return $this->update($id, $data);
    }

    /**
     * Insert/update
     * Cần update đủ thông tin, không giống mysql có thể update các trường riêng lẻ
     * @param string $id
     * @param array $data
     */
    function update($id, $data) {

//        if (!is_object($data)) {
//            $data = json_decode(json_encode($data), true);
//        }

        if (!is_array($data)) {
            throw new \BadMethodCallException('$data must be array');
        }

        return $this->conn->index([
                    'index' => $this->index,
                    'type' => $this->type,
                    'id' => $id,
                    'body' => $data,
                    'refresh' => $this->refresh
        ]);
    }

    function partialUpdate($id, $dataToUpdate) {
        return $this->conn->update([
            'index' => $this->index,
            'id' => $id,
            'body' => [
                'doc' => $dataToUpdate
            ],
            'refresh' => $this->refresh
        ]);
    }

    function setKeyPartition($keyPartition) {
        $this->keyPartition = $keyPartition;
        return $this;
    }

    function buildUpdateQuery($params) {

        $inlineQuery = "";

        $query = $this->getQuery();

        foreach ($params as $field => $newValue) {
            if (is_string($newValue)) {
                $newValue = str_replace("'", "", $newValue);
                $newValue = "'$newValue'";
            } elseif (is_null($newValue))
                $newValue = "''";

            $inlineQuery .= "ctx._source.$field = $newValue;";
        }

        $query["conflicts"] = "proceed";
        $query["body"]["script"] = [
            "source" => $inlineQuery,
            "lang" => "painless",
        ];

        return $query;
    }

    /** Update multi fields by query
     * @param array $params
     */
    function updateByQuery($params) {

        $results = $this->getAll();
        $this->updateByIds($results["ids"], $results["results"], $params);
    }

    /** Update multi fields by query
     * @param array $dataToUpdate
     */
    function updateByQuery2($dataToUpdate) {
        $query = $this->buildUpdateQuery($dataToUpdate);
        $this->conn->updateByQuery($query);
    }

    function deleteByQuery() {
        $ids = $this->getAll(false)["ids"];

        $this->deleteByIds($ids);
    }

    function deleteByIds($ids) {
        $params = ['body' => []];
        $i = 1;
        foreach ($ids as $id) {

            $params['body'][] = [
                "delete" => [
                    "_index" => $this->indexName(),
                    "_id" => $id
                ]
            ];

            if ($i % $this->batchSize == 0) {
                $responses = $this->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                if ($responses["errors"] === false)
                    return false;
                // unset the bulk response when you are done to save memory
                unset($responses);
            }

        }

        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $responses = $this->bulk($params);
            if ($responses["errors"] === false)
                return false;
        }

        return true;
    }

    function updateByIds($ids, $data, $dataToUpdate) {
        $params = ['body' => []];

        for ($i=0; $i < count($ids); $i++) {

            $params['body'][] = [
                "index" => [
                    "_index" => $this->indexName(),
                    "_id" => $ids[$i]
                ]
            ];

            $params['body'][] = array_replace($data[$i], $dataToUpdate);

//            file_put_contents("update.txt", json_encode($params));
            if (($i+1) % $this->batchSize == 0) {
                $responses = $this->bulk($params);

                // erase the old bulk request
                $params = ['body' => []];

                // unset the bulk response when you are done to save memory
                if ($responses["errors"] === false)
                    return false;
                unset($responses);
            }

        }

        // Send the last batch if it exists
        if (!empty($params['body'])) {
            $responses = $this->bulk($params);
            if ($responses["errors"] === false)
                return false;
        }

        return true;
    }

    function bulk($params) {
        return $this->conn->bulk($params);
    }

    /**
     * @param string $index
     * @param string $method
     * @param array $data
     * @param string $id
     * @param array $query // Use with method METHOD_UPDATE_BY_QUERY or METHOD_DELETE_BY_QUERY
     * @param array $updateTimeKey
     */
    function buildElasticMessage($method, $data, $id, $query = null, $updateTimeKey = ["updated_time"]) {
        return [
            "index" => $this->index,
            "method" => $method,
            "data" => $data,
            "id" => $id,
            "query" => $query,
            "updatedTimeKey" => $updateTimeKey
        ];
    }

    function buildBulkParamsFromMessages($messages) {
        $params = ['body' => []];
        foreach ($messages as $message) {
            switch ($message["method"]) {
                case self::METHOD_DELETE:
                    $operation = "delete";
                    break;
                case self::METHOD_UPDATE:
                case self::METHOD_INSERT:
                    $operation = "index";
                    break;
                case self::METHOD_PARTIAL_UPDATE:
                case self::METHOD_SCRIPT_UPDATE:
                    $operation = "update";
                    break;
                default:
                    return ["errors" => true];
            }

            $params['body'][] = [
                $operation => [
                    '_index' => $message["index"],
                    '_id'    => $message["id"]
                ]
            ];

            if ($operation == "index")
                $params['body'][] = $message["data"];

            if ($message["method"] == self::METHOD_PARTIAL_UPDATE)
                $params['body'][] = ["doc" => $message["data"]];

            if ($message["method"] == self::METHOD_SCRIPT_UPDATE)
                $params['body'][] = ["script" => $message["data"]];
        }

        return $params;
    }

    function addQueue($message) {
        $key = uid();
        $keyPartition = $this->keyPartition ?? $key;
        $message["zoneID"] = ZoneMapper::getMasterZoneID();
        QueueProducer::makeInstance()->insertQueue("SYNC_ELASTIC", $keyPartition, $key, json_encode($message));
    }

    function handleMultiMessagess($messages, $force = false) {
        $failedMessages = [];

        foreach (array_column($messages, "Value") as $message) {
            $message = json_decode($message, true);
            $mapper = $this->from($message["index"]);
            $found = false;
            $instance = null;
            try {
                $instance = $mapper->get($message["id"])["_source"];
                $found = true;
            } catch (\Exception $ex){
                // khong tim thay record
            }
            switch ($message["method"]) {
                case self::METHOD_UPDATE:
                case self::METHOD_PARTIAL_UPDATE:
                case self::METHOD_SCRIPT_UPDATE:
                    if($force && !$found)
                        break;
                    $updated_time = arrData($message["data"], $message["updatedTimeKey"]);
                    if ($updated_time > arrData($instance, $message["updatedTimeKey"]))
                        $failedMessages[] = $message;
                    break;
                case self::METHOD_INSERT:
                    if (!$found)
                        $failedMessages[] = $message;
                    break;
                case self::METHOD_DELETE:
                    if ($found)
                        $failedMessages[] = $message;
                    break;
            }
        }

        if (count($failedMessages) == 0)
            return true;

        $params = $this->buildBulkParamsFromMessages($failedMessages);
//        var_dump("----------params----------");
//        var_dump($params);
//        var_dump("----------params----------");

        $response = $this->bulk($params);
//        var_dump($response);
        //return true;

        if($response["errors"] === false) {
            return true;
        }
        else{
            foreach ($response["items"] as $index) {
                $typeErr = arrData($index, ["update", "error" ,"type"]);
                if($typeErr == "cluster_block_exception")
                    return result(false, "cluster_block_exception",QueueConsumer::QUEUE_002);
            }

            return result(false, $response,QueueConsumer::QUEUE_001);
        }
    }

    function queryString($query) {
        $this->queryString = $query;
    }

    function multimatch($query) {
        $this->multimatch = $query;
    }

    /**
     * Xóa bản ghi
     * @param type $id
     */
    function delete($id) {
        return $this->conn->delete([
                    'index' => $this->index,
                    'type' => $this->type,
                    'id' => $id
        ]);
    }

    /**
     * Thêm điều kiện tìm kiếm
     * @param string $key
     * @param string|array $query https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_search_operations.html
     * @param string $operator
     */
    function where($key, $query, $operator = "match") {
        $this->query[] = [$key, $query];
        $this->operators[$key] = $operator;
        $this->result = null;

        return $this;
    }

    function exists($fieldName) {
        $this->where("field", $fieldName, "exists");
        return $this;
    }

    function whereMustNot($key, $query) {
        $this->mustNotQuery[] = [$key, $query];

        return $this;
    }

    /**
     * Tim kiem dieu kien theo khoang
     * @param string $key
     * @param string $greaterThanValue gia tri so sanh duoi
     * @param string $lowerThanValue gia tri so sanh tren
     * @return $this
     */
    function filterRange($key, $loweIndex = '', $highIndex = '', $greaterThanOption = "gte", $lessThanOption = "lte") {
        $highIndex = trim($highIndex);
        $loweIndex = trim($loweIndex);

        if (!$highIndex && !$loweIndex) {
            return $this;
        }
//        $this->range[$key] = [];
        if ($loweIndex) {
            $this->range[$key][$greaterThanOption] = $loweIndex;
        }

        if ($highIndex) {
            $this->range[$key][$lessThanOption] = $highIndex;
        }

        return $this;
    }

    /**
     * add terms (search theo array)
     * @param string $key
     * @param array|string $value
     * @return $this
     */
    function filterTerms($key, $value) {
        $this->terms[$key] = $value;
        return $this;
    }

    /**
     * Sinh query từ các điều kiện đã có
     */
    function getQuery() {
        $query = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => []
        ];

        if ($this->select)
            $query['body']['_source'] = $this->select;

        if (!empty($this->query)) {
            $query['body']['query']['bool']['must'] = [];

//            var_dump($this->operators);
            $boolArrMust = $boolArrShould = [];
            foreach ($this->query as $element) {
                list($field, $value) = $element;
                $boolArrMust['bool']['must'] = [];
                $boolArrShould['bool']['should'] = [];
                $boolData = [];
                //must filter
                if (!is_array($value)) {
                    $match_phrase = [$this->operators[$field] => [$field => $value]];
                    array_push($boolArrMust['bool']['must'], $match_phrase);
                    $boolData = $boolArrMust;
                } else {
                    foreach ($value as $val) {
//                      $match_phrase = ['match_phrase_prefix' => [$field => $value]];
                        $match_phrase = ['match' => [$field => $val]];
                        array_push($boolArrShould['bool']['should'], $match_phrase);
                        $boolData = $boolArrShould;
                    }
                }
                array_push($query['body']['query']['bool']['must'], $boolData);
            }
        }

        if ($this->multimatch)
            $query['body']['query']['bool']['must'][] = [
                "multi_match" => [
                    "query" => $this->multimatch
                ]
            ];

        if (!empty($this->mustNotQuery)) {
            $query['body']['query']['bool']['must_not'] = [];

            foreach ($this->mustNotQuery as $value) {
                $op = "match";
                list($field, $val) = $value;

                $query['body']['query']['bool']['must_not'][] = [
                    $op => [
                      $field => $val
                    ]
                ];
            }
        }

        if (!empty($this->range)) {
            $query['body']['query']['bool']['filter'] = [];
            $boolArrMust = [];
            foreach ($this->range as $field => $arrCompareValue) {
                $match_range = ['range' => [$field => $arrCompareValue]];

                $filterBoolMustElement = ['bool' => ['must' => $match_range]];
                array_push($query['body']['query']['bool']['filter'], $filterBoolMustElement);
            }
        }

        if (!empty($this->terms)) {
            if (!isset($query['body']['query']['bool']['filter']))
                $query['body']['query']['bool']['filter'] = [];

            foreach ($this->terms as $key => $value) {

                $match_terms = [
                    "terms" => [
                        $key => (array) $value
                    ]
                ];

                $filterBoolMustElement = ['bool' => ['must' => $match_terms]];

                array_push($query['body']['query']['bool']['filter'], $filterBoolMustElement);
            }
        }

        if ($this->queryString)
            $query['body']["query"]["query_string"] = [
                "query" => $this->queryString
            ];

        if ($this->searchAfter) {
            $query['body']["search_after"] = $this->searchAfter;
        }

        if ($this->limit !== null) {
            $query['body']['size'] = $this->limit;
        }
        if ($this->offset) {
            $query['body']['from'] = (int) $this->offset;
        }

        if ($this->collapse)
            $query['body']['collapse'] = ["field" => $this->collapse];

        if (!empty($this->sort)) {
            $query['body']['sort'] = [];
            foreach ($this->sort as $key => $value) {
                $query['body']['sort'][$key] = ["order" => $value];
            }
        }


        if (!empty($this->agg)) {

        }

        if ($this->scroll) {
            //scroll api
            $query['scroll'] = $this->scroll;
            $query['size'] = $this->limit;

            unset($query['body']['size']);
        }

        if ($this->fuzzy)
            $query['body']['query']['fuzzy'] = $this->fuzzy;

//        echo json_encode($query);die();
//        if ($this->index == "pacs_instance") {echo json_encode($query);die();};
        return $query;
    }

    /**
     * Không xử lý kết quả của Elastic, format lay theo dinh dang dung duoc
     * @return array
     */
    function search() {
        if (!$this->result) {
            $this->result = $this->conn->search($this->getQuery());
        }

        return $this->result;
    }

    protected $scroll = false;

    /**
     * @param $val scroll time
     * @return $this
     */
    function setScroll($val = false) {
        $this->scroll = $val;
        return $this;
    }

    /**
     * get scroll data
     * @param string $scrollID
     * @return array
     */

    function scroll($scrollID) {
        $scrollData = $this->conn->scroll([
                "scroll_id" => $scrollID, //...using our previously obtained _scroll_id
                "scroll" => $this->scroll
            ]
        );

        $hits = arrData($scrollData, 'hits', []);
        $hits = arrData($hits, 'hits', []);

        $result['scrollID'] = arrData($scrollData, '_scroll_id', '');
        $result['results'] = array_column($hits, "_source");;

        return $result;
    }

    function searchAfter($searchAfter) {
        $this->searchAfter = $searchAfter;
        return $this;
    }

    function fuzzy($field, $value) {
        $this->fuzzy = [
            $field => [
                "value" => $value
            ]
        ];
        return $this;
    }

    /**
     * @param type $count tổng số bản ghi đưa vào count
     * @return $this
     */
    function count(&$count) {
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => [
                "query" => $this->getQuery()["body"]["query"]
            ]
        ];
        $count = (int) arrData($this->conn->count($params), "count");
        return $this;
    }

    function countByQuery($termKeywordField, $termSize = 10000) {
        $query = $this->getQuery();

        $query["body"]["size"] = 0;
        $query["body"]["aggs"] = [
            "_count" => [
                "terms" => [
                    "field" => $termKeywordField,
                    "size" => $termSize
                ]
            ]
        ];

        return $this->conn->search($query)["aggregations"]["_count"]["buckets"];
    }

    function getScrollID() {
        return $this->scrollID;
    }

    function getAll($withSource = true, $getHits = false, $getLastSort = false) {
        $result = [];
        $dataSet = $this->search();
        $hits = arrData($dataSet, 'hits', []);
        $hits = arrData($hits, 'hits', []);

        if ($withSource)
            $result['results'] = array_column($hits, "_source");

        if ($getHits)
            $result['hits'] = $hits;
        $result['scrollID'] = arrData($dataSet, '_scroll_id', '');
        $result['ids'] = array_column($hits, "_id");
        $result['lastSort'] = arrData($hits, [count($hits)-1, "sort"]);

        return $result;
    }

    function getEntities($callback = null, &$total = null) {
        $hits = arrData($this->search(), 'hits', []);
        $this->count($total);
        $hits = arrData($hits, 'hits', []);
        $ret = [];

        foreach ($hits as $row) {
            $row = $row['_source'];
            $entity = $this->makeEnity($row);
            if ($callback) {
                call_user_func($callback, $row, $entity);
            }
            $ret[] = $entity;
        }

        return $ret;
    }

    function getEntity($callback = null) {
        $row = $this->GetRow();
        $entity = $this->makeEnity($row);
        if ($callback) {
            call_user_func($callback, $row, $entity);
        }
        return $entity;
    }

    /**
     * Viết câu lệnh select giống sql
     * @param string $fields field1, field2, field3
     * @return $this
     */
    function select($fields) {
        $this->result = null;

        //tách thành mảng
        $fields = explode(',', $fields);
        $this->select = [];
        foreach ($fields as $field) {
            $this->select[] = trim($field);
        }
        return $this;
    }

    function getCol() {
        if (!$this->select) {
            throw new \Exception("must call method ::select before getCol");
        }

        $dataSet = $this->search();
        $hits = arrData($dataSet, 'hits', []);
        $hits = arrData($hits, 'hits', []);
        $ret = [];

        foreach ($hits as $row) {
            $row = $row['_source'];
            $ret[] = arrData($row, $this->select[0]);
        }

        $result['scrollID'] = arrData($dataSet, '_scroll_id', '');
        $result['results'] = $ret;
        return $result;
    }

    function getAssoc() {
        if (!$this->select) {
            throw new \Exception("must call method ::select before getAssoc");
        }
        if (count($this->select) < 2) {
            throw new \Exception("::select atleast 2 fields");
        }

        $hits = arrData($this->search(), 'hits', []);
        $hits = arrData($hits, 'hits', []);
        $ret = [];

        foreach ($hits as $row) {
            $row = $row['_source'];
            $ret[arrData($row, $this->select[0])] = arrData($row, $this->select[1]);
        }
        return $ret;
    }

    function getRow() {
        $hits = arrData($this->search(), 'hits', []);
        $hits = arrData($hits, 'hits', []);
        $row = $hits[0] ?? null;
        return arrData($row, '_source');
    }

    function getOne() {
        if (!$this->select) {
            throw new \Exception("must call method ::select before getOne");
        }

        $row = $this->getRow();
        if (!$row) {
            return null;
        }
        return arrData($row, $this->select[0]);
    }

//    function sort($cond, $key = null) {
//        if ($key) {
//            $this->sort[$key] = $cond;
//        } else {
//            $this->sort[] = $cond;
//        }
//
//        return $this;
//    }
//    

    /**
     * Sắp xếp
     * @param string $key
     * @param string $order
     * @return $this
     */
    function sort($key, $order = "asc") {
        if ($key) {
            $this->sort[$key] = $order;
        } else {
            $this->sort[] = $order;
        }
        $this->result = null;
        return $this;
    }

    /**
     * Các hàm tính sum, avg,..
     * https://www.elastic.co/guide/en/elasticsearch/reference/current/search-aggregations-metrics-avg-aggregation.html
     * @param string $name
     * @param array $function
     * @return $this
     */
    function agg($name, $function) {
        $this->result = null; //reset cache
        $this->agg[$name] = $function;
        return $this;
    }

    /**
     *
     * @param string $name giá trị của 1 biến, nếu không truyền lấy tất cả
     */
    function getAggs($name = null) {
        $result = [];
        $query = $this->getQuery();
        $query["body"]["size"] = 0;
        $query["body"]["aggs"] = $this->agg;
        $dataSet = $this->conn->search($query);
        $aggregations = arrData($dataSet, "aggregations");
        if ($name == null)
            return $aggregations;
        $value = arrData($aggregations, $name);
        return $value;
    }

    function setPage($pageNo, $pageSize = 500) {
        $pageSize = $pageSize ?: $this->pageSize;

        $this->pageSize = $pageSize;
        $this->pageNo = $pageNo;
        $offset = ($pageNo - 1) * $pageSize;

        $this->limit($pageSize, $offset);
        return $this;
    }
}
