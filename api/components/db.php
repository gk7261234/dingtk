<?php

class db
{

    //内部日志输出
    private function logInfo($str)
    {
        if (gettype($str) == 'array') {
            $str = json_encode($str);
        }
        $file = __DIR__ . '/../../logs/app.log';
        $time = (string)date("Y-m-d H:i:s");
        file_put_contents($file, "[" . $time . "] [内部打印]:" . " " . $str . "\n", FILE_APPEND);
    }

    // Properties
//    private $dbhost = '192.168.1.22';
//    private $dbuser = 'root';
//    private $dbpass = 'root';
//    private $dbname = 'vc265';
//    private $dbh;
    private $dbhost,$dbuser,$dbpass,$dbname,$dbh;


    public function __construct($dbhost,$dbuser,$dbpass,$dbname)
    {
        $this->dbhost = $dbhost;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbname = $dbname;
    }

    /**
     * @return PDO
     */
    public function connect()
    {
        $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
        $setting = [
            PDO::ATTR_PERSISTENT => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "set names utf8",  //交互数据未utf8格式
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ];
        if (!isset($this->dbh)) {
            $this->dbh = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass, $setting);
        }
        return $this->dbh;
    }

    /**
     * 插入一条数据
     * @param $tableName
     * @param array $params
     * @return bool
     */
    // $params => ["key"=>":value"] $data = > [":value"=>$value]   => ["key"=>$value,"key1"=>$value1]
    public function insert($tableName, $params = [])
    {
        try {

            $con = $this->connect();

            //获取该表所有字段
            $queryTableColumn = $con->prepare('DESC '.$tableName);
            $queryTableColumn->execute();
            $table_fields = $queryTableColumn->fetchAll(PDO::FETCH_COLUMN);

            $setAttribute = array_keys($params);          //[key,key1,key2.....]

            if (in_array('id',$table_fields)){
                $table_fields = array_splice($table_fields,1);
            }

            $newArr = array_intersect($table_fields,$setAttribute);   //表和提交数据的交集

            $setValue = [];

            $newDate = [];

            $newKey = [];

            foreach ($params as $key => $val){
                if (in_array($key,$newArr)){
                    $newDate[$key] = $val;
                    //重新排序
                    array_push($setValue,':'.$key);
                    array_push($newKey,$key);
                }
            }
            $setAttribute = implode(",", $newKey);   //"key,key1....."
            $data = array_combine($setValue, array_values($newDate));   //[":value"=>$value,":value1"=>$value1]

            $setValue = implode(",", $setValue);           //":value,value1......"

            $sql = "INSERT INTO $tableName ($setAttribute) VALUES ($setValue)";   //INSERT INTO $tableName (key,key1.....) VALUES (:value,:value1......)
            $this->logInfo($sql);
            $this->logInfo($data);

            //预处理sql模板（包含占位符）
            $stmt = $con->prepare($sql);
            //变量转移
            if ($stmt->execute($data)===true){
                $id = $con->lastInsertId();        //若想返回整条数据 根据返回id查询即可
                $this->logInfo($id);
                if ($id == 0){
                    return true;
                }
                return $id;
            }else{
                return false;
            }
        } catch (PDOException $e) {
            $this->logInfo($e->getMessage());
            return false;
        }
    }

    //查询数据
    /**
     * @param $sql
     * @param array $data
     * @return array
     */
    public function queryAllValue($sql, $data = [])
    {
        try {
            $res = $this->connect()->prepare($sql);
            $res->execute($data);
            $arr = $res->fetchAll(PDO::FETCH_ASSOC);
            return $arr;
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    //查询单条数据的单个字段    优化： 任何条件都可以查询
    public function querySingleSqlValue($tableName, $id, $target)
    {
        try {
            $res = $this->connect()->prepare('select '.$target.' from ' . $tableName . ' WHERE id = :id');
            $res->execute([':id' => $id]);
            $arr = $res->fetchAll(PDO::FETCH_ASSOC);
            return $arr != [] ? $arr[0][$target] : [];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    //根据id查询单条数据
    public function findOne($tableName, $id)
    {
        try {
            $res = $this->connect()->prepare('select * from ' . $tableName . ' WHERE id = :id');
            $res->execute([':id' => $id]);
            $arr = $res->fetchAll(PDO::FETCH_ASSOC);
            return $arr[0];
        } catch (PDOException $e) {
            return $e->getMessage();
        }
    }

    //更新数据
    /**
     * @param $tableName
     * @param array $data
     * @param string $where
     * @return bool
     */
    public function update($tableName, $data = [], $where = '1=1')
    {
        try {
            //$data => [key=>$value] => str => "key=:key,key1=:key1,key2=:key2..." $data => [:key=>$value,:key1=>$value1...]
            $setValue = [];
            $arr = [];
            $where_arr = [];
            //set
            foreach ($data as $key => $val) {
                array_push($setValue, ':' . $key);    //[:key,:key1...]
                array_push($arr, $key . '=:' . $key);   //[key=:key,]
            }
            $str = implode(',', $arr);
            //where
            if (gettype($where) == 'array') {
                $data = array_merge($data, $where_arr);
                foreach ($where as $key => $val) {
                    array_push($setValue, ':' . $key);    //[:key,:key1...]
                    array_push($where_arr, $key . '=:' . $key);   //[key=:key,]
                    $data[$key] = $val;
                }
                $where = implode(',', $where_arr);
            }
            $params = array_combine($setValue, array_values($data));   //[:key=>$value]
            $this->logInfo($params);
            $sql = "UPDATE $tableName SET $str WHERE $where";
            $this->logInfo($sql);
            $res = $this->connect()->prepare($sql);
            return $res->execute($params);
        } catch (PDOException $e) {
            $this->logInfo($e->getMessage());
            return false;
        }
    }

    //事务封装

    public function begin()
    {
        $this->beginTransaction = $this->connect()->beginTransaction();
        return $this->beginTransaction;
    }


    public function commit()
    {
        $this->commit = $this->connect()->commit();
        return $this->commit;
    }

    public function rollback()
    {
        $this->rollback = $this->connect()->rollBack();
        return $this->rollback;
    }

}