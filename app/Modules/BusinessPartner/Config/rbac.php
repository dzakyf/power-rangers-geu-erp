<?php

/*
|--------------------------------------------------------------------------
| BDP route permission map
|--------------------------------------------------------------------------
|
| Keys are "HTTP_METHOD route/uri". The route URI may contain parameters.
| Values are either [menu_id, action] or a list of alternative
| [menu_id, action] requirements (user only needs one).
|
| Only menu landing/index-table actions are mapped here. Shared helper
| endpoints stay unmapped until their cross-menu usage is reviewed.
|
*/

return [
    // Master Container
    'GET master-container' => ['SI00258', 'view'],
    'GET master-container-data' => ['SI00258', 'view'],
    'GET master-container-get' => ['SI00258', 'view'],
    'GET master-container-create' => ['SI00258', 'create'],
    'POST master-container-submit' => ['SI00258', 'create'],
    'POST master-container-edit' => ['SI00258', 'edit'],
    'DELETE master-container-delete' => ['SI00258', 'delete'],

    // Master POO
    'GET master-poo' => ['SI00259', 'view'],
    'GET master-poo-data' => ['SI00259', 'view'],
    'GET master-poo-create' => ['SI00259', 'create'],
    'POST master-poo-create-submit' => ['SI00259', 'create'],
    'GET master-poo-update' => ['SI00259', 'edit'],
    'POST master-poo-update-submit' => ['SI00259', 'edit'],
    'POST master-poo-delete' => ['SI00259', 'delete'],

    // Master Kemasan
    'GET master-category' => ['SI00260', 'view'],
    'GET master-category-data' => [
        ['SI00260', 'view'],
        ['SI00259', 'view'],
    ],
    'GET master-category-create' => ['SI00260', 'create'],
    'POST master-category-submit' => ['SI00260', 'create'],
    'POST master-category-edit' => ['SI00260', 'edit'],
    'DELETE master-category-delete' => ['SI00260', 'delete'],

    // Survey
    'GET partner-survey-index' => ['SI00227', 'view'],
    'GET partner-survey-dashboard' => ['SI00227', 'view'],
    'GET partner-survey-data' => [
        ['SI00227', 'view'],
        ['SI00228', 'view'],
    ],
    'GET partner-survey-data-history' => ['SI00227', 'view'],
    'GET partner-survey-create' => ['SI00227', 'create'],
    'GET partner-survey-create-reply' => ['SI00227', 'create'],
    'POST partner-survey-submit' => ['SI00227', 'create'],
    'POST partner-survey-update' => ['SI00227', 'edit'],
    'POST partner-survey-delete' => ['SI00227', 'delete'],

    // Approval Survey
    'GET partner-survey-approval-index' => ['SI00228', 'view'],
    'GET partner-survey-approval-data' => ['SI00228', 'view'],
    'GET partner-survey-approval-data-log' => ['SI00228', 'view'],

    // Survey Form Builder
    'GET partner-survey-form-index' => ['SI01052', 'view'],
    'GET partner-survey-form-builder' => ['SI01052', 'view'],
    'POST partner-survey-form-create-version' => ['SI01052', 'create'],
    'POST partner-survey-form-save' => ['SI01052', 'edit'],
    'POST partner-survey-form-publish' => ['SI01052', 'approve'],

    // Warehouse Stock Opname
    'GET partner-warehouse-stock-opname' => ['SI00276', 'view'],
    'GET partner-warehouse-stock-opname-data' => ['SI00276', 'view'],
    'GET partner-warehouse-stock-opname-detail' => ['SI00276', 'view'],
    'GET partner-warehouse-stock-opname-adjustment-data' => ['SI00276', 'view'],
    'GET partner-warehouse-stock-opname-systemqty' => ['SI00276', 'view'],
    'GET partner-warehouse-stock-opname-new' => ['SI00276', 'create'],
    'POST partner-warehouse-stock-opname-submit' => ['SI00276', 'create'],
    'POST partner-warehouse-stock-opname-update' => ['SI00276', 'edit'],
    'POST partner-warehouse-stock-opname-add-adjustment' => ['SI00276', 'edit'],
    'POST partner-warehouse-stock-opname-action' => ['SI00276', 'edit'],
    'POST partner-warehouse-stock-opname-finalize' => ['SI00276', 'approve'],

    // Sales Tiering
    'GET partner-sales-tiering' => ['SI00279', 'view'],
    'GET xml-partner-sales-tiering' => ['SI00279', 'view'],

    // Branding Marketing
    'GET partner-branding-marketing-index' => ['SI00252', 'view'],
    'GET partner-branding-marketing-data' => ['SI00252', 'view'],
    'GET partner-branding-marketing-create' => ['SI00252', 'create'],
    'POST partner-branding-marketing-submit' => ['SI00252', 'create'],
    'PUT partner-branding-marketing-update' => ['SI00252', 'edit'],

    // Renewal Approval
    'GET partner-renewal-approval-index' => ['SI00273', 'view'],
    'GET partner-renewal-approval-detail' => ['SI00273', 'view'],
    'GET partner-renewal-approval-create' => ['SI00273', 'create'],

    // Finance: Pickup Cost
    'GET finance-pickup-cost' => ['SI00378', 'view'],
    'GET finance-pickup-route-detail' => [
        ['SI00378', 'view'],
        ['SI00277', 'view'],
        ['SI00267', 'view'],
    ],
    'GET partner-pickup-route-map-data' => [
        ['SI00378', 'view'],
        ['SI00267', 'view'],
    ],
    'POST finance-cost-approve-route' => ['SI00378', 'approve'],

    // Finance: Acquisition Cost
    'GET finance-acquisition-cost' => ['SI00379', 'view'],
    'GET finance-acquisition-cost-data' => ['SI00379', 'view'],
    'GET finance-acquisition-cost-detail' => ['SI00379', 'view'],
    'POST finance-acquisition-cost-submit' => ['SI00379', 'edit'],
    'POST finance-acquisition-cost-upload-receipt' => ['SI00379', 'edit'],
    'POST finance-acquisition-cost-action' => ['SI00379', 'approve'],

    // Finance: Settlement
    'GET finance-settlement-approval' => ['SI00380', 'view'],
    'GET finance-settlement-cost' => ['SI00277', 'view'],
    'GET finance-settlement-detail' => [
        ['SI00380', 'view'],
        ['SI00277', 'view'],
    ],
    'POST finance-settlement-action' => [
        ['SI00380', 'approve'],
        ['SI00277', 'approve'],
    ],
    'POST partner-pickup-finish-settlement' => ['SI00277', 'create'],
];
