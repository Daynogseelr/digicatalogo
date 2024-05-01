<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller{
    public function indexCompany(){
        if (auth()->user()->type == 'ADMINISTRADOR') {
            return view('users.company');
        } else  if (auth()->user()->type == 'EMPRESA' ||  auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }      
    }
    public function indexEmployee(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR') {
            $companies = User::where('type', 'EMPRESA')->get();
            return view('users.employee', compact('companies'));
        } else  if (  auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }   
    }
    public function indexClient(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR') {
            return view('users.client');
        } else  if (  auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }   
    }
}
