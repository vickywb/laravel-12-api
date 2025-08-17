<?php

namespace App\Repository;

use App\Models\Role;
use Illuminate\Container\Attributes\Cache;

class RoleRepository
{
    private $role;
    private $roleCache = 'roles';

    public function __construct(Role $role) {
        $this->role = $role;
    }

    public function get($params = [])
    {
        $cacheKey = $this->generateCacheKey($params);

        return Cache::rememberForever($cacheKey, function() use ($params) {
            $roles = $this->role
                ->when(!empty($params['search']['name']), function($query) use ($params) {
                    return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
                });
        });

        if (!empty($params['page'])) {
            return $roles->paginate($params['page']);
        }

        return $roles->get();
    }

    public function store(Role $role)
    {
        $role->save();
        $this->clearCache();
        return $role;
    }

    public function update(Role $role)
    {
        $role->update();
        $this->clearCache();
        return $role;
    }

    public function delete(Role $role)
    {
        $role->delete();
        $this->clearCache();
    }

    private function generateCacheKey(array $params)
    {
        return $this->cacheKey . '_' . md5(json_encode($params));
    }

    public function clearCache()
    {
        Cache::forget($this->cacheKey);
    }

}