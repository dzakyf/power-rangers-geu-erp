<?php
if (@$_REQUEST["zero_size"] == "1") {
    // IE10, IE11 zero file fix
    $filename = @$_REQUEST["file_name"];
    file_put_contents("doc_recruitment/technical/" . $filename, "");
} else {
    $nama_folder = date("Y/m");
    if (!is_dir("doc_recruitment/technical/" . $nama_folder)) {
        mkdir("doc_recruitment/technical/" . $nama_folder, 0755, true);
    }
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

