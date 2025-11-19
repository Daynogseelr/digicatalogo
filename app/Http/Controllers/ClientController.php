<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\DataTables\ClientDataTable;
use App\Models\User;
use App\Models\Discount;
use App\Models\Employee;
use App\Models\Seller;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function indexClient(ClientDataTable $dataTable){
        if ( auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'EMPRESA') {
            return $dataTable->render('users.client');
        } 
        return redirect()->route('indexStore');
    }
    public function storeClient(Request $request)
    {
        $clientId = $request->id;
        if ($clientId == NULL || $clientId == '') {
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:users,ci',
                'email' => 'nullable|unique:users,email', // Validate email only if present
            ]);
        } else {
            $user = User::find($request->id);
            if (!$user) {
                abort(404);
            }
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality' => 'required',
                'ci' => 'required|numeric|min:100000|max:9999999999|unique:users,ci,' . $user->id, // Correct unique rule
                'email' => 'nullable|unique:users,email,' . $user->id, // Correct unique rule
            ]);
        }
        $client   =   User::updateOrCreate(
            [
                'id' => $clientId
            ],
            [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'nationality' => $request->nationality,
                'ci' => $request->ci,
                'phone' => $request->phone ?: '00000000000', // Use the null coalescing operator
                'email' => $request->email ?: $request->name . '' . $request->last_name . '' . $request->ci,
                'status' => '1',
                'type' => 'COMPRADOR',
                'direction' => $request->direction ?: 'CARUPANO',
                'password' => '123',
            ]
        );
        return Response()->json($client);
    }
    public function editClient(Request $request)
    {
        $where = array('id' => $request->id);
        $client  = User::where($where)->first();
        return Response()->json($client);
    }

    public function destroyClient(Request $request)
    {
        $client = User::where('id', $request->id)->delete();
        return Response()->json($client);
    }
    public function statusClient(Request $request)
    {
        $client = User::find($request->id);
        if ($client->status == '1') {
            $client->update(['status' => '0']);
        } else {
            $client->update(['status' => '1']);
        }
        return Response()->json($client);
    }
    public function discount(Request $request)
    {
        $where = array('id' => $request->id);
        $client  = User::where($where)->first();
        $discount = Discount::where('id_client', $request->id)
        ->where('id_company', auth()->id())->first();
        return Response()->json([
            'client' => $client,
            'discount' => $discount
        ]);
    }
    public function updatediscount(Request $request)
    {
        $request->validate([
            'discount' => 'required'
        ]);
        $discount = Discount::where('id_client', $request->id)
        ->where('id_company', auth()->id())->first();
        if ($discount) {
            $discount->update(['discount' => $request->discount]);
        } else {
            $discount = Discount::Create([
                'id_client' => $request->id,
                'id_company' => auth()->id(),
                'discount' => $request->discount
            ]);
        }
        return Response()->json([
            'discount' => $discount
        ]);
    }
    public function clientEmployee(Request $request)
    {
        $request->validate([
            'percentMax'  => 'required|numeric|min:1|max:100',
        ]); 
        $user = User::find($request->id_employee);
        if ($user->type != 'CLIENTE') {
            return Response()->json('mal');
        } else {
            $user->update(['type' => $request->type]);
            $company = new Employee();
            $company -> id_company =  auth()->id();
            $company -> id_employee = $request->id_employee;
            $company -> percent = $request->percentMax;
            $company -> save();
            return Response()->json('bien');
        }
    }
    public function clientSeller(Request $request)
    {
        $user = User::find($request->id_seller);
        $request->validate([
            'percent'  => 'required|numeric|min:1|max:100',
            'slug' => 'required|min:2|max:200|string|unique:users,slug,' . $user->id,
        ]);  
        if ($user->type != 'CLIENTE') {
            return Response()->json('mal');
        } else {
            $user->update(['type' => 'VENDEDOR','slug' => $request->slug]);
            $company = new Seller();
            $company -> id_company =  auth()->id();
            $company -> id_seller = $request->id_seller;
            $company -> percent = $request->percent;
            $company -> save();
            return Response()->json('bien');
        }
    }

}
