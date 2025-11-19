<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Closure extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_seller',
        'id_bill_first',
        'id_bill_last',
        'id_bill_payment_first',
        'id_bill_payment_last',
        'id_repayment_first',
        'id_repayment_last',
        'bill_amount',
        'payment_amount',
        'repayment_amount',
        'small_box_amount',
        'type'
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'id_seller');
    }
}
