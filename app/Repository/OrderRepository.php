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
            ->when(!empty($params['user_id']), function ($query) use ($params) {
                return $query->where('user_id', $params['user_id']);
            })
            ->when(!empty($params['order']), function ($query) use ($params) {
                return $query->orderByRaw($params['order']);
            })
            ->when(!empty($params['with']), function ($query) use ($params) {
                return $query->with($params['with']);
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