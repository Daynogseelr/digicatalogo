<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['id_company','name','description','url1','url2','url3','price','status'];

    public function scopeSearch($query, $scope=''){
        return $query->where('name','like',"%$scope%");
    }
    
}


