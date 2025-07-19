<?php

namespace App\Repository;

use App\Models\Transaction;

class TransactionRepository
{
    private $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    public function get($params = [])
    {
        $transactions = $this->transaction
            ->when(!empty($params['orders']), function ($query) use ($params) {
                return $query->orderByRaw($params['orders']);
            })->when(!empty($params['search']['status']), function ($query) use ($params) {
                return $query->where('transaction_status', 'like', '%' . $params['search']['status'] . '%');
            })->when(!empty($params['search']['invoice_number']), function ($query) use ($params) {
                return $query->where('invoice_number', 'like', '%' . $params['search']['invoice_number'] . '%');
            })->when(!empty($params['search']['payment_method']), function ($query) use ($params) {
                return $query->where('payment_method', 'like', '%' . $params['search']['payment_method'] . '%');
            })->when(!empty($params['search']['date_from']), function ($query) use ($params) {
                return $query->whereDate('created_at', '>=', $params['search']['date_from']);
            })->when(!empty($params['search']['date_to']), function ($query) use ($params) {
                return $query->whereDate('created_at', '<=', $params['search']['date_to']);
            })->where(!empty($params['search']['total_min']), function ($query) use ($params) {
                return $query->where('total_price', '>=', $params['search']['total_min']);
            })->where(!empty($params['search']['total_max']), function ($query) use ($params) {
                return $query->where('total_price', '<=', $params['search']['total_max']);
            });

        if (!empty($params['page'])) {
            $transactions = $transactions->paginate($params['page']);
        }

        return $transactions;
    }
}