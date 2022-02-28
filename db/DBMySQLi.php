<?php

class DBMySQLi {
    protected $conn = null;

    function __construct($database_key = "database_config") {
        $jsonFileName = General::getConfigurationFile();
        $database_config = General::getConfigurationParameter($database_key, "database_config", "$jsonFileName");

        $database = $database_config["database"];
        $server = $database_config["server"];
        $port = $database_config["port"];
        $username = $database_config["username"];
        $password = $database_config["password"];

        try {
            $this->conn = new mysqli($server, $username, $password, $database, $port);
            if ($this->conn->connect_error) {
                General::writeEvent("Connection failed: " . $this->conn->connect_error);
            }
              
        } catch (Exception $e) {
            General::writeEvent("Exception occur message is: ".$e->getMessage());
            General::writeEvent("jsonFileName: $jsonFileName");
        }
    }
    public function close(){
        if($this->conn){
            $this->conn->close();
        }
    }
    public function find($table_name, $id){
        try{
            // prepare sql and bind parameters
            $stmt = $this->conn->prepare("SELECT * FROM $table_name WHERE id = ? LIMIT 1;");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result(); // get the mysqli result
            return $result->fetch_assoc(); // fetch data   
        } catch (Exception $exception) {
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
                $keys_bind[] = "?";
            }
            $sql = "INSERT INTO $table_name (".implode(", ", $keys).") VALUES (".implode(", ", $keys_bind).")";
            $stmt = $this->conn->prepare($sql);
            foreach($fillable as $key => &$value){
                $letter = "s";
                if(is_int($value)){
                    $letter = "i";
                } else if(is_double($value)){
                    $letter = "d";
                }
                $stmt->bind_param($letter, $value);
            }
            $stmt->execute();
            return $this->conn->insert_id;
        } catch (Exception $exception) {
            General::writeEvent("DB::create exception: ".var_export($exception->getMessage(), true));
        }
        return false;
    }

    public function dml_sql($sql, $values = []){
        try {
            foreach($values as $key => $val){
                $sql = str_replace(":$key", "?", $sql);
            }
            $stmt = $this->conn->prepare($sql);
            foreach($values as $key => &$value){
                $letter = "s";
                if(is_int($value)){
                    $letter = "i";
                } else if(is_double($value)){
                    $letter = "d";
                }
                $stmt->bind_param($letter, $value);
            }
            $stmt->execute();
            return $this->conn->affected_rows;
        } catch (Exception $exception) {
            General::writeEvent("DB::dml_sql exception: ".var_export($exception->getMessage(), true));
            General::writeEvent("Error sql: $sql");
        }
        return false;
    }
    public function select_with_headers($sql, $values = [], $headers_required = true){
        try {
            if(!$this->conn){
                return [];
            }
            foreach($values as $key => $val){
                $sql = str_replace(":$key", "?", $sql);
            }
            $stmt = $this->conn->prepare($sql);
            $letter = "";
            $vals = [];
            foreach($values as $key => &$value){
                if(is_int($value)){
                    $letter .= "i";
                } else if(is_double($value)){
                    $letter .= "d";
                } else {
                    $letter .= "s";
                }
                $vals[] = $value;
            }
            if($letter){
                $stmt->bind_param($letter, ...$vals);
            }
            $stmt->execute();

            $result = $stmt->get_result();
            $list = [];
            while($row = $result->fetch_assoc()){
                $list[] = $row;
            }
            $headers = [];
            if($headers_required){
                $field_cnt = $result->field_count;
                for ($i = 0; $i < $field_cnt; $i++) {
                    $meta = $result->fetch_field_direct($i);
                    $headers[] = $meta->name;
                }

            }
            return [
                "headers" => $headers,
                "body" => $list,
            ];

        } catch (Exception $exception) {
            General::writeEvent("DB::Select exception: ".var_export($exception->getMessage(), true));
            General::writeEvent("sql: $sql");
        }
        return false;
    }
}
