<?php

namespace App\Http\Controllers;
use Illuminate\support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller{
    public function indexEmployee(){
        if ( auth()->user()->type == 'ADMINISTRADOR') {
            DB::statement("SET SQL_MODE=''");
            if(request()->ajax()) {
                return datatables()->of(User::select('*')
                ->where('status',1)
                ->where('type','EMPLEADO'))
                ->addColumn('action', 'users.employee-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else  if ( auth()->user()->type == 'EMPRESA'){
            DB::statement("SET SQL_MODE=''");
            if(request()->ajax()) {
                return datatables()->of(DB::table('users')
                ->join('employees', 'employees.id_employee','=','users.id')
                ->select('users.id as id','name','last_name','nationality','ci','phone','direction','email','status')
                ->where('employees.id_company', auth()->id())
                ->where('users.type','EMPLEADO'))
                ->addColumn('action', 'users.employee-action')
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
            }
        } else  if ( auth()->user()->type == 'EMPLEADO'){
            return redirect()->route('dashboard');
        } else {
            return redirect()->route('storeIndex');
        }  
        return view('index');
    }
    public function storeEmployee(Request $request){  
        $employeeId = $request->id;
        if ($employeeId == NULL || $employeeId == '') {
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
            $employee   =   User::updateOrCreate(
                [
                 'id' => $employeeId
                ],
                [
                'name' => $request->name, 
                'last_name' => $request->last_name,
                'nationality' => $request->nationality, 
                'ci' => $request->ci, 
                'phone' => $request->phone,
                'email' => $request->email, 
                'status' =>'1', 
                'type' =>'EMPLEADO', 
                'direction' => $request->direction
                ]);    
        } else {
            $employee   =   User::updateOrCreate(
                [
                 'id' => $employeeId
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
                'type' =>'EMPLEADO', 
                'direction' => $request->direction
                ]);  
        }     
        $companies = Employee::where('id_employee',$employee->id)->first();
        if ($companies == null) {
            $company = new Employee();
            $company -> id_company =  auth()->id();
            $company -> id_employee = $employee->id;
            $company -> save();
        }     
        return Response()->json($employee);
    }
    public function editEmployee(Request $request){   
        $where = array('id' => $request->id);
        $employee  = User::where($where)->first();
        return Response()->json($employee);
    }
    public function destroyEmployee(Request $request){
        $employee = User::where('id',$request->id)->delete();
        return Response()->json($employee);
    }
    public function statusEmployee(Request $request){ 
        $employee = User::find($request-> id); 
        if ( $employee->status == '1') {
            $employee->update(['status' => '0']); 
        } else {
            $employee->update(['status' => '1']); 
        }
        return Response()->json($employee);   
    }
}
