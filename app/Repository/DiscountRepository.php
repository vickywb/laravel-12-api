<?php

namespace App\Repository;

use App\Models\Discount;

class DiscountRepository
{
    protected $discount;

    public function __construct(Discount $discount)
    {
        $this->discount = $discount;
    }

    public function get($params = [])
    {
        $discounts = $this->discount
            ->when(!empty($params['search']['code']), function ($query) use ($params) {
                return $query->where('code', 'LIKE', '%' . $params['search']['code'] . '%');
            });

        if (!empty($params['page'])) {
            return $discounts->paginate($params['page']);
        }

        return $discounts->get();
    }

    public function store(Discount $discount)
    {
        $discount->save();

        return $discount;
    }
}