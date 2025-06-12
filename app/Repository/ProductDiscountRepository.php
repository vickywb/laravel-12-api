<?php

namespace App\Repository;

use App\Models\ProductDiscount;

class ProductDiscountRepository
{
    protected $productDiscount;

    public function __construct(ProductDiscount $productDiscount)
    {
        $this->productDiscount = $productDiscount;
    }

    public function get($params = [])
    {
        $productDiscounts = $this->productDiscount
            ->when(!empty($params['with']), function ($query) use ($params) {
                return $query->with($params['with']);
            })
            ->when(!empty($params['search']['product_name']), function ($query) use ($params) {
                return $query->whereHas('product', function ($query) use ($params) {
                    $query->where('product_name', 'LIKE', '%' . $params['search']['product_name'] . '%');
                });
            });

        if (!empty($params['page'])) {
            return $productDiscounts->paginate($params['page']);
        }

        return $productDiscounts->get();
    }

    public function store(ProductDiscount $productDiscount)
    {
        $productDiscount->save();

        return $productDiscount;
    }
}