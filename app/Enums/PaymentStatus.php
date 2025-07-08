<?php

namespace App\Enums;

enum PaymentStatus:string
{
    case UNPAID    = 'pending';
    case PAID      = 'settlement';
    case CAPTURED  = 'capture';
    case EXPIRED   = 'expire';
    case CANCELLED = 'cancel';
    case FAILED    = 'failure';

    public static function fromMidtrans(string $status): self
    {
        return match ($status) {
            'pending'    => self::UNPAID,
            'settlement' => self::PAID,
            'capture'    => self::CAPTURED,
            'expire'     => self::EXPIRED,
            'cancel'     => self::CANCELLED,
            'failure'    => self::FAILED,
            default      => throw new \InvalidArgumentException("Unknown Midtrans status: {$status}"),
        };
    }

    public function isPaid(): bool
    {
        return in_array($this, [self::PAID, self::CAPTURED]);
    }
}