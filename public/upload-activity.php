<?php
    

    if (@$_REQUEST["zero_size"] == "1") {
        // IE10, IE11 zero file fix
        // get file name
        $filename = @$_REQUEST["file_name"];
        // just create empty file
        file_put_contents("sales_file/documentation/".$filename, "");
    } else {
        // get file name
        $filename = $_FILES["file"]["name"];
        $fileNamex = $_FILES["file"]["name"];
		$split = explode(".",$fileNamex);
		$ext =$split[1];
		$fileNames  = "fileba-".md5(date('Ymdhis').$fileNamex).".".$ext;
        move_uploaded_file($_FILES["file"]["tmp_name"], "sales_file/documentation/".$fileNames);
    }
    // response
    header("Content-Type: text/json");
    print_r(json_encode(array(
        "state" => true,    // saved or not saved
        "name"  => $fileNames,   // server-name
        
    )));


?>