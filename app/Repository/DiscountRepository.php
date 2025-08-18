<?php

namespace App\Repository;

use App\Models\Discount;
use Illuminate\Support\Facades\Cache;

class DiscountRepository
{
    protected $discount;
    private $cacheKey = 'discounts';

    public function __construct(Discount $discount)
    {
        $this->discount = $discount;
    }

    public function get($params = [])
    {
        $cacheKey = $this->generateCacheKey($params);

        return Cache::tags(['discounts'])->rememberForever($cacheKey, function () use ($params) {
            $discounts = $this->discount
                ->when(!empty($params['search']['code']), function ($query) use ($params) {
                    return $query->where('code', 'LIKE', '%' . $params['search']['code'] . '%');
                })
                ->when(!empty($params['search']['active']), function ($query) use ($params) {
                return $query->where('start_at', '<=', now())->where('end_at', '>=', now());
            })
            ->when(!empty($params['search']['expired']), function ($query) use ($params) {
                return $query->where('end_at', '<', now());
            })
            ->when(!empty($params['search']['upcoming']), function ($query) use ($params) {
                return $query->where('start_at', '>', now());
            });

            if (!empty($params['page'])) {
                return $discounts->paginate($params['page']);
            }

            return $discounts->get();
        });
    }

    public function find($id)
    {
        return Cache::tags(['discounts'])->rememberForever("discount_{$id}", function () use ($id) {
            return $this->discount->find($id);
        });
    }

    public function store(Discount $discount)
    {
        $discount->save();
        $this->clearCache();
        return $discount;
    }

    public function update(Discount $discount)
    {
        $discount->update();
        $this->clearCache();
        return $discount;
    }

    public function delete(Discount $discount)
    {
        $discount->delete();
        $this->clearCache();
        return $discount;
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