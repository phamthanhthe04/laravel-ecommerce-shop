<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'vnpay_transaction_id',
        'mock_transaction_id',
        'mock_card_last4',
        'payment_date',
        'delivery_name',
        'delivery_phone',
        'delivery_address',
        'delivery_ward',
        'delivery_district',
        'delivery_province',
        'delivery_note'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // Relationship với User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relationship với OrderItems
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relationship với Payment
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Tính tổng tiền từ order items
    public function calculateTotal()
    {
        return $this->orderItems->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    // Lấy địa chỉ giao hàng đầy đủ
    public function getFullDeliveryAddress()
    {
        $address = [];
        
        if ($this->delivery_address) {
            $address[] = $this->delivery_address;
        }
        if ($this->delivery_ward) {
            $address[] = $this->delivery_ward;
        }
        if ($this->delivery_district) {
            $address[] = $this->delivery_district;
        }
        if ($this->delivery_province) {
            $address[] = $this->delivery_province;
        }
        
        return implode(', ', $address);
    }

    // Kiểm tra có thông tin giao hàng hay không
    public function hasDeliveryInfo()
    {
        return !empty($this->delivery_name) && !empty($this->delivery_phone) && !empty($this->delivery_address);
    }

    // Scope để lọc theo status
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
