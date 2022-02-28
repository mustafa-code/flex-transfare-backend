<?php
require_once "../functions.php";
require_once file_path("common/General.php");
require_once file_path("common/Validation.php");
require_once file_path("db/DB.php");
require_once file_path("db/DBMySQLi.php");
require_once file_path("db/DBPDO.php");

$method = getServerData("REQUEST_METHOD");

if ($method == 'GET') {
    $request = filter_input_array(INPUT_GET, FILTER_SANITIZE_NUMBER_INT);

    $rules = array(
        "id" => "required|valid_id:files",
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
        $id = $request["id"];
        $db = new DB();
        $file = $db->find("files", $id);
        $file_path = str_replace(url(), "", $file["file_url"]);
        if(file_exists($file_path)){
            unlink($file_path);
        }
        $deleted = $db->dml_sql("DELETE FROM files WHERE id = :id;", [
            "id" => $id,
        ]);
        if($deleted){
            $result = [
                "success" => true,
                "message" => "File deleted successfully",
                "result" => $deleted,
            ];
        } else {
            $result = [
                "success" => false,
                "message" => "Fail to delete file",
            ];
        }
        exit_json($result);
    }

} else {
    exit_json(get_response(false, "Method not allowed", 405));
}
