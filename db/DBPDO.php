<?php

class DBPDO {
    protected $conn = null;

    function __construct($database_key = "database_config") {
        $jsonFileName = General::getConfigurationFile();

        $database_config = General::getConfigurationParameter($database_key, "", "$jsonFileName");

        $database = $database_config["database"];
        $server = $database_config["server"];
        $port = $database_config["port"];
        $username = $database_config["username"];
        $password = $database_config["password"];

        try {
            $this->conn = new PDO("mysql:host=$server;port=$port;dbname=$database", $username, $password, [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ] );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            General::writeEvent("Exception occur message is: ".$e->getMessage());
            General::writeEvent("jsonFileName: $jsonFileName");
        }
    }
    public function close(){
        $this->conn = null;
    }
    public function find($table_name, $id){
        try{
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare("SELECT * FROM $table_name WHERE id = :id LIMIT 1");
            $stmt->bindParam(":id", $id);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $result = $stmt->fetchAll();
            if($result){
                return $result[0];
            }
        } catch (PDOException $exception) {
            General::writeEvent("DB::Find exception: ".var_export($exception->getMessage(), true));
        }
        return false;
    }

    public function create($table_name, $fillable){
        // prepare sql and bind parameters
        try {
            $keys = [];
            $keys_bind = [];
            foreach($fillable as $key => $value){
                $keys[] = $key;
                $keys_bind[] = ":$key";
            }
            $sql = "INSERT INTO $table_name (".implode(", ", $keys).") VALUES (".implode(", ", $keys_bind).")";
            $stmt = $this->conn->prepare($sql);
            foreach($fillable as $key => &$value){
                $stmt->bindParam(":$key", $value);
            }
            $stmt->execute();
        return $this->conn->lastInsertId();
        } catch (PDOException $exception) {
            General::writeEvent("DB::create exception: ".var_export($exception->getMessage(), true));
        }
        return false;
    }

    public function dml_sql($sql, $values = []){
        try {
            $stmt = $this->conn->prepare($sql);
            foreach($values as $key => &$value){
                if(is_int($value)){
                    $stmt->bindParam(":$key", $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam(":$key", $value);
                }
            }
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $exception) {
            General::writeEvent("DB::dml_sql exception: ".var_export($exception->getMessage(), true));
            General::writeEvent("Error sql: $sql");
        }
        return false;
    }
    public function select_with_headers($sql, $values = [], $headers_required = true){
        try {
            if($this->conn){
                $stmt = $this->conn->prepare($sql);
                foreach($values as $key => &$value){
                    if(is_int($value)){
                        $stmt->bindParam(":$key", $value, PDO::PARAM_INT);
                    } else {
                        $stmt->bindParam(":$key", $value);
                    }
                }
                $stmt->execute();
                // set the resulting array to associative
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $result = $stmt->fetchAll();

                $headers = [];
                if($headers_required){
                    $colcount = $stmt->columnCount();
                    for ($i = 0; $i < $colcount; $i++) {
                        $meta = $stmt->getColumnMeta($i);
                        $headers[] = $meta["name"];
                    }
                }
                return [
                    "headers" => $headers,
                    "body" => $result,
                ];
            } else {
                return [];
            }
        } catch (PDOException $exception) {
            General::writeEvent("DB::Select exception: ".var_export($exception->getMessage(), true));
            General::writeEvent("sql: $sql");
        }
        return false;
    }
}
