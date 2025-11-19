<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LoginController extends Controller{
    public function index(){
        if (Auth::check()) {
            return redirect()->route('dashboard');
        } else {
            return view('login.login');
        }
    }
    public function login(Request $request){
        $request->validate([
            'email'  => 'required|min:4|max:100',
            'password'  => 'required|min:4|max:20',
        ]);
        $credentials = [
            "email" => $request->email,
            "password" => $request->password
        ];
        if (Auth::attempt($credentials)) {
            if (auth()->user()->status == 1) {
                if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'SUPERVISOR' || auth()->user()->type == 'EMPLEADO') {
                    $request->session()->regenerate();
                    return redirect()->intended(route('dashboard'));
                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    throw ValidationException::withMessages([
                        'password' => 'Acceso Denegado'
                    ]);     
                }
            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                throw ValidationException::withMessages([
                    'password' => 'este usuario esta desactivado'
                ]);     
            }
            return redirect('login');
        }else {
            throw ValidationException::withMessages([
                'password' => 'Estas credenciales no coinciden con los registros'
            ]);   
            return redirect('login');
        }
    }
    public function logout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
