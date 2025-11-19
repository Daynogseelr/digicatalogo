<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repayment extends Model
{
    use HasFactory;
    protected $fillable = ['id_bill','id_product','id_company','id_seller','id_client', 'id_inventory','id_currency','rate','rate_official', 'abbr_repayment', 'abbr_official', 'abbr_principal','code','quantity','amount','method','status'];
    public function bill(){
        return $this->belongsTo(Bill::class, 'id_bill');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
    public function seller(){
        return $this->belongsTo(User::class, 'id_seller');
    }
    public function client(){
        return $this->belongsTo(User::class, 'id_client');
    }
     public function company(){
        return $this->belongsTo(User::class, 'id_company');
    }
    public function inventory(){
        return $this->belongsTo(Inventory::class, 'id_inventory');
    }
     public function currency(){
        return $this->belongsTo(Currency::class, 'id_currency');
    }
}
