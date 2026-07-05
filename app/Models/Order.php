<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable=['user_id','order_number','sub_total','quantity','delivery_charge','status','total_amount','first_name','last_name','country','post_code','address1','address2','phone','email','payment_method','payment_status','shipping_id','shipping_destination_id','shipping_destination_label','shipping_courier','shipping_service','shipping_description','shipping_etd','shipping_cost','qris_history_id','qris_string','qris_original_amount','qris_final_amount','qris_expiry_time','coupon','paymentku_transaction_id','paymentku_qr_string','paymentku_original_amount','paymentku_final_amount','paymentku_expired_at'];

    protected $casts = [
        'shipping_cost' => 'float',
        'qris_original_amount' => 'float',
        'qris_final_amount' => 'float',
        'qris_expiry_time' => 'datetime',
        'paymentku_original_amount' => 'float',
        'paymentku_final_amount' => 'float',
        'paymentku_expired_at' => 'datetime',
    ];

    public function cart_info(){
        return $this->hasMany(Cart::class, 'order_id', 'id');
    }
    public static function getAllOrder($id){
        return Order::with('cart_info')->find($id);
    }
    public static function countActiveOrder(){
        $data=Order::count();
        if($data){
            return $data;
        }
        return 0;
    }
    public function cart(){
        return $this->hasMany(Cart::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getResolvedShippingCostAttribute()
    {
        return (float) ($this->shipping_cost ?? 0);
    }

    public function getResolvedShippingLabelAttribute()
    {
        if ($this->shipping_courier) {
            return strtolower((string) $this->shipping_courier) === 'lion'
                ? 'Lion Parcel'
                : strtoupper((string) $this->shipping_courier);
        }

        return '-';
    }

    public function getResolvedShippingDestinationAttribute()
    {
        return $this->shipping_destination_label ?: '-';
    }

}
