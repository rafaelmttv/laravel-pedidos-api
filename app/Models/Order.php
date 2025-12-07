<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

enum OrderStatus: string {
    case Pending = 'pending';
    case Paid = 'paid';
    case Canceled = 'canceled';
}

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total',
        'status',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'status' => OrderStatus::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recalculateTotal()
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }
}
