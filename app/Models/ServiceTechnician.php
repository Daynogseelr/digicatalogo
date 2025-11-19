<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceTechnician extends Model
{
    use HasFactory;
    protected $fillable = ['id_service','id_technician','percent'];
    public function service(){
        return $this->belongsTo(Service::class, 'id_service');
    }
    public function technician(){
        return $this->belongsTo(User::class, 'id_technician');
    }
}
