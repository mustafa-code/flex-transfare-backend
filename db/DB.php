<?php

class DB {

    private $db;
    function __construct($database_key = "database_config") {
        $jsonFileName = General::getConfigurationFile();
        $db_mode = General::getConfigurationParameter("db_mode", "pdo", "$jsonFileName");
        if($db_mode === "mysqli"){
            $this->db = new DBMySQLi($database_key);
        } else {
            $this->db = new DBPDO($database_key);
        }
    }
    public function close(){
        $this->db->close();
    }
    public function find($table_name, $id){
        return $this->db->find($table_name, $id);
    }

    public function create($table_name, $fillable){
        return $this->db->create($table_name, $fillable);
    }

    public function dml_sql($sql, $values = []){
        return $this->db->dml_sql($sql, $values);
    }


    public function select($sql, $values = []){
        return $this->db->select_with_headers($sql, $values, false)["body"];
    }
    public function all($table_name){
        return $this->select("SELECT * FROM $table_name;");
    }

    public function select_with_headers($sql, $values = [], $headers_required = true){
        return $this->db->select_with_headers($sql, $values, $headers_required);
    }

    public function update($table_name, $fillable, $id){
        $l = [];
        foreach($fillable as $k => $v){
            $l[] = "$k = :$k";
        }
        $sql_builder = "UPDATE $table_name SET ". implode(", ", $l) . " WHERE id = :id;";
        $fillable["id"] = $id;
        return $this->db->dml_sql($sql_builder, $fillable);
    }

    public function delete($table_name, $id){
        return $this->db->dml_sql("DELETE FROM $table_name WHERE id = :id", [
            "id" => $id,
        ]);
    }

}
