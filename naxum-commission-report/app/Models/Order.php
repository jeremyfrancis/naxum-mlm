<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_number',
        'purchaser_id',
        'order_date',
    ];

    /**
     * Get the user who purchased this order.
     */
    public function purchaser()
    {
        return $this->belongsTo(User::class, 'purchaser_id');
    }

    /**
     * Get the items in this order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Calculate the total value of this order.
     */
    public function getOrderTotal()
    {
        return $this->items->sum(function ($item) {
            return $item->product ? $item->product->price * $item->quantity : 0;
        });
    }
}
