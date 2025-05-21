<?php

namespace App\Repository;

use App\Models\Role;

class RoleRepository
{
    private $role;

    public function __construct(Role $role) {
        $this->role = $role;
    }

    public function get($params = [])
    {
        $roles = $this->role
            ->when(!empty($params['search']['name']), function($query) use ($params) {
                return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
            });
            
        if (!empty($params['page'])) {
            return $roles->paginate($params['page']);
        }

        return $roles->get();
    }

    public function store(Role $role)
    {
        $role->save();

        return $role;
    }
}