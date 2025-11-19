<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductIntegral extends Model
{
    use HasFactory;
    protected $fillable = ['id_productI','id_product','quantity', 'is_fraction'];
    public function productI(){
        return $this->belongsTo(Product::class, 'id_productI');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
}
