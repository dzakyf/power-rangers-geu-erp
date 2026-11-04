<?php

if (@$_REQUEST["zero_size"] == "1") {
    // IE10, IE11 zero file fix
    $filename = @$_REQUEST["file_name"];
    file_put_contents("doc_recruitment/psikotest/" . $filename, "");
} else {
    $nama_folder = date("Y/m");
    if (!is_dir("doc_recruitment/psikotest/" . $nama_folder)) {
        mkdir("doc_recruitment/psikotest/" . $nama_folder, 0755, true);
    }
    $filename = $_FILES["file"]["name"];
    $fileNamex = $_FILES["file"]["name"];
    $split = explode(".", $fileNamex);
    $ext = $split[count($split) - 1];
    $fileNames  = "filepsikotest-" . md5(date('Ymdhis') . $fileNamex) . "." . $ext;
    move_uploaded_file($_FILES["file"]["tmp_name"], "doc_recruitment/psikotest/" . $nama_folder . '/' . $fileNames);
}

// response
header("Content-Type: text/json");
print_r(json_encode(array(
    "state" => true,
    "name"  => "doc_recruitment/psikotest/" . $nama_folder . '/' . $fileNames,
)));

