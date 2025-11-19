<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\AddCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\Employee;
use App\Models\Dolar;
use App\Models\Bill;
use App\Models\Shopping; // Add this line
use App\Models\Service; // Add this line
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Agrega esta línea

class DashboardController extends Controller{
     public function index(){
        $countCategory = Category::where('status', 1)->get()->count();
        $countProduct = Product::get()->count();
        $countClient = User::where('status', 1)->where('type', 'CLIENTE')->get()->count();
        $countBill = Bill::where('status','!=', 0)->get()->count();
        $totalBilling = Bill::sum('net_amount'); // Calculate total billing
        $totalPurchases = Shopping::sum('total'); // Calculate total purchases
        $totalServices = Service::sum('price'); // Calculate total services
        return view('dashboard', compact('countCategory','countProduct','countClient','countBill', 'totalBilling', 'totalPurchases', 'totalServices'));
    }
     
}
