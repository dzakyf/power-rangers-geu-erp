<?php
    

    if (@$_REQUEST["zero_size"] == "1") {
        // IE10, IE11 zero file fix
        // get file name
        $filename = @$_REQUEST["file_name"];
        // just create empty file
        file_put_contents("doc_recruitment/ktp/".$filename, "");
    } else {
        $nama_folder = date("Y/m");
        if (!is_dir("doc_recruitment/ktp/" . $nama_folder)) {
            mkdir("doc_recruitment/ktp/" . $nama_folder, 0755, true);
        }
        // get file name
        $filename = $_FILES["file"]["name"];
        $fileNamex = $_FILES["file"]["name"];
		$split = explode(".",$fileNamex);
		$ext =$split[count($split) - 1];
		$fileNames  = "ktp-".md5(date('Ymdhis').$fileNamex).".".$ext;
        move_uploaded_file($_FILES["file"]["tmp_name"], "doc_recruitment/ktp/". $nama_folder . '/' . $fileNames);
    }
    // response
    header("Content-Type: text/json");
    print_r(json_encode(array(
        "state" => true,    // saved or not saved
        "name"  => $nama_folder . '/' .$fileNames,   // server-name
        
    )));
