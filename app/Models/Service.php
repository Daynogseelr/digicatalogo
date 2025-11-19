<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    protected $fillable = ['id_seller','id_client','id_technician','id_category','id_currency','code','ticker','model','brand','serial','description','solution','price','priceBs','status'];

    public function currency(){
        return $this->belongsTo(Currency::class, 'id_currency');
    }
    public function seller(){
        return $this->belongsTo(User::class, 'id_seller');
    }
    public function client(){
        return $this->belongsTo(User::class, 'id_client');
    }
    public function technician(){
        return $this->belongsTo(User::class, 'id_technician');
    }
    public function id_category(){
        return $this->belongsTo(ServiceCategory::class, 'id_category');
    }
    public function serviceDetails(){
        return $this->hasMany(ServiceDetail::class, 'id_service');
    }
    public function secondary_technicians(){
        return $this->hasMany(ServiceTechnician::class, 'id_service');
    }
}
