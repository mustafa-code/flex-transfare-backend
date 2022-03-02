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
        // "file_content" => "required",
        "start_enc" => "required",
        "end_enc" => "required",
        "size_before" => "required",
        "size_after" => "required",
        "email" => "required",
        "title" => "required",
        "message" => "required",
        "file_name" => "required",
        "file_type" => "required",
        "enc_key" => "required",
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
        $root = file_path("");
        $file_name = "files/FILE_". date("Ymd_His")."_" .uniqid()."_".$_FILES["file"]["name"];
        move_uploaded_file($_FILES["file"]["tmp_name"], "$root$file_name");
    
        // $file = General::saveBase64Image($request["file_content"], "aes");
        $db = new DB();
        $id = $db->create("files", [
            "message" => $request["message"],
            "file_name" => $request["file_name"],
            "file_type" => $request["file_type"],
            "enc_key" => $request["enc_key"],
            "start_encryption" => $request["start_enc"],
            "end_encryption" => $request["end_enc"],
            "size_before" => $request["size_before"],
            "size_after" => $request["size_after"],
            "email" => $request["email"],
            "title" => $request["title"],
            "file_url" => url($file_name),
        ]);
        if($id){
            $result = [
                "success" => true,
                "message" => "File uploaded successfully",
                "data" => $db->find("files", $id),
            ];
        } else {
            $result = [
                "success" => false,
                "message" => "Fail to upload file",
            ];
        }
        exit_json($result);
    }

} else {
    exit_json(get_response(false, "Method not allowed", 405));
}
