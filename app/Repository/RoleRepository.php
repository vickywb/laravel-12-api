<?php

namespace App\Repository;

use App\Models\Role;

class RoleRepository
{
    private $role;

    public function __construct(Role $role) {
        $this->role = $role;
    }
}