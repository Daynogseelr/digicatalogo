<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    public function company(){
        return $this->belongsTo(User::class, 'id');
    }
    public function employee(){
        return $this->belongsTo(User::class, 'id');
    }
    protected $fillable = ['id_company','id_employee'];
}
