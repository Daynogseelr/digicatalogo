<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller{
    public function indexProfile(){
        return view('profile.profile');
    }
    public function updateProfile(Request $request){
        if (auth()->user()->type != 'EMPRESA') {
            $request->validate([
                'name' => 'required|min:2|max:20|string',
                'last_name' => 'required|min:2|max:20|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci,'. auth()->id(),
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'direction'  => 'required|min:5|max:100|string',
                'email'  => 'required|email|min:6|max:40|unique:users,email,'. auth()->id(),
            ]);   
        } else {
            $request->validate([
                'name' => 'required|min:2|max:20|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci,'. auth()->id(),
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'direction'  => 'required|min:5|max:100|string',
                'email'  => 'required|email|min:6|max:40|unique:users,email,'. auth()->id(),
            ]);   
        }
        auth()->user()->update($request->all());
        return back()->withStatus(__('Perfil modificado con exito.'));
    }
    public function updateProfileFile(Request $request){
        $request->validate([
            'logo'   =>  'required|image|mimes:jpg,jpeg,png|max:3000',
        ]);
        if ( auth()->user()->logo != '') {
            unlink(public_path('logos/'.auth()->user()->logo));
        } 
        //Storage::disk('public')->delete('logos/'.$company -> logo); 
        $ruta='logos/';
        $imagen = $request->logo;
        $imagencompany= date('YmdHis').".".$imagen->getClientOriginalExtension();
        $imagen->move($ruta,$imagencompany);
        $url= $imagencompany;
        $company = User::where('id',auth()->id())->first();
        $company->update([
            'logo' =>  $url
        ]);     
        return back()->withStatus(__('Perfil modificado con exito.'));
    }
    public function passwordProfile(Request $request){
        $request->validate([
            'old_password' => ['required', 'min:8'],
            'password' => ['required', 'min:8', 'confirmed', 'different:old_password'],
            'password_confirmation' => ['required', 'min:8'],
        ]);  
        if ( Hash::check($request->old_password, auth()->user()->password)) {       
            auth()->user()->update(['password' => Hash::make($request->get('password'))]);
            return back()->withYesPasswordStatus(__('Contraseña guardada satisfactoriamente.'));
        }

        return back()->withPasswordStatus(__('contraseña actual no coincide.'));
    }
}
