<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Serial extends Model
{
    use HasFactory;
    
    protected $fillable = ['id_shopping','id_product','serial','status'];
    public function shopping(){
        return $this->belongsTo(Shopping::class, 'id_shopping');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id_product');
    }
}
