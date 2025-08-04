<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->select(['id', 'name','image','input_name','input_others','is_auto']);
    }
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method','method')->select(['id', 'icon']);
    }

    public function items()
    {
       return $this->hasOne(Item::class, 'id', 'item_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function autoTopUpProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }


}
