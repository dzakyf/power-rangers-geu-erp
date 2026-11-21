<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Credential\Http\Controllers\CredentialRoleController;
use App\Modules\Credential\Http\Controllers\CredentialUserController;
use App\Modules\Credential\Http\Controllers\PrivilegeMappingController;

    Route::get('/home-credential', function () {
        return view('home-credential');
    });

    Route::get("/credential-role", [CredentialRoleController::class, "index"]);
    Route::get("/master-credential-role-list", [CredentialRoleController::class, "roleListData"])->name("master.credential.role.list");
    Route::get("/master-credential-role-permission", [CredentialRoleController::class, "rolePermissionsData"])->name("master.credential.role.permission");
    Route::post("/master-credential-role-permission-save", [CredentialRoleController::class, "savePermissions"])->name("master.credential.role.permission.save");
    Route::post("/master-credential-role-create", [CredentialRoleController::class, "createRole"]);
    Route::post("/master-credential-role-update/{id}", [CredentialRoleController::class, "updateRole"]);
    Route::post("/master-credential-role-delete/{id}", [CredentialRoleController::class, "deleteRole"]);
    Route::post("/master-credential-role-grant-all/{id}", [CredentialRoleController::class, "grantAllPermissions"]);
    Route::post("/master-credential-role-revoke-all/{id}", [CredentialRoleController::class, "revokeAllPermissions"]);
    Route::post("/master-credential-role-menu-create", [CredentialRoleController::class, "createMenuItem"]);
    Route::post("/master-credential-role-menu-update", [CredentialRoleController::class, "updateMenuItem"]);
    Route::post("/master-credential-role-menu-delete", [CredentialRoleController::class, "deleteMenuItem"]);
    Route::get("/master-credential-role-menu-tree", [CredentialRoleController::class, "menuTree"])->name("master.credential.role.menu.tree");
    Route::post("/master-credential-role-menu-create-cascade", [CredentialRoleController::class, "createMenuItemCascade"])->name("master.credential.role.menu.create.cascade");

    Route::get("/credential-user-management", [CredentialUserController::class, "index"]);
    Route::get("/credential-create-user", [CredentialUserController::class, "createUser"]);
    Route::get("/master-credential-user-list", [CredentialUserController::class, "userListData"]);
    Route::get("/master-credential-candidate-without-account", [CredentialUserController::class, "candidatesWithoutAccount"]);
    Route::post("/credential-create-candidate-account", [CredentialUserController::class, "createCandidateAccount"]);
    Route::post("/credential-create-user-submit", [CredentialUserController::class, "createUserSubmit"]);
    Route::post("/master-credential-user-update", [CredentialUserController::class, "updateUser"]);
    Route::post("/master-credential-user-delete", [CredentialUserController::class, "deleteUser"]);

    // Privilege & Role Access Mappings
    Route::get("/credential-privilege-mapping", [PrivilegeMappingController::class, "index"]);
    Route::get("/credential-privilege-mapping-data", [PrivilegeMappingController::class, "data"]);
    Route::post("/credential-privilege-mapping-store", [PrivilegeMappingController::class, "store"]);
    Route::post("/credential-privilege-mapping-delete", [PrivilegeMappingController::class, "destroy"]);

    Route::get('/credential-maillog', function () {
        return view('credential.maillog');
    });

    Route::post('/credential-maillog-update', function () {
        return view('credential.maillog-update');
    });

    Route::get('/xml-credential-maillog', function () {
        return view('credential.xml.maillog');
    });


    Route::get('credential-organization', function () {
        return view('credential.organization');
    });
    Route::get('/xml-credential-organization', function () {
        return view('credential.xml.organization');
    });
    Route::post('/credential-organization-update', function () {
        return view('credential.organization-update');
    });
