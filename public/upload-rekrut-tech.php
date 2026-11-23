<?php

require_once __DIR__ . '/upload-bootstrap.php';

if (@$_REQUEST["zero_size"] == "1") {
    // IE10, IE11 zero file fix
    $filename = @$_REQUEST["file_name"];
    ensure_upload_dir_or_fail(dirname("doc_recruitment/technical/" . $filename));
    file_put_contents("doc_recruitment/technical/" . $filename, "");
} else {
    $nama_folder = date("Y/m");
    ensure_upload_dir_or_fail("doc_recruitment/technical/" . $nama_folder);
    $fileNamex = $_FILES["file"]["name"];
    $split = explode(".", $fileNamex);
    $ext = end($split);
    $fileNames = "tech-" . md5(date('Ymdhis') . $fileNamex) . "." . $ext;
    move_uploaded_file($_FILES["file"]["tmp_name"], "doc_recruitment/technical/" . $nama_folder . '/' . $fileNames);
}

header("Content-Type: text/json");
print_r(json_encode([
    "state" => true,
    "name"  => $nama_folder . '/' . $fileNames,
]));

