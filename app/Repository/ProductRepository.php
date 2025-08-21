<?php

namespace App\Repository;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private $product;
    private $cacheTTL = 1800; // 30 minutes

    public function __construct(Product $product) {
        $this->product = $product;
    }

    public function get($params = [])
    {
        $cacheKey = $this->generateCacheKey($params);

        return Cache::tags(['products'])->remember($cacheKey, $this->cacheTTL, function () use ($params) {
            $products = $this->product
                ->when(!empty($params['search']['name']), function ($query) use ($params) {
                    return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%')
                    ->orWhereHas('category', function ($query) use ($params) {
                        return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
                    });
                })
                ->when(!empty($params['order']), function ($query) use ($params) {
                    return $query->orderByRaw($params['order']);
                })
                ->when(!empty($params['order_desc']), function ($query) use ($params) {
                    return $query->orderByDesc($params['order_desc']);
                })
                ->when(!empty($params['whereHas']), function ($query) use ($params) {
                    return $query->whereHas($params['whereHas']);
                })
                ->when(!empty($params['with']), function ($query) use ($params) {
                    return $query->with($params['with']);
                });

            if (!empty($params['page'])) {
                return $products->paginate($params['page']);
            }

            return $products->get();
        });
    }

    private function generateCacheKey(array $params)
    {
        return 'products_' . md5(json_encode($params));
    }

    public function store(Product $product)
    {
        $product->save();
        $this->clearCache();
        return $product;
    }

    public function update(Product $product, array $data)
    {
        $product->update($data);
        $this->clearCache();
        return $product;
    }

    public function delete(Product $product)
    {
        $product->delete();
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::tags(['products'])->flush();
    }
}
