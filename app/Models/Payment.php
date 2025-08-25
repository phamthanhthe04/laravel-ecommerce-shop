<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'amount',
        'payment_date',
        'payment_method',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationship với Order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // Scope để lọc theo phương thức thanh toán
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Scope để lọc theo trạng thái
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
