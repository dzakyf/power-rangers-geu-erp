<?php
error_reporting(0);

use App\Services\HCGA\EmploymentService;

try {
    $service = app(EmploymentService::class);
    $result = $service->updateEmployeeDetailData($_POST);
    echo "success";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
