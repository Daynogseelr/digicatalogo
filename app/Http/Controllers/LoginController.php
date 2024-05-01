<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Cart;
use Illuminate\Validation\ValidationException;
use Illuminate\support\Facades\Hash;
use Illuminate\support\Facades\Auth;
use Illuminate\Http\Request;


class LoginController extends Controller{
    public function index(){
        $companies = User::select('logo')->where('status','1')->where('type','EMPRESA')->get(); 
        return view('login.login', compact('companies'));
    }
    public function registerIndex(){
        $companies = User::select('logo')->where('status','1')->where('type','EMPRESA')->get(); 
        return view('login.register', compact('companies'));
    }
    public function login(Request $request){
        $request->validate([
            'email'  => 'required|email|min:6|max:40',
            'password'  => 'required|min:8|max:20',
        ]);
        $credentials = [
            "email" => $request->email,
            "password" => $request->password
        ];
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $companies = User::select('id')->where('type','EMPRESA')->where('status',1)->get();
            foreach ($companies as $company) {
                $cart   =   Cart::Create([
                    'id_client' =>  auth()->id(), 
                    'id_company' =>  $company->id, 
                ]); 
            }
           
            return redirect()->intended(route('dashboard'));
        }else {
            throw ValidationException::withMessages([
                'email' => 'Estas credenciales no coinciden con los registros',
                'password' => 'Estas credenciales no coinciden con los registros'
            ]);   
            return redirect('login');
        }
    }
    public function registerClient(Request $request){
        $request->validate([
            'name' => 'required|min:2|max:20|string',
            'last_name'  => 'required|min:2|max:20|string',
            'nationality'  => 'required',
            'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci',
            'phone'  => 'required|numeric|min:1000000000|max:99999999999',
            'direction'  => 'required|min:5|max:100|string',
            'email'  => 'required|email|min:6|max:40|unique:users,email',
            'password'  => 'required|min:8|max:20',
        ]);   
        $user = new User();
        $user -> name = $request -> name;
        $user -> last_name = $request -> last_name;
        $user -> nationality = $request -> nationality;
        $user -> ci = $request -> ci;
        $user -> phone = $request -> phone;
        $user -> email = $request -> email;
        $user -> direction = $request -> direction;
        $user -> password = Hash::make($request -> password);
        $user -> type = 'CLIENTE'; 
        $user -> status = 1; 
        $user -> save();
        Auth::login($user);
        $companies = User::select('id')->where('type','EMPRESA')->where('status',1)->get();
        foreach ($companies as $company) {
            $cart   =   Cart::Create([
                'id_client' =>  auth()->id(), 
                'id_company' =>  $company->id, 
            ]); 
        }
        return redirect(route('dashboard'));
    }
    public function logout(Request $request){
        $cart = Cart::where('id_client',auth()->id())->where('status','ACTIVO')->delete();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('login'));
    }
}
