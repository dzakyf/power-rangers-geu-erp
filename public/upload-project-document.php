<?php

require_once __DIR__ . '/upload-bootstrap.php';

    if (@$_REQUEST["zero_size"] == "1") {
        // IE10, IE11 zero file fix
        // get file name
        $filename = @$_REQUEST["file_name"];
        ensure_upload_dir_or_fail(dirname("project_file/" . $filename));
        // just create empty file
        file_put_contents("project_file/".$filename, "");
    } else {

        $nama_folder = date("Y/m");
        ensure_upload_dir_or_fail("project_file/" . $nama_folder);
        // get file name
        $filename = $_FILES["file"]["name"];
        $fileNamex = $_FILES["file"]["name"];
		$split = explode(".",$fileNamex);
		$ext =$split[1];
		$fileNames  = "projects-".md5(date('Ymdhis').$fileNamex).".".$ext;
        move_uploaded_file($_FILES["file"]["tmp_name"], "project_file/". $nama_folder . '/' . $fileNames);
    }
    // response
    header("Content-Type: text/json");
    print_r(json_encode(array(
        "state" => true,    // saved or not saved
        "name"  => $nama_folder . '/' . $fileNames,   // server-name
        
    )));
