<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'tracking_token', 'customer_name', 'table_number', 'delivery_address', 'order_type', 'dining_mode', 'payment_method', 'payment_status', 'status', 'notes', 'total', 'user_id'])]
class Order extends Model
{
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
