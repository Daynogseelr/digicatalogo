<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;;

class ServicePayment extends Model
{
    use HasFactory;
    
    protected $fillable = ['id_seller','id_technician','dateStart','dateEnd','percent','amount','status'];
    public function seller(){
        return $this->belongsTo(User::class, 'id_seller');
    }
    public function technician(){
        return $this->belongsTo(User::class, 'id_technician');
    }
    public function serviceDetails(){
        return $this->hasMany(ServiceDetail::class, 'id_servicePayment');
    }
}
