<?php

namespace App\Repository;

use App\Models\Product;

class ProductRepository
{
    private $product;

    public function __construct(Product $product) {
        $this->product = $product;
    }

    public function get($params = [])
    {
        $products = $this->product
            ->when(!empty($params['search']['name']), function ($query) use ($params) {
                 return $query->where('name', 'LIKE', '%' . $params['search']['name'] . '%');
            })
            ->when(!empty($params['order']), function ($query) use ($params) {
                return $query->orderByRaw($params['order']);
            })
            ->when(!empty($params['search']['category_name']), function ($query) use ($params) {
                return $query->whereHas('category', function ($query) use ($params) {
                 return $query->where('name', 'LIKE', '%' . $params['search']['category_name'] . '%');
                });
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
    }

    public function store(Product $product)
    {
        $product->save();

        return $product;
    }
}