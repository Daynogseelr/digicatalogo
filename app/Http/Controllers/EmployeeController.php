<?php

namespace App\Http\Controllers;
use App\DataTables\EmployeeDataTable;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Employee;
use App\Models\Inventory;
use App\Models\InventoryEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller{
    public function indexEmployee(EmployeeDataTable $dataTable){
        if ( auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'ADMINISTRATIVO' || auth()->user()->type == 'EMPRESA') {
             $inventories = Inventory::where('status', 1)->get();
            return $dataTable->render('users.employee', compact('inventories'));
        } 
        return redirect()->route('indexStore');
    }
    public function storeEmployee(Request $request){  
        $employeeId = $request->id; 
        if ($employeeId == NULL || $employeeId == '') {   
            $user = User::select('id','type')->where('ci',$request->ci)->first();
            if ($user) {
                if ($user->type == 'COMPRADOR' || $user->type == 'CLIENTE') {
                    $employeeId = $user->id;
                } else {
                    $request->validate([
                        'id_inventory'  => 'required',
                        'name' => 'required|min:2|max:20|string',
                        'last_name' => 'required|min:2|max:20|string',
                        'nationality'  => 'required',
                        'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci',
                        'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                        'percent'  => 'required|numeric|min:1|max:100',
                        'direction'  => 'required|min:3|max:200|string',
                        'email'  => 'required|min:5|max:100|unique:users,email',
                        'password'  => 'required|min:8|max:20',
                    ]);  
                }
            } else {
                $request->validate([
                    'id_inventory'  => 'required',
                    'name' => 'required|min:2|max:20|string',
                    'last_name' => 'required|min:2|max:20|string',
                    'nationality'  => 'required',
                    'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci',
                    'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                    'percent'  => 'required|numeric|min:1|max:100',
                    'direction'  => 'required|min:3|max:200|string',
                    'email'  => 'required|min:5|max:100|unique:users,email',
                    'password'  => 'required|min:8|max:20',
                ]);  
            }  
        } else {           
            $user = User::find($request-> id);
            if (! $user) {
                abort(404);
            }
            $request->validate([
                'id_inventory'  => 'required',
                'name' => 'required|min:2|max:20|string',
                'last_name' => 'required|min:2|max:20|string',
                'nationality'  => 'required',
                'ci'  => 'required|numeric|min:1000000|max:999999999|unique:users,ci,'. $user->id,
                'phone'  => 'required|numeric|min:1000000000|max:99999999999',
                'percent'  => 'required|numeric|min:1|max:100',
                'direction'  => 'required|min:3|max:200|string',
                'email'  => 'required|min:5|max:100|unique:users,email,'. $user->id,
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
                    'type' =>  $request->type, 
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
                    'type' => $request->type, 
                    'direction' => $request->direction
                ]);  
        }     
        $companies = Employee::where('id_employee',$employee->id)->first();
        if ($companies == null) {
            $company = new Employee();
            $company -> id_employee = $employee->id;
            $company -> percent = $request->percent;
            $company -> smallBox = $request->smallBox;
            $company -> save();
        } else {
            $companies -> percent  = $request->percent; 
            $companies -> smallBox = $request->smallBox;
            $companies -> save();
        }    
        if ($request->id_inventory) {
            $inventory = InventoryEmployee::
                where('id_employee',$employee->id)
                ->delete();
            foreach ($request->id_inventory as $id_inventory) {
                InventoryEmployee::Create([
                    'id_inventory' => $id_inventory, 
                    'id_employee' => $employee->id,
                ]); 
            }
        }
        return Response()->json($employee);
    }
    public function editEmployee(Request $request)
    {

        $employee = User::with('inventoryEmployees')
                        ->where('id', $request->id) // Buscamos por el ID de la tabla 'users'
                        ->first();
        if (!$employee) {
            return response()->json(['error' => 'Empleado no encontrado.'], 404);
        }
        $employeeDetails = DB::table('employees')
                            ->where('id_employee', $employee->id) // Asumiendo que id_employee en 'employees' es el id del usuario
                            ->first();
        return response()->json([
            'res' => $employee, // Este es el objeto Eloquent User con inventoryEmployees cargado
            'employee' => $employeeDetails, // Datos de la tabla 'employees' si los necesitas
            'inventories' => $employee->inventoryEmployees // Ahora esto funcionará correctamente
        ]);
    }
    public function deleteEmployee(Request $request){
        $employees = Employee::where('id_employee', $request->id)->where('id_company', auth()->id())->delete();
        $employee = User::find($request-> id); 
        $employee->update(['type' => 'CLIENTE','status' => '1']); 
        return Response()->json($employee);
    }
    public function statusEmployee(Request $request){ 
        $employee = User::find($request-> id); 
        if ( $employee->status == '1') {
            $employee->update(['type' => 'CLIENTE','status' => '1']); 
        } else {
            $employee->update(['type' => 'EMPLEADO','status' => '1']); 
        }
        return Response()->json($employee);   
    }
}
