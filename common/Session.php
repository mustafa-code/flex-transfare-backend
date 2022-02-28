<?php

class Session{
    public static function init() {
        if (session_status() == PHP_SESSION_NONE) {
            $ses_name = General::getConfigurationParameter("ses_name", 'zer_ses');
            session_name($ses_name);
            session_start();
        }
    }
    public static function put($key, $value){
        self::init();
        $_SESSION[$key] = $value;
    }
    public static function get($key){
        self::init();
        if(self::has($key)){
            return $_SESSION[$key];
        } else {
            return false;
        }
    }
    public static function flash($key){
        self::init();
        if(self::has($key)){
            $value = self::get($key);
            self::forget($key);
            return $value;
        }
        return false;
    }
    public static function putAll(array $array){
        self::init();
        foreach($array as $key => $value){
            $_SESSION[$key] = $value;
        }
    }
    public static function has($key){
        self::init();
        return isset($_SESSION[$key]);
    }
    public static function forget($key){
        self::init();
        unset($_SESSION[$key]);
    }
    public static function clear(){
        self::init();
        session_unset();
        session_destroy();
    }
}
