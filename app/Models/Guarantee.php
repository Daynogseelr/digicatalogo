<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guarantee extends Model
{
    protected $fillable = ['id_product','code','name','serial','description','status'];

    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }

}
