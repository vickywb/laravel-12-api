<?php

namespace App\Repository;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

class CategoryRepository
{
    private $category;
    private $cacheKey = 'categories';

    public function __construct(Category $category) {
        $this->category = $category;
    }

    public function get($params = [])
    {
        $cacheKey = $this->generateCacheKey($params);

        return Cache::tags(['categories'])->rememberForever($cacheKey, function () use ($params) {
            $categories = $this->category
                ->when(!empty($params['search']['name']), function ($query) use ($params) {
                    return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
                });

            if (!empty($params['page'])) {
                return $categories->paginate($params['page']);
            }

            return $categories->get();
        });
    }

    public function find($id)
    {
        return Cache::tags(['categories'])->rememberForever("category_{$id}", function () use ($id) {
            return $this->category->find($id);
        });
    }

    public function store(Category $category)
    {
        $category->save();
        $this->clearCache();
        return $category;
    }

    public function update(Category $category)
    {
        $category->update();
        $this->clearCache();
        return $category;
    }

    public function delete(Category $category)
    {
        $category->delete();
        $this->clearCache();
        return $category;
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
