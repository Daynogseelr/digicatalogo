<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['code','code2','code3','code4','name',
    'description','cost','utility','price','stock_min','url1','url2','url3',
    'serial','stock','type','code_fraction','name_fraction','equivalence_fraction','price_fraction','status'];

    public function scopeSearch($query, $scope=''){
        return $query->where('name','like',"%$scope%");
    }
    public function stocks(){
        return $this->hasMany(Stock::class, 'id_product');
    }
    public function productI(){
        return $this->hasMany(ProductIntegral::class, 'id_productI');
    }
    public function latestStock() {
        return $this->hasOne(Stock::class, 'id_product')->latest();
    }
}


