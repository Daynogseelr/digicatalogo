<?php

namespace App\Http\Controllers;
use Illuminate\support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;

class ClientController extends Controller{
    public function indexClient(){
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR') {
            if(request()->ajax()) {
                return datatables()->of(User::select('*')->where('type', 'CLIENTE'))
                ->addColumn('action', 'users.client-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
            return view('index');
        } else  if (auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }
    }
    public function storeClient(Request $request){  
        $clientId = $request->id;
        if ($clientId == NULL || $clientId == '') {
            $request->validate([
                'name' => 'required|min:2|max:20|string',
                'last_name' => 'required|min:2|max:20|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci',
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'direction'  => 'required|min:5|max:100|string',
                'email'  => 'required|email|min:6|max:40|unique:users,email',
                'password'  => 'required|min:8|max:20',
            ]);   
        } else {
            $user = User::find($request-> id);
            if (! $user) {
                abort(404);
            }
            $request->validate([
                'name' => 'required|min:2|max:20|string',
                'last_name' => 'required|min:2|max:20|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci,'. $user->id,
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'direction'  => 'required|min:5|max:100|string',
                'email'  => 'required|email|min:6|max:40|unique:users,email,'. $user->id,
                'password'  => 'min:8|max:20',
            ]);   
        }
        if ($request->password == 'PASSWORD') {
            $client   =   User::updateOrCreate(
                [
                 'id' => $clientId
                ],
                [
                'name' => $request->name, 
                'last_name' => $request->last_name,
                'nationality' => $request->nationality, 
                'ci' => $request->ci, 
                'phone' => $request->phone,
                'email' => $request->email, 
                'status' =>'1', 
                'type' =>'CLIENTE', 
                'direction' => $request->direction
                ]);    
        } else {
            $client   =   User::updateOrCreate(
                [
                 'id' => $clientId
                ],
                [
                'name' => $request->name, 
                'last_name' => $request->last_name,
                'nationality' => $request->nationality, 
                'ci' => $request->ci, 
                'phone' => $request->phone,
                'email' => $request->email, 
                'password' => Hash::make($request->password),
                'status' =>'1', 
                'type' =>'CLIENTE', 
                'direction' => $request->direction
                ]);  
        }                 
        return Response()->json($client);
    }
    public function editClient(Request $request){   
        $where = array('id' => $request->id);
        $client  = User::where($where)->first();
        return Response()->json($client);
    }
 
    public function destroyClient(Request $request){
        $client = User::where('id',$request->id)->delete(); 
        return Response()->json($client);
    }
    public function statusClient(Request $request){ 
        $client = User::find($request-> id);
        if ( $client->status == '1') {
            $client->update(['status' => '0']); 
        } else {
            $client->update(['status' => '1']); 
        } 
        return Response()->json($client);   
    }
}
