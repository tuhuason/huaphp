<?php
/**
 * Mysql数据库
 * PDO驱动
 */
class db
{
    const SELECT = 1;

    const UPDATE = 2;

    const DELETE = 3;

    const INSERT = 4;

    private $table;

    private $where;

    private $fields = "*";

    private $order;

    private $group;

    private $values;

    private $bind = array();

    /**
     * @var Pdo
     */
    private static $inst;

    public function __construct($table)
    {
        $this->table = $table;
    }

    static function connect($config)
    {
        if (!self::$inst instanceof self) {
            self::$inst = new \PDO($config['dsn'], $config['db_user'], $config['db_pwd'], array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $config['db_encode'] . '\''
            ));
            return self::$inst;
        }
        return self::$inst;
    }

    static function insert($sql, $data = array())
    {
        self::query($sql, $data);
        return self::$inst->lastInsertId();
    }

    static function update($sql, $data = array())
    {
        $result = self::query($sql, $data);
        if ($result->errorCode() !== "00000") {
            return false;
        }
        return true;
    }

    static function select($sql, $data = array())
    {
        $result = self::query($sql, $data);
        return $result->fetch();
    }

    static function selectAll($sql, $data = array())
    {
        $result = self::query($sql, $data);
        return $result->fetchAll();
    }

    static function delete($sql, $data = array())
    {
        return self::query($sql, $data);
    }

    static function table($table, $sub = "")
    {
        if (!empty($sub)) {
            $table .= "," . $sub;
        }
        return new self($table);
    }

    public function values($values)
    {
        $this->values .= $values;
        return $this;
    }

    public function where($where, $data = array())
    {
        if (!empty($where)) {
            $this->where .= " WHERE " . $where;
            $this->bind = array_merge($this->bind, $data);
        }
        return $this;
    }

    public function fields($fields)
    {
        $this->fields = $fields;
        return $this;
    }

    public function order($order, $data = array())
    {
        $this->order = " ORDER BY " . $order;
        $this->bind = array_merge($this->bind, $data);
        return $this;
    }

    public function group($group, $data = array())
    {
        $this->group = " GROUP BY " . $group;
        $this->bind = array_merge($this->bind, $data);
        return $this;
    }

    public function done($type)
    {
        switch ($type) {
            case db::SELECT:
                return self::select("SELECT " . $this->fields . " FROM " . $this->table . $this->where . $this->order . $this->group, $this->bind);
                break;
            case db::INSERT:
                return self::insert("INSERT INTO " . $this->table . "(" . $this->fields . ") VALUES " . $this->values);
                break;
            case db::DELETE:
                return self::delete("DELETE FROM " . $this->table . $this->where);
                break;
            case db::UPDATE:
                return self::update("UPDATE " . $this->table . " SET " . $this->values . $this->where);
                break;
        }
        return false;
    }

    static function query($sql, $data = array())
    {
        $rst = self::$inst->prepare($sql);
        foreach ($data as $key => &$value) {
            $rst->bindParam($key, $value, is_numeric($value) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $rst->setFetchMode(\PDO::FETCH_ASSOC);
        $rst->execute();
        $errorInfo = $rst->errorInfo();
        if ($errorInfo[2]) {
            throw new \Exception($errorInfo[2],$errorInfo[1]);
        }
        return $rst;
    }

    public function count($per = 10, $page = 1)
    {
        $data = array(
            "page" => $page,
            "total" => 0,
            "totalPage" => 0,
            "perPage" => $per,
            "currentPage" => $page,
        );
        $countSql = "SELECT COUNT(1) AS Total FROM " . $this->table . $this->where . $this->order;
        $total = self::select($countSql, $this->bind);
        $data["total"] = $total["Total"];
        $data["totalPage"] = ceil($total["Total"] / $per);
        return $data;
    }

    public function paginate($per = 10, $page = 1)
    {
        $data = $this->count($per, $page);
        $data["list"] = array();
        $listSql = "SELECT " . $this->fields . " FROM " . $this->table . $this->where . $this->order . " LIMIT " . ($page - 1) * $per . "," . $per;
        $data["list"] = self::selectAll($listSql, $this->bind);
        return $data;
    }

    public function html($per = 10, $page = 1, $link = "")
    {
        $data = $this->paginate($per, $page);
        $data["firstPage"] = 1;
        $data["lastPage"] = 1;
        $data["nextPage"] = 1;
        $data["upPage"] = 1;
        $html = "";
        if ($data["total"] > 1) {
            // 上一页
            if ($data["currentPage"] > 1) {
                $data["upPage"] = $data["currentPage"] - 1;
                $html .= "<li><a class=\"pre-page\" href=\"?p=" . $data["upPage"] . $link . "\">上一页</a></li> ";
            }
            // 中间页码
            if ($data["totalPage"] > 1) {
                $begin = $data["page"] >= 10 ? $data["page"] - 4 : 1;
                $end = $data["totalPage"] - $begin >= 10 ? $begin + 9 : $data["totalPage"];
                for ($i = $begin; $i <= $end; $i++) {
                    if ($i == $data["currentPage"]) {
                        $html .= "<li class=\"active\"><a href=\"?p=" . $data["currentPage"] . "\">$i</a></li> ";
                    } else {
                        $html .= "<li><a href=\"?p=" . $i . $link . "\">$i</a></li> ";
                    }
                }
            }
            // 下一页
            if ($data["currentPage"] < $data["totalPage"]) {
                $data["nextPage"] = $data["currentPage"] + 1;
                $html .= "<li><a class=\"next-page\" href=\"?p=" . $data["nextPage"] . $link . "\">下一页</a></li> ";
            }
        }
        return array("list" => $data["list"], "html" => $html);
    }

    static function transaction(callable $transaction)
    {
        self::$inst->beginTransaction();
        $queue = $transaction();
        if ($queue) {
            self::$inst->commit();
            return $queue;
        }
        self::$inst->rollBack();
        return false;
    }
}