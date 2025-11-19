<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    public function employee(){
        return $this->belongsTo(User::class, 'id_employee');
    }
    protected $fillable = ['id_employee','percent','smallBox'];
}
