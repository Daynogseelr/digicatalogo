<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        if (auth()->user()->type == 'ADMINISTRADOR') {
            if (request()->ajax()) {
                return datatables()->of(User::select('*')->where('type', 'EMPRESA'))
                    ->addColumn('action', 'users.company-action')
                    ->rawColumns(['action'])
                    ->addIndexColumn()
                    ->make(true);
            }
            return view('index');
        } else  if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }
    }
    public function store(Request $request)
    {
        $companyId = $request->id;
        if ($companyId == NULL || $companyId == '') {
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci',
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'state'  => 'required|min:3|max:50|string',
                'city'  => 'required|min:3|max:50|string',
                'postal_zone'  => 'required|min:3|max:50|string',
                'direction'  => 'required|min:3|max:250|string',
                'email'  => 'required|min:2|max:100|unique:users,email',
                'password'  => 'required|min:4|max:20',
                'logo'   =>  'required|image|mimes:jpg,jpeg,png|max:3000',
            ]);
        } else {
            $user = User::find($request->id);
            if (!$user) {
                abort(404);
            }
            $request->validate([
                'name' => 'required|min:2|max:200|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci,' . $user->id,
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'state'  => 'required|min:3|max:50|string',
                'city'  => 'required|min:3|max:50|string',
                'postal_zone'  => 'required|min:3|max:50|string',
                'direction'  => 'required|min:3|max:250|string',
                'email'  => 'required|min:4|max:100|unique:users,email,' . $user->id,
                'password'  => 'min:4|max:20',
                'logo'   =>  'image|mimes:jpg,jpeg,png|max:3000',
            ]);
        }
        if ($request->password == 'PASSWORD') {
            $company   =   User::updateOrCreate(
                [
                    'id' => $companyId
                ],
                [
                    'name' => $request->name,
                    'nationality' => $request->nationality,
                    'ci' => $request->ci,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'status' => '1',
                    'type' => 'EMPRESA',
                    'state' => $request->state,
                    'city' => $request->city,
                    'postal_zone' => $request->postal_zone,
                    'direction' => $request->direction,
                ]
            );
        } else {
            $company   =   User::updateOrCreate(
                [
                    'id' => $companyId
                ],
                [
                    'name' => $request->name,
                    'nationality' => $request->nationality,
                    'ci' => $request->ci,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'status' => '1',
                    'type' => 'EMPRESA',
                    'state' => $request->state,
                    'city' => $request->city,
                    'postal_zone' => $request->postal_zone,
                    'direction' => $request->direction,
                ]
            );
        }
        //Storage::disk('public')->delete('logos/'.$company -> logo); 
        if ($request->file('logo')) {
            $ruta = 'logos/';
            $imagen = $request->logo;
            $imagencompany = date('YmdHis') . "." . $imagen->getClientOriginalExtension();
            $imagen->move($ruta, $imagencompany);
            $url = $imagencompany;
            $company->update([
                'logo' =>  $url
            ]);
        }
        return Response()->json($company);
    }
    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $company  = User::where($where)->first();
        return Response()->json($company);
    }
    public function destroy(Request $request)
    {
        $company = User::where('id', $request->id)->delete();
        return Response()->json($company);
    }

    public function statusCompany(Request $request)
    {
        $company = User::find($request->id);
        if ($company->status == '1') {
            $company->update(['status' => '0']);
            $employees = Employee::select('id_employee')->where('id_company', $request->id)->get();
            foreach ($employees as $employee) {
                $employe = User::find($employee->id_employee);
                $employe->update(['type' => 'CLIENTE']); 
            }
            Employee::where('id_company', $request->id)->delete();
            $sellers = Seller::select('id_seller')->where('id_company', $request->id)->get();
            foreach ($sellers as $seller) {
                $selle = User::find($seller->id_seller);
                $selle->update(['type' => 'CLIENTE','slug' => NULL]);
            }
            Seller::where('id_company', $request->id)->delete();
        } else {
            $company->update(['status' => '1']);
        }
        return Response()->json($company);
    }
}
