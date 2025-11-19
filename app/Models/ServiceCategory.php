<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategory extends Model
{
    use HasFactory;
    protected $fillable = ['id_company','name','brand','model','serial','status'];

    public function company(){
        return $this->belongsTo(User::class, 'id_company');
    }
}
