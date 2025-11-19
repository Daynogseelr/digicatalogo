<?php

namespace App\Http\Controllers;

use App\DataTables\IndividualClosureDataTable;
use App\DataTables\GlobalClosureDataTable;
use App\Models\Closure;
use App\Models\Bill;
use App\Models\Repayment;
use App\Models\Bill_payment;
use App\Models\Employee;
use App\Models\SmallBox;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ClosureController extends Controller
{
    public function indexClosure(GlobalClosureDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('closures.closure');
        } else {
            return redirect()->route('indexStore');
        }
    }
    public function ajaxClosure()
    {
        if (request()->ajax()) {
            return datatables()->of(Closure::select('id', DB::raw('FORMAT(bill_amount, 2) as bill_amount'), 'created_at')
                ->where('type', 'GLOBAL')->orderBy('created_at', 'desc'))
                ->addColumn('action', 'closures.closure-action')
                ->addColumn('formatted_created_at', function ($closure) {
                    return $closure->created_at->format('d/m/Y H:i:s'); // Adjust the format as needed
                })
                ->rawColumns(['action'])
                ->addIndexColumn()
                ->make(true);
        }
        return view('index');
    }
    public function storeClosure()
    {
        // 1. Generar cierres individuales faltantes
        $users = Employee::select('id_employee')->get();
        foreach ($users as $user) {
            $bills = Bill::where('id_seller', $user->id_employee)
                ->where('status', 1)
                ->where('id_closureI', null)
                ->get();

            $payments = Bill_payment::where('id_seller', $user->id_employee)
                ->where('id_closureI', null)
                ->get();

            $repayments = Repayment::where('id_seller', $user->id_employee)
                ->where('id_closureI', null)
                ->get();

            $smallBoxes = SmallBox::where('id_employee', $user->id_employee)
                ->where('id_closureIndividual', null)
                ->get();

            if ($bills->isNotEmpty() || $payments->isNotEmpty() || $repayments->isNotEmpty() || $smallBoxes->isNotEmpty()) {
                $closure = Closure::create([
                    'id_seller' => $user->id_employee,
                    'id_bill_first' => $bills->first()->id ?? 0,
                    'id_bill_last' => $bills->last()->id ?? 0,
                    'id_bill_payment_first' => $payments->first()->id ?? 0,
                    'id_bill_payment_last' => $payments->last()->id ?? 0,
                    'id_repayment_first' => $repayments->first()->id ?? 0,
                    'id_repayment_last' => $repayments->last()->id ?? 0,
                    'bill_amount' => $bills->sum('net_amount'),
                    'payment_amount' => $payments->sum('amount'),
                    'repayment_amount' => $repayments->sum('amount'),
                    'small_box_amount' => $smallBoxes->sum('cash'),
                    'type' => 'INDIVIDUAL'
                ]);
                // Actualiza los registros con el id del cierre
                Bill::whereIn('id', $bills->pluck('id'))->update(['id_closureI' => $closure->id]);
                Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closureI' => $closure->id]);
                Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closureI' => $closure->id]);
                SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closureIndividual' => $closure->id]);
            }
        }
        $empresa = User::where('type', 'EMPRESA')->first();
        $bills = Bill::where('id_seller', $empresa->id)
            ->where('status', 1)
            ->where('id_closureI', null)
            ->get();

        $payments = Bill_payment::where('id_seller', $empresa->id)
            ->where('id_closureI', null)
            ->get();

        $repayments = Repayment::where('id_seller', $empresa->id)
            ->where('id_closureI', null)
            ->get();

        $smallBoxes = SmallBox::where('id_employee', $empresa->id)
            ->where('id_closureIndividual', null)
            ->get();

        if ($bills->isNotEmpty() || $payments->isNotEmpty() || $repayments->isNotEmpty() || $smallBoxes->isNotEmpty()) {
            $closure = Closure::create([
                'id_seller' => $empresa->id,
                'id_bill_first' => $bills->first()->id ?? 0,
                'id_bill_last' => $bills->last()->id ?? 0,
                'id_bill_payment_first' => $payments->first()->id ?? 0,
                'id_bill_payment_last' => $payments->last()->id ?? 0,
                'id_repayment_first' => $repayments->first()->id ?? 0,
                'id_repayment_last' => $repayments->last()->id ?? 0,
                'bill_amount' => $bills->sum('net_amount'),
                'payment_amount' => $payments->sum('amount'),
                'repayment_amount' => $repayments->sum('amount'),
                'small_box_amount' => $smallBoxes->sum('cash'),
                'type' => 'INDIVIDUAL'
            ]);
            // Actualiza los registros con el id del cierre
            Bill::whereIn('id', $bills->pluck('id'))->update(['id_closureI' => $closure->id]);
            Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closureI' => $closure->id]);
            Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closureI' => $closure->id]);
            SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closureIndividual' => $closure->id]);
        }
        // 2. Generar el cierre globa
        $bills = Bill::where('status', 1)
            ->where('id_closure', null)
            ->get();

        $payments = Bill_payment::where('id_closure', null)
            ->get();

        $repayments = Repayment::where('id_closure', null)
            ->get();

        $smallBoxes = SmallBox::where('id_closure', null)
            ->get();

        if ($bills->isEmpty() && $payments->isEmpty() && $repayments->isEmpty() && $smallBoxes->isEmpty()) {
            return response('mal');
        }

        $closure = Closure::create([
            'id_seller' => auth()->id(),
            'id_bill_first' => $bills->first()->id ?? 0,
            'id_bill_last' => $bills->last()->id ?? 0,
            'id_bill_payment_first' => $payments->first()->id ?? 0,
            'id_bill_payment_last' => $payments->last()->id ?? 0,
            'id_repayment_first' => $repayments->first()->id ?? 0,
            'id_repayment_last' => $repayments->last()->id ?? 0,
            'bill_amount' => $bills->sum('net_amount'),
            'payment_amount' => $payments->sum('amount'),
            'repayment_amount' => $repayments->sum('amount'),
            'small_box_amount' => $smallBoxes->sum('cash'),
            'type' => 'GLOBAL'
        ]);

        // Actualiza los registros con el id del cierre global
        Bill::whereIn('id', $bills->pluck('id'))->update(['id_closure' => $closure->id]);
        Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closure' => $closure->id]);
        Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closure' => $closure->id]);
        SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closure' => $closure->id]);

        return response($closure->id);
    }
    public function indexIndividualClosure(IndividualClosureDataTable $dataTable, Request $request)
    {
        $users = collect();
        $selected_user_id = $request->input('user_id', auth()->id());
        if (in_array(auth()->user()->type, ['SUPERVISOR', 'ADMINISTRATIVO','ADMINISTRADOR'])) {
            $employees = Employee::get();
            $users = User::whereIn('id', $employees->pluck('id_employee'))->get();
        } elseif (auth()->user()->type == 'EMPRESA') {
            $employees = Employee::get();
            $users = User::whereIn('id', $employees->pluck('id_employee'))->get();
            $users->prepend(auth()->user());
        } elseif (auth()->user()->type == 'EMPLEADO') {
            $users = collect([auth()->user()]);
            $selected_user_id = auth()->id();
        }
        return $dataTable->render('closures.closureIndividual', compact('users', 'selected_user_id'));
    }

    public function ajaxIndividualClosure(Request $request)
    {
        return app(IndividualClosureDataTable::class)->ajax();
    }

    public function storeIndividualClosure(Request $request)
    {
        $user_id = $request->input('user_id', auth()->id());
        if (auth()->user()->type == 'EMPLEADO') {
            $user_id = auth()->id();
        }
        // Buscar el último cierre individual de este usuario
        $lastClosure = Closure::where('id_seller', $user_id)
            ->where('type', 'INDIVIDUAL')
            ->orderBy('id', 'desc')
            ->first();
        // Facturas
        $bills = Bill::where('id_seller', $user_id)
            ->where('status', 1)
            ->where('id_closureI', null)
            ->get();
        // Pagos
        $payments = Bill_payment::where('id_seller', $user_id)
            ->where('id_closureI', null)
            ->get();
        // Devoluciones
        $repayments = Repayment::where('id_seller', $user_id)
            ->where('id_closureI', null)
            ->get();
        // Caja chica
        $smallBoxes = SmallBox::where('id_employee', $user_id)
            ->where('id_closureIndividual', null)
            ->get();
        if ($bills->isEmpty() && $payments->isEmpty() && $repayments->isEmpty() && $smallBoxes->isEmpty()) {
            return response('mal');
        }
        $closure = Closure::create([
            'id_seller' => $user_id,
            'id_bill_first' => $bills->first()->id ?? 0,
            'id_bill_last' => $bills->last()->id ?? 0,
            'id_bill_payment_first' => $payments->first()->id ?? 0,
            'id_bill_payment_last' => $payments->last()->id ?? 0,
            'id_repayment_first' => $repayments->first()->id ?? 0,
            'id_repayment_last' => $repayments->last()->id ?? 0,
            'bill_amount' => $bills->sum('net_amount'),
            'payment_amount' => $payments->sum('amount'),
            'repayment_amount' => $repayments->sum('amount'),
            'small_box_amount' => $smallBoxes->sum('cash'),
            'type' => 'INDIVIDUAL'
        ]);
        // Actualiza los registros con el id del cierre
        Bill::whereIn('id', $bills->pluck('id'))->update(['id_closureI' => $closure->id]);
        Bill_payment::whereIn('id', $payments->pluck('id'))->update(['id_closureI' => $closure->id]);
        Repayment::whereIn('id', $repayments->pluck('id'))->update(['id_closureI' => $closure->id]);
        SmallBox::whereIn('id', $smallBoxes->pluck('id'))->update(['id_closureIndividual' => $closure->id]);
        return response($closure->id);
    }
}
