<?php

namespace App\Providers;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;

class CartProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
   
    }
}
