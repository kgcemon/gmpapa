<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'name',
        'email',
        'phone',
        'item_id',
        'quantity',
        'total',
        'customer_data',
        'status',
        'others_data',
        'order_note',
        'payment_method',
        'transaction_id',
        'number',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->select(['id', 'name','image','input_name','input_others','is_auto']);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method','id')->select(['id', 'icon', 'method']);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function autoTopUpProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
