<?php

function dd($data){
    echo "<pre>";
    // var_dump($data);
    print_r(json_encode($data, JSON_PRETTY_PRINT));
    exit;
}
function get_response($success, $message, $code = 0){
    return [
        "success" => $success,
        "message" => $message,
        "error_code" => $code? $code: 0
    ];
}
function exit_json($response){
    // header("Content-Type: application/json;charset=UTF-8");
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}
function getServerData($var){
    return $_SERVER[$var];
}

function url($short_path = ""){
    // $server_url = General::getConfigurationParameter("server_url", "");
    // if($server_url){
    //     return "$server_url/$short_path";
    // }
    $server = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING);

    $root = $server['DOCUMENT_ROOT'];
    $filePath = str_replace(DIRECTORY_SEPARATOR, "/", __DIR__);
    $root_file = str_replace($root, "", $filePath);
    return sprintf(
      "%s://%s",
      isset($server['HTTPS']) && $server['HTTPS'] != 'off' ? 'https' : 'http',
          $server['HTTP_HOST'].$root_file.'/'.$short_path);
}
function file_path($short_path){
    $filePath = str_replace(DIRECTORY_SEPARATOR, "/", __DIR__);
    return $filePath."/$short_path";
}

function csrf_token(){
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    $token = $_SESSION['csrf_token'];
    return $token;
}

