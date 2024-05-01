<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddCategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DetaillCartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
Route::group(['middleware' => 'guest'], function () {
    Route::get('/', function () {
        return view('login.login');
    });
    Route::get('logeo', 'App\Http\Controllers\LoginController@login')->name('logeo');
    Route::get('login', 'App\Http\Controllers\LoginController@index')->name('login');
    Route::get('register', 'App\Http\Controllers\LoginController@registerIndex')->name('registerIndex');
    Route::post('registerClient', 'App\Http\Controllers\LoginController@registerClient')->name('registerClient');
});
Route::group(['middleware' => 'auth'], function () {
    Route::get('dashboard', 'App\Http\Controllers\DashboardController@index')->name('dashboard');
    Route::get('ajax-crud-datatableProductDashboard', [DashboardController::class, 'ajaxProductDashboard']);
    Route::get('logout', 'App\Http\Controllers\LoginController@logout')->name('logout');

    Route::get('indexCompany', 'App\Http\Controllers\userController@indexCompany')->name('indexCompany');
    Route::get('ajax-crud-datatable', [CompanyController::class, 'index']);
    Route::post('store', [CompanyController::class, 'store']);
    Route::post('edit', [CompanyController::class, 'edit']);
    Route::post('delete', [CompanyController::class, 'destroy']);
    Route::get('statusCompany', [CompanyController::class, 'statusCompany']);

    Route::get('indexEmployee', 'App\Http\Controllers\userController@indexEmployee')->name('indexEmployee');
    Route::get('ajax-crud-datatableEmployee', [EmployeeController::class, 'indexEmployee']);
    Route::post('storeEmployee', [EmployeeController::class, 'storeEmployee']);
    Route::post('editEmployee', [EmployeeController::class, 'editEmployee']);
    Route::post('deleteEmployee', [EmployeeController::class, 'destroyEmployee']);
    Route::get('statusEmployee', [EmployeeController::class, 'statusEmployee']);

    Route::get('indexClient', 'App\Http\Controllers\userController@indexClient')->name('indexClient');
    Route::get('ajax-crud-datatableClient', [ClientController::class, 'indexClient']);
    Route::post('storeClient', [ClientController::class, 'storeClient']);
    Route::post('editClient', [ClientController::class, 'editClient']);
    Route::post('deleteClient', [ClientController::class, 'destroyClient']);
    Route::get('statusClient', [ClientController::class, 'statusClient']);

    Route::get('indexCategory', 'App\Http\Controllers\CategoryController@indexCategory')->name('indexCategory');
    Route::get('ajax-crud-datatableCategory', [CategoryController::class, 'ajaxCategory']);
    Route::post('storeCategory', [CategoryController::class, 'storeCategory']);
    Route::post('editCategory', [CategoryController::class, 'editCategory']);
    Route::post('deleteCategory', [CategoryController::class, 'destroyCategory']);
    Route::get('statusCategory', [CategoryController::class, 'statusCategory']);

    Route::get('indexProduct', 'App\Http\Controllers\ProductController@indexProduct')->name('indexProduct');
    Route::get('ajax-crud-datatableProduct', [ProductController::class, 'ajaxProduct']);
    Route::post('storeProduct', [ProductController::class, 'storeProduct']);
    Route::post('editProduct', [ProductController::class, 'editProduct']);
    Route::post('deleteProduct', [ProductController::class, 'destroyProduct']);
    Route::get('statusProduct', [ProductController::class, 'statusProduct']);

    Route::get('indexAddCategory', 'App\Http\Controllers\AddCategoryController@indexAddCategory')->name('indexAddCategory');
    Route::get('ajax-crud-datatableAddCategory', [AddCategoryController::class, 'ajaxAddCategory']);
    Route::post('storeAddCategory', [AddCategoryController::class, 'storeAddCategory']);
    Route::post('editAddCategory', [AddCategoryController::class, 'editAddCategory']);
    Route::post('deleteAddCategory', [AddCategoryController::class, 'destroyAddCategory']);
    Route::get('statusAddCategory', [AddCategoryController::class, 'statusAddCategory']);

    Route::get('storeIndex', 'App\Http\Controllers\StoreController@storeIndex')->name('storeIndex');
    Route::get('indexStore', 'App\Http\Controllers\StoreController@indexStore')->name('indexStore');
    Route::get('indexCart', 'App\Http\Controllers\StoreController@indexCart')->name('indexCart');
    Route::post('storeCart', [CartController::class, 'storeCart'])->name('storeCart');
    Route::post('mostrarProduct', [CartController::class, 'mostrarProduct']);

    Route::post('addCart', [DetaillCartController::class, 'addCart']);
    Route::post('mostrarCart', [DetaillCartController::class, 'mostrarCart']);
    Route::post('summaryCart', [DetaillCartController::class, 'summaryCart']);
    Route::post('updateCart', [DetaillCartController::class, 'updateCart']);
    Route::post('deleteCart', [DetaillCartController::class, 'deleteCart']);
    Route::post('quantityCart', [DetaillCartController::class, 'quantityCart']);
    Route::post('buscarProduct', [DetaillCartController::class, 'buscarProduct']);

    Route::get('indexOrder', 'App\Http\Controllers\OrderController@indexOrder')->name('indexOrder');
    Route::get('ajax-crud-datatableOrder', [OrderController::class, 'ajaxOrder']);
    Route::post('mostrarOrder', [OrderController::class, 'mostrarOrder']);
    Route::post('summaryOrder', [OrderController::class, 'summaryOrder']);
    Route::post('updateOrder', [OrderController::class, 'updateOrder']);
    Route::post('deleteOrder', [OrderController::class, 'deleteOrder']);
    Route::post('statusOrder', [OrderController::class, 'statusOrder']);

    Route::get('indexOrderClient', 'App\Http\Controllers\OrderController@indexOrderClient')->name('indexOrderClient');
    Route::get('ajax-crud-datatableOrderClient', [OrderController::class, 'ajaxOrderClient']);

    Route::get('indexProfile', 'App\Http\Controllers\ProfileController@indexProfile')->name('indexProfile');
    Route::post('updateProfile', [ProfileController::class, 'updateProfile'])->name('updateProfile');
    Route::post('updateProfileFile', [ProfileController::class, 'updateProfileFile'])->name('updateProfileFile');
    Route::post('passwordProfile', [ProfileController::class, 'passwordProfile'])->name('passwordProfile');
    
});