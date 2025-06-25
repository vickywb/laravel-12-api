<?php

namespace App\Repository;

use App\Models\Order;

class OrderRepository
{
    private $order;

    public function __construct(Order $order) {
        $this->order = $order;
    }

    public function get($params = [])
    {
        $orders = $this->order
            ->when(!empty($params['order']), function ($query) use ($params) {
                return $query->orderByRaw($params['order']);
            })
            ->when(!empty($params['search']['status']), function ($query) use ($params) {
                return $query->where('order_status', 'LIKE', '%' . $params['search']['status']  . '%');
            });

        if (!empty($params['page'])) {
            return $orders->paginate($params['page']);
        }

        return $orders->get();
    }
}