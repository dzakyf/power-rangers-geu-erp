<?php
    

    if (@$_REQUEST["zero_size"] == "1") {
       error_log("No File Upload");
    } else {

        $nama_folder = date("Y/m");
        if (!is_dir("tmpdata/" . $nama_folder)) {
            mkdir("tmpdata/" . $nama_folder, 0755, true);
        }
        // get file name
        $filename = $_FILES["file"]["name"];
        $fileNamex = $_FILES["file"]["name"];
		$split = explode(".",$fileNamex);
		$ext =$split[1];
		$fileNames  = "filecpsd-".md5(date('Ymdh').$fileNamex).".".$ext;
        move_uploaded_file($_FILES["file"]["tmp_name"], "tmpdata/" . $nama_folder . '/' . $fileNames);
    }
    
    // response
    header("Content-Type: text/json");
    print_r(json_encode(array(
        "state" => true,    // saved or not saved
        "name"  => $nama_folder . '/' . $fileNames, // server-name
        
    )));


?>