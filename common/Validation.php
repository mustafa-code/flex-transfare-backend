<?php

class Validation{
    public static function validate($rules, $data){
        $db = new DB();
        $errors = array();
        foreach ($rules as $key => $value) {
            if(is_int(strpos($value, "required"))){
                if(!isset($data[$key]) || empty($data[$key])){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is required.",
                        'code' => 'required'
                    ];
                    continue;
                }
            }
            if(is_int(strpos($value, "nullable"))){
                if(!isset($data[$key]) || empty($data[$key])){
                    continue;
                }
            }
            if(is_int(strpos($value, "unique"))) {
                $table_pos_begin = strpos($value, "unique") + strlen('unique')+1;
                $table_pos_end = strpos($value, "|", $table_pos_begin);
                if(!$table_pos_end){
                    $table_pos_end = strlen($value);
                }
                $table = (substr($value, $table_pos_begin, $table_pos_end - $table_pos_begin));
                $item = $data[$key];
                if(strpos($value, "phone") !== false){
                    $item = self::tokenizerPhone($item);
                }
                if(is_int(strpos($table, "."))){
                    $arr = explode(".",$table);
                    $table = $arr[0];
                    $id = $arr[1];

                    $response = $db->select("SELECT count(*) as counts FROM $table WHERE $key = :item_value AND id != :id;", [
                        "item_value" => $item,
                        "id" => $id,
                    ]);
                    if($response[0]['counts'] > 0){
                        $errors[$key][] = [
                            'message' => self::getReadableName($key) . " must be unique.",
                            'code' => "unique"
                        ];
                    }
                } else {
                    $response = $db->select("SELECT count(*) as counts FROM $table WHERE $key = :item_value;", [
                        "item_value" => $item,
                    ]);
                    if($response[0]['counts'] > 0){
                        $errors[$key][] = [
                            'message' => self::getReadableName($key) . " must be unique.",
                            'code' => "unique"
                        ];
                    }
                }
            }
            if(is_int(strpos($value, "phone"))){
                if(!self::getOperatorId($data[$key])){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is invalid syntax.",
                        'code' => "phone"
                    ];
                }
            }
            if(is_int(strpos($value, "email"))){
                if (!filter_var($data[$key], FILTER_VALIDATE_EMAIL)) {
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is invalid syntax.",
                        'code' => "email"
                    ];
                }
            }
            if(is_int(strpos($value, "json"))){
                json_decode($data[$key]);
                if (json_last_error() != 0) {
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is invalid json formate.",
                        'code' => "json"
                    ];
                }
            }
            if(is_int(strpos($value, "numeric"))){
                if (!filter_var($data[$key], FILTER_VALIDATE_INT)) {
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is invalid syntax.",
                        'code' => "numeric"
                    ];
                }
            }
            if(is_int(strpos($value, "maxlen"))){
                $table_pos_begin = strpos($value, "maxlen") + strlen('maxlen')+1;
                $table_pos_end = strpos($value, "|", $table_pos_begin);
                if(!$table_pos_end){
                    $table_pos_end = strlen($value);
                }
                $maxlen = (substr($value, $table_pos_begin, $table_pos_end - $table_pos_begin));
                if(strlen($data[$key]) > $maxlen){
                    $errors[$key][] = [
                        'message' => "Max length of ".self::getReadableName($key)." is $maxlen.",
                        'code' => "maxlen:$maxlen"
                    ];
                }
            }
            if(is_int(strpos($value, "minlen"))){
                $table_pos_begin = strpos($value, "minlen") + strlen('minlen')+1;
                $table_pos_end = strpos($value, "|", $table_pos_begin);
                if(!$table_pos_end){
                    $table_pos_end = strlen($value);
                }
                $minlen = (substr($value, $table_pos_begin, $table_pos_end - $table_pos_begin));
                if(strlen($data[$key]) < $minlen){
                    $errors[$key][] = [
                        'message' => "Min length of ".self::getReadableName($key)." is $minlen.",
                        'code' => "minlen:$minlen"
                    ];
                }
            }
            if(is_int(strpos($value, "confirmed"))){
                if(!isset($data["{$key}_confirmation"]) || $data[$key] != $data["{$key}_confirmation"]){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " is not confirmed.",
                        'code' => 'confirmed'
                    ];
                }
            }
            if(is_int(strpos($value, "arabic"))){
                if(!preg_match('/[^\x20-\x7f]/', $data[$key])){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key)." must be arabic text.",
                        'code' => 'arabic'
                    ];
                }
            }
            if(is_int(strpos($value, "english"))){
                if(preg_match('/[^\x20-\x7f]/', $data[$key])){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key)." must be english text.",
                        'code' => 'english'
                    ];
                }
            }
            if(is_int(strpos($value, "valid_id"))) {
                $table_pos_begin = strpos($value, "valid_id") + strlen('valid_id')+1;
                $table_pos_end = strpos($value, "|", $table_pos_begin);
                if(!$table_pos_end){
                    $table_pos_end = strlen($value);
                }
                $table = (substr($value, $table_pos_begin, $table_pos_end - $table_pos_begin));
                $response = $db->select("SELECT count(*) as counts FROM $table WHERE id = :id;", [
                    "id" => $data[$key],
                ]);
                if($response[0]['counts'] <= 0){
                    $errors[$key][] = [
                        'message' => self::getReadableName($key) . " must be valid.",
                        'code' => "valid_id"
                    ];
                }
            }
        }
        return $errors;
    }
    private static function getReadableName($name){
        return ucfirst(str_replace("_"," ", $name));
    }
    private static function getOperatorId($phone){
        $matches = [];
        preg_match( '/^(0|\+?249|00249)?((1[0-9])[0-9]{7})$/', $phone, $matches);
        if($matches){
            return ['id' => 3, 'matches' => $matches[2]]; // Sudani
        } else {
            preg_match( '/^(0|\+?249|00249)?((9(0|1|6))[0-9]{7})$/', $phone, $matches);
            if($matches){
                return ['id' => 1, 'matches' => $matches[2]]; // Zain
            } else {
                preg_match( '/^(0|\+?249|00249)?((9(2|3|9))[0-9]{7})$/', $phone, $matches);
                if($matches){
                    return ['id' => 2, 'matches' => $matches[2]]; // MTN
                }
            }
        }
        return false;
    }
    private static function tokenizerPhone($phone) {
        return substr($phone, strlen($phone) - 9, 9);
    }
}