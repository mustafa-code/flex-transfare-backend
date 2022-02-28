<?php

class General {

    static $msg_err = array();
    static $msg_succ = array();
    static $errorFlage = false;
    static $succFlage = false;

    public static function getConfigurationFile() {
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $jsonFileName = __DIR__."/../config.json";
        } else {
            $jsonFileName = __DIR__."/../../../config/shop.json";
            // $jsonFileName = "/var/sites/w/wanplastic.com/config/waan.json";
        }

        return $jsonFileName;
    }

    public static function redirect($path){
        header("location: $path");
        exit;
    }
    public static function redirectBack(){
        self::redirect(self::backUrl());
    }
    public static function backUrl(){
        return $_SERVER['HTTP_REFERER'];
    }
    public static function getReadableName($name){
        return ucfirst(str_replace("_"," ", $name));
    }
    // Function to get the client IP address
    public static function getClientIp() {
        $server = $_SERVER;//filter_input_array(INPUT_SERVER, FILTER_SANITIZE_URL);
        $ipaddress = '';
        if (isset($server['HTTP_CLIENT_IP'])) {
                $ipaddress = $server['HTTP_CLIENT_IP'];
        } else if (isset($server['HTTP_X_FORWARDED_FOR'])) {
                $ipaddress = $server['HTTP_X_FORWARDED_FOR'];
        } else if (isset($server['HTTP_X_FORWARDED'])) {
                $ipaddress = $server['HTTP_X_FORWARDED'];
        } else if (isset($server['HTTP_FORWARDED_FOR'])) {
                $ipaddress = $server['HTTP_FORWARDED_FOR'];
        } else if (isset($server['HTTP_FORWARDED'])) {
                $ipaddress = $server['HTTP_FORWARDED'];
        } else if (isset($server['REMOTE_ADDR'])) {
                $ipaddress = $server['REMOTE_ADDR'];
        } else {
                $ipaddress = 'UNKNOWN';
        }
        return $ipaddress;
    }
    public static function tokenizeList($list_str, $is_int = false){
        $list_array = array();
        $token = strtok($list_str, ",");
        while ($token !== false){
            $list_array[] = ($is_int) ? intval($token): $token;
            $token = strtok(",");
        }
        return $list_array;
    }

    public static function writeEvent($message, $fileName = "ticket") {

        $TimeRef = date('Y-m-d H:i:s');
        $date = date('d');
        if (strncasecmp(PHP_OS, 'WIN', 3) == 0) {
            $fileName = __DIR__."/../log/log".$date.".log";
        } else {
            $fileName = __DIR__."/../../../logs/shop-".$date.".log";
            // $fileName = "/var/sites/w/wanplastic.com/logs/waan-".$date.".log";
        }

        $Handle = fopen($fileName, 'a');
        if (!$Handle) {
            $Handle = fopen($fileName, 'w');
        }
        $Data = '--- ' . $TimeRef . ' -- ' . $message . "~\n";
        fwrite($Handle, $Data);
        fclose($Handle);
    }

    public static function callURL($url, $params = null, $headers = false) {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            if($headers){
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            } else {
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            return $response;
        } catch (Exception $exception) {
            General::writeEvent("callURL error: " . $exception->getMessage());
        }
    }

    public static function getConfigurationParameter($parameterName, $defaulValue, $jsonFileName = FALSE) {
        try {
            if($jsonFileName === FALSE){
                $jsonFileName = General::getConfigurationFile();
            }
            if (file_exists($jsonFileName)) {

                $jsonFileNameContent = file_get_contents($jsonFileName);
                $value = json_decode($jsonFileNameContent, true);

                return isset($value[$parameterName])? $value[$parameterName]: $defaulValue;
            }
        } catch (Exception $exception) {
            General::writeEvent($exception->getMessage());
        }
        return $defaulValue;
    }

    public static function clean($param, $methodType = INPUT_POST) {

        $strValue = filter_input($methodType, $param);
        $str = trim($strValue);
        // if (get_magic_quotes_gpc()) {
        //     $str = stripslashes($str);
        // }
        return ($str);
    }

    public static function isJson($string) {
        json_decode($string);
        if (json_last_error() == 0) {
            return true;
        } else {
            return false;
        }
    }

    public static function isNull($filed, $messageValue) {

        if ($filed == '') {
            General::addError($messageValue);
            return true;
        } else {
            return false;
        }
    }

    public static function validateNumber($value, $messageValue = "Not number") {

        if (!is_numeric($value)) {
            General::addError($messageValue);
            return false;
        } else {
            return true;
        }
    }

    public static function validateEmail($value, $messageValue) {

        if (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $value)) {
            General::addError($messageValue);
            return false;
        } else {
            return true;
        }
    }

    public static function validateDate($m, $d, $y, $messageValue) {
        if (checkdate($m, $d, $y)) {
            return true;
        } else {
            General::addError($messageValue);
            return false;
        }
    }

    public static function validateFile($value, $messageValue) {

        if (isset($_FILES[$value]) && $_FILES[$value]['size'] > 0) {
            return true;
        } else {
            General::addError($messageValue);
            return false;
        }
    }

    public static function addError($err) {
        //General::$msg_err[] = $err ;
        array_push(General::$msg_err, $err);
        General::setErrorFlage(true);
    }

    public static function getError() {
        return General::$msg_err;
    }

    public static function getErrorFlage() {

        return General::$errorFlage;
    }

    public static function setErrorFlage($errorFlage) {
        General::$errorFlage = $errorFlage;
    }

    public static function secondsToTime($seconds, $format = '%a days, %h:%i:%s ') {
        $dtF = new DateTime("@0");
        $dtT = new DateTime("@$seconds");
        return $dtF->diff($dtT)->format($format);
        //return  gmdate("d H:i:s",$seconds);
    }

    public static function secondsToHours($seconds) {
        $dtF = new DateTime("@0");
        $dtT = new DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%h');
        //return  gmdate("d H:i:s",$seconds);
    }

    public static function isAllowedFileType($extension) {
        $allowedExts = array("pem");
        if (in_array($extension, $allowedExts)) {
            return true;
        } else {
            return false;
        }
    }

    public static function getHeaderValue($key) {

        $token = null;
        $headers = apache_request_headers();
        if (isset($headers[$key])) {
            $token = $headers[$key];
        }
        return $token;
    }

    public static function removeZero($number) {
        $number = General::removeSpace($number);
        $value = substr($number, 0, 1);

        if (strcmp($value, "0") == 0) {
            $number = substr($number, 1);
        }

        return $number;
    }

    public static function add249($number) {

        $number = General::removeZero($number);

        if (is_numeric($number)) {

            if (strlen($number) == 9) {
                $number = "249" . $number;
            }
        }


        return $number;
    }

    public static function removeSpace($string) {

        $value = preg_replace('/\s+/', '', $string);

        return $value;
    }
    public static function getJsonData($string, $urldecode = true) {
        if($urldecode){
            return urldecode($string);
        } else {
            return $string;
        }
        // return urldecode(stripslashes(stripslashes($string)));
    }
    public static function getRequestData() {
        $strValue = file_get_contents('php://input');
        if ($strValue == null) {
            $strValue = $_SERVER["QUERY_STRING"];
            // General::writeEvent("QUERY_STRING : " . $strValue);
        } else {
            // General::writeEvent("input : " . $strValue);
        }
        $str = trim($strValue);
        return $str;
    }
    public static function setCookie($key,$value){
         setcookie($key,$value,time()+60*60*24*30,'/',null,false,false);
    }
    public static function getCookie($key) {
        $strValue = filter_input(INPUT_COOKIE, $key);
        return $strValue;
    }
    public static function getOperatorId($phone){
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
    public static function limit_text_chars($string, $charsreturned) {
        $retval = $string;  //  Just in case of a problem
        if(strlen($string) > $charsreturned){
            $retval = substr($string, 0, $charsreturned)." ...";
        }
        return $retval;
    }
    public static function limit_text($string, $wordsreturned) {
        $retval = $string;  //  Just in case of a problem
        $array = explode(" ", $string);
        /*  Already short enough, return the whole thing*/
        if (count($array)<=$wordsreturned)
        {
            $retval = $string;
        }
        /*  Need to chop of some words*/
        else
        {
            array_splice($array, $wordsreturned);
            $retval = implode(" ", $array)." ...";
        }
        return $retval;
    }
    public static function generatePassword() {
        $keyspace = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return self::generateRandom($keyspace);
    }
    public static function generateVoucher() {
        $keyspace = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return self::generateRandom($keyspace);
    }
    private static function generateRandom($keyspace, $length = 10){
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; $i++) {
            $str .= $keyspace[random_int(0, $max)];
        }
        return $str;
    }
    public static function tokenizerPhone($phone) {
        return substr($phone, strlen($phone) - 9, 9);
    }
    public static function generateSMSToken(){
        return self::generateRandom("123456789", 4);
    }
    public static function saveBase64Image($image, $imageFileType){
        $root = file_path("");
        $file_name = "files/FILE_". date("Ymd_His")."_" .uniqid() . ".$imageFileType";
        file_put_contents($root.$file_name, base64_decode(str_replace("data:image/png;base64", "", $image)));
        return $file_name;
    }
}
