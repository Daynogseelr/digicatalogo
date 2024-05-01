<?php

namespace App\Http\Controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\AddCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Else_;
class AddCategoryController extends Controller{
    public function indexAddCategory(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR') {
            $categories = Category::where('status', '1')->get();
            $products = Product::where('id_company',  auth()->id())->get();
            return view('categories.addCategory', compact('categories','products'));
        } else  if (auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }
    }
    public function ajaxAddCategory(){
        if ( auth()->user()->type == 'ADMINISTRADOR') {
            if(request()->ajax()) {
                return datatables()->of(DB::table('add_categories')
                ->join('categories', 'categories.id','=','add_categories.id_category')
                ->where('categories.status',1)
                ->join('products', 'products.id','=','add_categories.id_product')
                ->where('products.status',1)
                ->select(DB::raw("add_categories.id as id, categories.name as nameCategory, products.name as nameProduct")))
                ->addColumn('action', 'categories.addCategory-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else  if (auth()->user()->type == 'EMPRESA'){
            if(request()->ajax()) {
                return datatables()->of(DB::table('add_categories')
                ->join('categories', 'categories.id','=','add_categories.id_category')
                ->where('categories.status',1)
                ->where('add_categories.id_company',auth()->id())
                ->join('products', 'products.id','=','add_categories.id_product')
                ->where('products.status',1)
                ->select(DB::raw("add_categories.id as id, categories.name as nameCategory, products.name as nameProduct")))
                ->addColumn('action', 'categories.addCategory-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } 
        return view('index');
    }
    public function storeAddCategory(Request $request){  
        $request->validate([
            'id_category' => 'required',
            'id_product'  => 'required'
        ]);  
        foreach ($request->id_product as $id_product) {
            $category = AddCategory::where('id_category',$request->id_category)
            ->where('id_company',auth()->id())
            ->where('id_product',$id_product)
            ->delete();
            $category = AddCategory::Create([
                'id_category' => $request->id_category, 
                'id_product' => $id_product,
                'id_company' =>  auth()->id(),
            ]); 
        }
        return Response()->json($category);
    }
    public function destroyAddCategory(Request $request){
        $category = AddCategory::where('id',$request->id)->delete();
        return Response()->json($category);
    }
}
