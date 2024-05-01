<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddCategory extends Model
{
    use HasFactory;
    public function company(){
        return $this->belongsTo(User::class, 'id');
    }
    public function category(){
        return $this->belongsTo(Category::class, 'id');
    }
    public function product(){
        return $this->belongsTo(Product::class, 'id');
    }
    protected $fillable = ['id_company','id_category','id_product'];
}
