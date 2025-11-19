<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{

    use HasFactory;
    
    protected $fillable = ['id_user','id_inventory','codeBill','name','date','total'];

    public function serials(){
        return $this->hasMany(Serial::class, 'id_shopping');
    }
    public function inventory(){
        return $this->belongsTo(Inventory::class, 'id_inventory');
    }
    public function user(){
        return $this->belongsTo(User::class, 'id_user');
    }
}
