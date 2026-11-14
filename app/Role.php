<?php

namespace App;

use Spatie\Permission\Exceptions\RoleAlreadyExists;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $fillable = ['name', 'guard_name', 'department'];

    /**
     * Department-aware role creation. Spatie's default checks name+guard only,
     * which would reject the same role name in a different department.
     */
    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);
        $department = $attributes['department'] ?? null;

        $query = static::where('name', $attributes['name'])
            ->where('guard_name', $attributes['guard_name']);

        if ($department === null) {
            $query->whereNull('department');
        } else {
            $query->where('department', $department);
        }

        if ($query->first()) {
            throw RoleAlreadyExists::create($attributes['name'], $attributes['guard_name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * Department-aware firstOrCreate. When no department is supplied, only a
     * global role is considered so seeders and legacy code stay deterministic.
     */
    public static function firstOrCreate(array $attributes = [], array $values = [])
    {
        $attributes['guard_name'] = $attributes['guard_name'] ?? Guard::getDefaultName(static::class);
        $department = array_key_exists('department', $attributes)
            ? $attributes['department']
            : (array_key_exists('department', $values) ? $values['department'] : null);

        $query = static::where('name', $attributes['name'])
            ->where('guard_name', $attributes['guard_name']);

        if ($department === null) {
            $query->whereNull('department');
        } else {
            $query->where('department', $department);
        }

        $role = $query->first();

        if ($role) {
            return $role;
        }

        return static::create(array_merge($attributes, $values));
    }
}
