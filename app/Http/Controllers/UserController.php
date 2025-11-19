<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Inventory;
use App\Models\User;
use App\Models\SellerPayment;
use Illuminate\Http\Request;

class UserController extends Controller{
    public function indexCompany(){
        if (auth()->user()->type == 'ADMINISTRADOR') {
            return view('users.company');
        } else  if (auth()->user()->type == 'EMPRESA' ||  auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }      
    }
    public function indexEmployee(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO') {  
            $inventories = Inventory::where('status', 1)->get();
            return view('users.employee', compact('inventories'));
        }   
        return redirect()->route('indexStore');
    }
    public function indexClient(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' ||  auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return view('users.client');
        } else {
            return redirect()->route('storeIndex');
        }   
    }
    public function indexSellerPayment($slug){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'VENDEDOR') {
            $seller = User::where('slug', $slug)->first();
            $total = SellerPayment::where('status', 'PENDIENTE')->sum('total_payment');
            return view('users.sellerPayment', compact('seller','total'));
        } else  if (  auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }   
    }
}
