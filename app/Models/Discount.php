<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    public function client(){
        return $this->belongsTo(User::class, 'id_client');
    }
    public function company(){
        return $this->belongsTo(User::class, 'id_company');
    }
    protected $fillable = ['id_client','id_company','discount'];
   
    
}
