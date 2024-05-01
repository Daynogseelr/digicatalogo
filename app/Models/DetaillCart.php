<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetaillCart extends Model
{
    use HasFactory;
    public function product(){
        return $this->belongsTo(User::class, 'id_product');
    }
    public function cart(){
        return $this->belongsTo(User::class, 'id_cart');
    }
    protected $fillable = ['quantity','price','id_cart','id_product'];
    
}
