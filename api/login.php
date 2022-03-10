<?php
require_once "../functions.php";
require_once file_path("common/General.php");
require_once file_path("common/Validation.php");
require_once file_path("db/DB.php");
require_once file_path("db/DBMySQLi.php");
require_once file_path("db/DBPDO.php");

$method = getServerData("REQUEST_METHOD");

if ($method == 'POST') {
    // $requestString = General::getJsonData(General::getRequestData());
    // $request = json_decode($requestString, true);
    $request = filter_input_array(INPUT_POST);

    $rules = array(
        "email" => "required|email",
        "password" => "required",
    );
    $errors = Validation::validate($rules, $request);
    if(count($errors) > 0){
        exit_json([
            'success' => false,
            'error_code' => 18,
            'errors' => $errors,
            "message" => "Validation error"
        ]);
    } else {
        $db = new DB();
        $users = $db->select("SELECT * FROM users WHERE email = :email;", [
            "email" => $request["email"]
        ]);
        if($users){
            $user = $users[0];
            if($user["password"] == $request["password"]){
                $result = [
                    "success" => true,
                    "message" => "User data is valid",
                    "user" => $user,
                ];
            } else {
                $result = [
                    "success" => false,
                    "message" => "Password is not valid",
                ];
            }
        } else {
            $result = [
                "success" => false,
                "message" => "Email is not found",
            ];
        }
        exit_json($result);
    }

} else {
    exit_json(get_response(false, "Method not allowed", 405));
}
