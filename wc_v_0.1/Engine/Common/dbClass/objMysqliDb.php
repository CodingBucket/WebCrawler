<?php

require_once(__DIR__ . '/objMysqliDb_config.php');
require_once(__DIR__ . '/MysqliDb.php');

class objMysqliDb {
    protected $dbConfig = array();
    protected $db = false;
    protected $pk = '';
    protected $table = '';
    protected $currentOffset = 1;
    protected $currentPage = 1;
    protected $pageLimit = 0;
    protected $count = 0;
    protected $rowCount = 0;
    protected $totalCount = 0;
    protected $pagination = array(
        'limit' => 0,
        'offset' => 0,
        'count' => 0,
        'total_count' => 0,
        'page' => 0,
        'total_page' => 0,
        'prev_page' => 0,
        'next_page' => 0,
        'first_page' => 0,
        'last_page' => 0,
    );

    public function __construct($dbConfig = array()) {
        global $objMysqliDb_config;
        if(empty($dbConfig)){
            $dbConfig = $objMysqliDb_config['database_default'];
        }
        if(is_string($dbConfig) && isset($objMysqliDb_config['databases'][$dbConfig])){
            $this->dbConfig = $objMysqliDb_config['databases'][$dbConfig];
        } else if(is_array($dbConfig)){
            $this->dbConfig = $dbConfig;
        }
        if($this->dbConfig){
            $this->db = new MysqliDb(
                    array(
                'host' => isset($this->dbConfig['host'])?$this->dbConfig['host']:'localhost',
                'username' => isset($this->dbConfig['username'])?$this->dbConfig['username']:'',
                'password' => isset($this->dbConfig['password'])?$this->dbConfig['password']:'',
                'db' => isset($this->dbConfig['db'])?$this->dbConfig['db']:'',
                'port' => isset($this->dbConfig['port'])?$this->dbConfig['port']:3306,
                'prefix' => isset($this->dbConfig['prefix'])?$this->dbConfig['prefix']:'',
                'charset' => isset($this->dbConfig['charset'])?$this->dbConfig['charset']:'utf8'
                    )
            );
        }
    }

    public function column($column_name = '', $table = '') {
        $isColumn = true;

        if (strpos($column_name, '(') !== false || strpos($column_name, ')') !== false || strpos($column_name, '+') !== false) {
            $isColumn = false;
        }

        if ($isColumn && $column_name && strpos($column_name, '.') === false) {
            $table = trim($table);
            if (empty($table) && $this->table) {
                $table = $this->table;
            }
            if ($table) {
                $column_name = $table . '`.`' . $column_name;
            }
        }

        if ($isColumn) {
            $column_name = '`' . $column_name . '`';
        }

        return $column_name;
    }
    
    public function getLastError($param){
        return $this->db->getLastError();
    }
    
    public function getLastInsertId(){
        return $this->db->getInsertId();
    }

    public function insert($insertData = array(),$table = '') {
        if (isset($this->pk) && $this->pk && isset($insertData[$this->pk])) {
            unset($insertData[$this->pk]);
        }
        if(empty($table)){
            $table = $this->table;
        }
        return $this->db->insert($table, $insertData);
    }

    public function update($updateData = array(), $conditions = array(), $table = '') {
        if (!is_array($conditions) || empty($conditions)) {
            $conditions = array();
        }
        
        if (isset($this->pk) && $this->pk && isset($updateData[$this->pk])) {
            if ($updateData[$this->pk]) {
                $conditions[$this->pk] = $updateData[$this->pk];
            }
            unset($updateData[$this->pk]);
        }

        if ($conditions) {
            $conditions = $this->buildConditions($conditions);
        }
        if(empty($table)){
            $table = $this->table;
        }
        return $this->db->update($table, $updateData);
    }

    public function updateByPk($pk = 0, $updateData = array(), $conditions = array()) {
        if (!is_array($conditions) || empty($conditions)) {
            $conditions = array();
        }
        $conditions[$this->pk] = $pk;
        return $this->update($updateData, $conditions);
    }

    public function save($saveData = array(), $conditions = array(), $table='') {
        if ((isset($saveData[$this->pk]) && $saveData[$this->pk]) || $conditions) {
            return $this->update($saveData, $conditions, $table);
        } else {
            return $this->insert($saveData, $table);
        }
    }

    public function delete($conditions = array(), $table = '') {
        if ($conditions) {
            $conditions = $this->buildConditions($conditions);
            if(empty($table)){
                $table = $this->table;
            }
            return $this->db->delete($table);
        }
        return false;
    }
    
    public function query($queryStr = '',$bindParams = null) {
        return $this->db->rawQuery($queryStr, $bindParams);
    }

    public function buildConditions($conditions = array()) {
        if ($conditions) {
            if(is_array($conditions)){
                foreach ($conditions as $k => $v) {
                    if (is_array($v)) {
                        $cond_func_whereProp = isset($v[0]) ? $this->column($v[0]) : '';
                        $cond_func_whereValue = isset($v[1]) ? $v[1] : 'DBNULL';
                        $cond_func_operator = isset($v[2]) ? $v[2] : '=';
                        $cond_func_cond = isset($v[3]) ? $v[3] : 'AND';
                        $this->db->where($cond_func_whereProp, $cond_func_whereValue, $cond_func_operator, $cond_func_cond);
                    } else if (is_numeric($k)) {
                        $this->db->where($v);
                    } else {
                        $k = $this->column($k);
                        $this->db->where($k, $v);
                    }
                }
            } else if(is_string($conditions)){
                $this->db->where($conditions);
            }
        }
    }

    public function buildOrder($order = array()) {
        if ($order) {
            if (is_string($order)) {
                $order = array($order);
            }
            foreach ($order as $v) {
                if ($v && isset($v[0]) && $v[0]) {
                    $orderByField = $v[0];
                    $orderbyDirection = isset($v[1]) ? $v[1] : 'DESC';
                    $customFields = isset($v[2]) ? $v[2] : null;
                    $this->db->orderBy($orderByField, $orderbyDirection, $customFields);
                }
            }
        }
    }

    public function buildJoin($join = array()) {
        if (is_array($join) && $join) {
            foreach ($join as $v) {
                if ($v && isset($v[0]) && $v[0]) {
                    $joinTable = isset($v[0]) && $v[0]? $v[0] : '';
                    $joinCondition = isset($v[1]) && $v[1]? $v[1] : '';
                    $joinType = isset($v[2]) && $v[2]? $v[2] : "LEFT";
                    
                    if($joinTable && $joinCondition){
                        $this->db->join($joinTable, $joinCondition, $joinType);
                    }
                }
            }
        }
    }

    public function buildGroup($group = array()) {
        if($group){
            if (is_array($group) && $group) {
                foreach ($group as $v) {
                    $this->db->groupBy($v);
                }
            } else {
                $this->db->groupBy($group);
            }
        }
    }

    public function count($conditions = array()) {
        if(!is_array($conditions) || empty($conditions)){
            $conditions = array();
        }
        
        $params = array();
        if($conditions){
            $params['conditions'] = $conditions;
        }

        return $this->find('count', $params);
    }

    public function paginationData() {
        $paginationData = array(
            'limit' => $this->pageLimit,
            'offset' => $this->currentOffset,
            'count' => $this->rowCount,
            'total_count' => $this->totalCount,
            'page' => $this->currentPage,
            'total_page' => 0,
            'prev_page' => 0,
            'next_page' => 0,
            'first_page' => 0,
            'last_page' => 0,
        );
        $paginationData['total_page'] = ceil($paginationData['count'] / $paginationData['limit']);
        $paginationData['first_page'] = 1;
        $paginationData['last_page'] = $paginationData['total_page'];
        $paginationData['prev_page'] = $paginationData['page'] - 1;
        if ($paginationData['prev_page'] < $paginationData['first_page']) {
            $paginationData['prev_page'] = 0;
        }
        $paginationData['next_page'] = $paginationData['page'] + 1;
        if ($paginationData['next_page'] > $paginationData['last_page']) {
            $paginationData['next_page'] = 0;
        }
        return $paginationData;
    }

    public function find($by = '',$params = array(), $table = '') {
        $results = false;
        
        if(empty($table)){
            $table = $this->table;
        }
        if ($table) {
            $conditions = isset($params['conditions']) && $params['conditions']?$params['conditions']:array();
            $order = isset($params['order']) && $params['order']?$params['order']:array();
            $join = isset($params['join']) && $params['join']?$params['join']:array();
            $group = isset($params['group']) && $params['group']?$params['group']:array();
            
            $columns = isset($params['columns']) && $params['columns']?$params['columns']:'*';
            $limit = isset($params['limit']) && $params['limit']?$params['limit']:0;
            $page = isset($params['page']) && $params['page']?$params['page']:0;
            
            if (empty($columns)) {
                $columns = '*';
            }
            if ($conditions) {
                $conditions = $this->buildConditions($conditions);
            }
            if ($order) {
                $order = $this->buildOrder($order);
            }
            if ($join) {
                $join = $this->buildJoin($join);
            }
            if ($group) {
                $group = $this->buildGroup($group);
            }
            $numRows = null;
            if ($limit > 0) {
                $page = (int) $page;
                if (empty($page)) {
                    $page = 1;
                }
                $offset = $limit * ($page - 1);
                if ($limit || $offset) {
                    if ($limit && $offset) {
                        $numRows = array($offset, $limit);
                    } else if ($limit) {
                        $numRows = $limit;
                    }
                }
                $this->currentOffset = $offset;
                $this->currentPage = $page;
                $this->pageLimit = $limit;
                
                if($by == 'all'){
                    $by = 'limit';
                }
            }
            
            switch ($by) {
                case 'value':
                    if(is_array($columns)){
                        foreach($columns as $column){
                            $columns = $column;
                            break;
                        }
                    }
                    $results = $this->db->getValue($table, $columns);
                    break;
                
                case 'first':
                    $results = $this->db->getOne($table, $columns);
                    break;
                
                case 'count':
                    $count = $this->db->getValue($table, "count(*)");
                    $results = (int) $count;
                    break;
                
                case 'limit':
                    $results = $this->db->withTotalCount()->get($table, $numRows, $columns);
                    $this->rowCount = $this->db->count;
                    $this->totalCount = $this->db->totalCount;
                    break;

                case 'all':
                    $results = $this->db->get($table, $numRows, $columns);
                default:
                    break;
            }
        }
        return $results;
    }

}
