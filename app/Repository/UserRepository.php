<?php

namespace App\Repository;

use App\Models\User;

class UserRepository
{
    private $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function get($params = [])
    {
        $user = $this->user
            ->when(!empty($params['search']['name']), function($query) use ($params) {
                return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
            });

        if (!empty($params['page'])) {
            return $user->paginate($params['page']);
        }

        return $user;
    }

    public function findByColumn($value, $column)
    {
        $this->user->where($column, $value)->first();
    }

    public function store(User $user)
    {
        $user->save();

        return $user;
    }

}