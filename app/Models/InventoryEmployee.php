<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryEmployee extends Model
{
    use HasFactory;
    public function company(){
        return $this->belongsTo(User::class, 'id_company');
    }
    public function employee(){
        return $this->belongsTo(User::class, 'id_employee');
    }
    public function inventory(){
        return $this->belongsTo(Inventory::class, 'id_inventory');
    }
    protected $fillable = ['id_company','id_employee','id_inventory'];
}
 