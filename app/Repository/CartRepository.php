<?php

namespace App\Repository;

use App\Models\Cart;

class CartRepository
{
    private $cart;

    public function __construct(Cart $cart) {
        $this->cart = $cart;
    }

    public function get($params = [])
    {
        $carts = $this->cart
            ->when(!empty($params['with']), function ($query) use ($params) {
                return $query->with($params['with']);
            });

        if (!empty($params['page'])) {
            return $carts->paginate($params['page']);
        }

        return $carts->get();
    }

    public function store(Cart $cart)
    {
        $cart->save();

        return $cart;
    }
}