<?php

namespace App\Services\Rbac;

use App\Role;
use Illuminate\Support\Collection;

class RoleResolver
{
    public function resolve(?string $name, ?string $department = null, string $guard = 'web'): ?Role
    {
        if ($name === null || trim($name) === '') {
            return null;
        }

        $name = trim($name);
        $department = $department !== null && trim($department) !== '' ? trim($department) : null;

        if ($department !== null) {
            $role = Role::where('name', $name)
                ->where('department', $department)
                ->where('guard_name', $guard)
                ->first();

            if ($role) {
                return $role;
            }
        }

        return Role::where('name', $name)
            ->whereNull('department')
            ->where('guard_name', $guard)
            ->first();
    }

    public function resolveById(int $id): ?Role
    {
        return Role::find($id);
    }

    public function resolveManyByIds(array $ids): Collection
    {
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return collect();
        }

        return Role::whereIn('id', $ids)->get();
    }

    public function label(Role $role): string
    {
        return $role->department
            ? $role->name . ' - ' . $role->department
            : $role->name;
    }
}
