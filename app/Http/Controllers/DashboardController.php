<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\AddCategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\Employee;
use Illuminate\Http\Request;

class DashboardController extends Controller{
    public function index(){
        if (auth()->user()->type == 'ADMINISTRADOR') {
            $countCategory = Category::where('status', 1)->get()->count();
            $countProduct = Product::get()->count();
            $countClient = User::where('status', 1)->where('type', 'CLIENTE')->get()->count();
            $countOrder = Cart::where('status','!=', 'ACTIVO')->where('status','!=', 'FINALIZADO')->where('status','!=', 'INCONCLUSO')->get()->count();
            return view('dashboard', compact('countCategory','countProduct','countClient','countOrder'));
        } else if (auth()->user()->type == 'EMPRESA') {
            $countCategory = AddCategory::where('id_company', auth()->id())->groupBy('id_category')->count();
            $countProduct = Product::where('id_company', auth()->id())->count();
            $countClient = User::where('status', 1)->where('type', 'CLIENTE')->count();
            $countOrder = Cart::where('status','!=', 'ACTIVO')->where('status','!=', 'FINALIZADO')->where('status','!=', 'INCONCLUSO')
            ->where('id_company',auth()->id())->count();
            return view('dashboard', compact('countCategory','countProduct','countClient','countOrder'));
        } else if (auth()->user()->type == 'EMPLEADO'){
            $id_company = Employee::select('id_company')->where('id_employee',auth()->id())->first();
            $countCategory = AddCategory::where('id_company', $id_company->id_company)->groupBy('id_category')->count();
            $countProduct = Product::where('id_company', $id_company->id_company)->count();
            $countClient = User::where('status', 1)->where('type', 'CLIENTE')->count();
            $countOrder = Cart::where('status','!=', 'ACTIVO')->where('status','!=', 'FINALIZADO')->where('status','!=', 'INCONCLUSO')
            ->where('id_company',$id_company->id_company)->count();
            return view('dashboard', compact('countCategory','countProduct','countClient','countOrder'));
        } else {
            return redirect()->route('storeIndex');
        }
    }
    public function ajaxProductDashboard(){
        if (auth()->user()->type == 'ADMINISTRADOR') {
            if(request()->ajax()) {
                return datatables()->of(Product::select('*'))
                ->addIndexColumn()
                ->make(true);
            }
        } else if (auth()->user()->type == 'EMPRESA') {
            if(request()->ajax()) {
                return datatables()->of(Product::select('*')->where('id_company',auth()->id()))
                ->addIndexColumn()
                ->make(true);
            }
        } else if (auth()->user()->type == 'EMPLEADO'){
            $id_company = Employee::select('id_company')->where('id_employee',auth()->id())->first();
            if(request()->ajax()) {
                return datatables()->of(Product::select('*')->where('id_company',$id_company->id_company))
                ->addIndexColumn()
                ->make(true);
            }
        } else {
            return redirect()->route('storeIndex');
        }
        return view('index');
    }
}
