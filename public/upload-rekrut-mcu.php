<?php
// Upload berkas hasil MCU (dipakai dhx.Vault di recruitment-mcu-detail)
$baseDir = "doc_recruitment/mcu/";
$allowed = array('pdf', 'jpg', 'jpeg', 'png', 'zip', 'rar', 'doc', 'docx');

header("Content-Type: text/json");

if (@$_REQUEST["zero_size"] == "1") {
    echo json_encode(array("state" => false, "message" => "File kosong"));
    exit;
}

if (empty($_FILES["file"]) || $_FILES["file"]["error"] !== UPLOAD_ERR_OK) {
    echo json_encode(array("state" => false, "message" => "Upload gagal"));
    exit;
}

$ext = strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION));
if (!in_array($ext, $allowed, true)) {
    echo json_encode(array("state" => false, "message" => "Tipe file tidak diizinkan"));
    exit;
}

$folder = date("Y/m");
if (!is_dir($baseDir . $folder)) {
    mkdir($baseDir . $folder, 0755, true);
}

$fileName = "filemcu-" . md5(date('YmdHis') . $_FILES["file"]["name"] . mt_rand()) . "." . $ext;
$target = $baseDir . $folder . '/' . $fileName;

if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target)) {
    echo json_encode(array("state" => false, "message" => "Gagal menyimpan file"));
    exit;
}

echo json_encode(array("state" => true, "name" => $target));

