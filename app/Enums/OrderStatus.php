<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING   = 'pending';
    case PAID      = 'paid';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
