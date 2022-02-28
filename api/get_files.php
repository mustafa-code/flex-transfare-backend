<?php
require_once "../functions.php";
require_once file_path("common/General.php");
require_once file_path("common/Validation.php");
require_once file_path("db/DB.php");
require_once file_path("db/DBMySQLi.php");
require_once file_path("db/DBPDO.php");

$method = getServerData("REQUEST_METHOD");

if ($method == 'GET') {
    $db = new DB();

    $data = $db->select("SELECT * FROM files ORDER BY id DESC");
    $result = [
        "success" => true,
        "message" => "All file list",
        "data" => $data,
        "count" => count($data),
    ];
    exit_json($result);
} else {
    exit_json(get_response(false, "Method not allowed", 405));
}
