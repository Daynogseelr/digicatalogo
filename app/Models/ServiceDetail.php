<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceDetail extends Model
{
    use HasFactory;
    protected $fillable = ['id_service','id_product','id_servicePayment','procedure','priceU','quantity','price','type','mode'];
    public function servicePayment(){
        return $this->belongsTo(ServicePayment::class, 'id_servicePayment');
    }
    public function service(){
        return $this->belongsTo(Service::class, 'id_service');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
   
}
