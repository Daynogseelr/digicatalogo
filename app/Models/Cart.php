<?php

namespace App\Models;
use Illuminate\Support\Facades\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    public function detaill_carts(){
        return $this->hasMany(DetaillCart::class, 'id_cart');
    }
    public function client(){
        return $this->belongsTo(User::class, 'id_client');
    }
    protected $fillable = ['status','id_client','id_company','retiro','total','order_date'];
    public function quantity_of_products(){
        $total = $this->detaill_carts->sum('quantity');
        return $total;
    }
    public function total_price(){
        $total = 0;
        foreach ($this->detaill_carts as $key => $detaill_cart){
            $total += $detaill_cart->price * $detaill_cart->quantity;
        }
        return $total;
    }
   

}
