<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_user',
        'id_inventory',
        'description',
        'amount_lost',
        'amount_profit',
        'amount',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'id_inventory_adjustment');
    }
}