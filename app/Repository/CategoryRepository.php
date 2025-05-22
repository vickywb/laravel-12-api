<?php

namespace App\Repository;

use App\Models\Category;

class CategoryRepository
{
    private $category;

    public function __construct(Category $category) {
        $this->category = $category;
    }

    public function get($params = [])
    {
        $categories = $this->category
            ->when(!empty($params['search']['name']), function ($query) use ($params) {
                return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
            });

        if (!empty($params['page'])) {
            return $categories->paginate($params['page']);
        }

        return $categories->get();
    }

    public function store(Category $category)
    {
        $category->save();

        return $category;
    }
}