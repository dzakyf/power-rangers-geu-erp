<?php

namespace App\Services\Approval;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApprovalAuthorizationService
{
    /**
     * Wildcard admin = role/permission name containing "admin" or the
     * literal wildcard permission "*". We intentionally do not match
     * users.username: a username containing "admin" is not an authority.
     */
    public function isWildcardAdmin($user): bool
    {
        if (!$user) {
            return false;
        }

        if (method_exists($user, 'getRoleNames')) {
            foreach ($user->getRoleNames() as $role) {
                if ($this->matchesAdminName((string) $role)) {
                    return true;
                }
            }
        }

        if (method_exists($user, 'getAllPermissions')) {
            foreach ($user->getAllPermissions() as $permission) {
                if ($this->matchesAdminName((string) $permission->name)) {
                    return true;
                }
            }
        }

        // Legacy HCGA admin flags. Kept for compatibility with existing users.
        $priv = strtolower(trim((string) ($user->Priv_ID ?? '')));
        $mainPriv = strtolower(trim((string) ($user->MainPriv ?? '')));
        if (strpos($priv, 'adm') !== false || strpos($mainPriv, 'admin') !== false) {
            return true;
        }

        return false;
    }

    public function assertCanReleaseClaim($user): void
    {
        if (!$this->isWildcardAdmin($user)) {
            throw new AuthorizationException('Hanya wildcard admin yang dapat melepas claim.');
        }
    }

    public function assertCanGrantSelfApproval($user): void
    {
        if (!$this->isWildcardAdmin($user)) {
            throw new AuthorizationException('Hanya wildcard admin yang dapat memberi exception self-approval.');
        }
    }

    public function wildcardAdminUserIds(): array
    {
        $ids = [];

        $roleIds = DB::table('roles')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%admin%'])
                    ->orWhereRaw('UPPER(name) = ?', ['ADM']);
            })
            ->pluck('id')
            ->all();

        if (!empty($roleIds)) {
            $ids = array_merge($ids, DB::table('model_has_roles')
                ->whereIn('role_id', $roleIds)
                ->pluck('model_id')
                ->all());
        }

        $permissionIds = DB::table('permissions')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) LIKE ?', ['%admin%'])
                    ->orWhere('name', '*');
            })
            ->pluck('id')
            ->all();

        if (!empty($permissionIds)) {
            $ids = array_merge($ids, DB::table('model_has_permissions')
                ->whereIn('permission_id', $permissionIds)
                ->pluck('model_id')
                ->all());
        }

        // Legacy admin privilege used by older HCGA screens.
        $ids = array_merge($ids, DB::table('users')
            ->where('Status', 'ACTIVE')
            ->where(function ($query) {
                $query->where('Priv_ID', 'like', '%ADM%')
                    ->orWhere('MainPriv', 'like', '%ADMIN%');
            })
            ->pluck('id')
            ->all());

        return array_values(array_unique(array_map('intval', $ids)));
    }

    private function matchesAdminName(string $name): bool
    {
        $name = Str::lower(trim($name));

        return $name === '*' || $name === 'adm' || Str::contains($name, 'admin');
    }
}