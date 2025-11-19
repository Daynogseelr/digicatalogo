<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;
    public function employees(){
        return $this->hasMany(Employee::class, 'id_employee');
    }
    public function inventoryEmployees(){
        return $this->hasMany(InventoryEmployee::class, 'id_employee');
    }
    public function companies(){
        return $this->hasMany(Employee::class, 'id_company');
    }
    public function company_carts(){
        return $this->hasMany(Cart::class, 'id_company');
    }
    public function client_carts(){
        return $this->hasMany(Cart::class, 'id_client');
    }
    public function bills(){
        return $this->hasMany(Bill::class, 'id_client');
    }
    public function stocks(){
        return $this->hasMany(Stock::class, 'id_user');
    }
    public function seller()
    {
        return $this->hasMany(Bill::class, 'id_seller');
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['slug','name','last_name','nationality','ci','phone','state','city','postal_zone','email','direction','password','type','logo','status'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
