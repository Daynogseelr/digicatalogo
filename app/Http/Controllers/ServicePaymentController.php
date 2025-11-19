<?php

namespace App\Http\Controllers;
use App\Models\Employee;
use App\Models\Dolar;
use App\Models\ServicePayment;
use App\Models\ServiceDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Faker\Factory;
use Carbon\Carbon;

class ServicePaymentController extends Controller
{
    public function indexServicePayment(){
        $production = DB::table('services')
            ->join('service_details', 'services.id', '=', 'service_details.id_service')
            ->select(
                DB::raw('SUM(CASE WHEN MONTH(service_details.created_at) = MONTH(CURDATE()) THEN service_details.price ELSE 0 END) AS total_price_current_month'),
            // Add other desired columns from services or service_details
            )
            ->where('service_details.id_product', null)
            ->where('services.status', 'ENTREGADO')
            ->first();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $countService = DB::table('services')
            ->where('status', 'ENTREGADO')
            ->whereMonth('created_at', $currentMonth) // Filtra por mes
            ->whereYear('created_at', $currentYear) // Filtra por año
            ->count();
        $payments = DB::table('service_payments')
            ->join('users', 'service_payments.id_technician', '=', 'users.id') // Une con la tabla users
            ->select('service_payments.id as id_service_payment','service_payments.percent','users.id', 'users.name as technicianName', 'users.last_name as technicianLast_name','service_payments.amount') // Selecciona los campos necesarios
            ->where('service_payments.status', 0)
            ->get(); 
        return view('services.servicePayment', compact('production','countService','payments'));    
    }  
    public function ajaxServicePayment(){
        DB::statement("SET SQL_MODE=''");
        if(request()->ajax()) {
            return datatables()->of(DB::table('service_payments')
            ->join('users as technician', 'technician.id','=','service_payments.id_technician')
            ->select('service_payments.id','service_payments.id_technician','service_payments.dateStart','service_payments.dateEnd','service_payments.percent','service_payments.status',
            'technician.name as technicianName','technician.last_name as technicianLast_name','service_payments.amount')
            ->where('service_payments.status', 1)
            ->orderBy('service_payments.dateStart','desc')
            ->get())
            ->addColumn('action', 'services.servicePayment-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return redirect()->route('indexStore');
    }
    public function modalServicePayment(Request $request){   
        $servicePayments  = DB::table('service_details')
            ->join('services','services.id','=','service_details.id_service')
            ->join('users as client','client.id','=','services.id_client')
            ->join('service_payments','service_payments.id','=','service_details.id_servicePayment')
            ->select('client.name','client.last_name',
                'services.created_at',
                'service_details.price','service_details.procedure',
                'service_payments.percent',
                'service_payments.status',
                'service_payments.amount',
                DB::raw('FORMAT(service_details.price * (service_payments.percent / 100), 2) as commission_amount')
                )
            ->where('service_payments.id',$request->id)
            ->get();
        return Response()->json(['servicePayments'=>$servicePayments]);
    }
    public function servicePaymenCommissionAll(Request $request){  
        $servicePayments  = ServicePayment::
            where('status',0)
            ->get();
        $servicePayments->each(function ($servicePayment) {
            $servicePayment->dateEnd = Carbon::now();
            $servicePayment->amount = $servicePayment->amount;
            $servicePayment->status = 1; // Cambia el valor a 1
            $servicePayment->save();
        });
        return redirect()->route('indexServicePayment');
    }
    public function servicePaymenCommission($id){  
        $servicePayment  = ServicePayment::find($id);
        $servicePayment->dateEnd = Carbon::now();
        $servicePayment->amount = $servicePayment->amount;
        $servicePayment->status = 1; // Cambia el valor a 1
        $servicePayment->save();
        return redirect()->route('indexServicePayment');
    }
    public function modalServicePercent(Request $request){ 
        $request->validate([
            'percent'  => 'required|numeric|min:1|max:100',
        ]);  
        $payment = ServicePayment::find($request->id);
        $amount = ($request->percent * $payment->amount) / $payment->percent;
        $payment->percent = $request->percent;
        $payment->amount = $amount;
        $payment->save();
        return redirect()->route('indexServicePayment');
    }
    public function chartServiceMonth(Request $request)
{
    $now = now();
    $month = $now->month;
    $year = $now->year;

    $services = DB::table('services')
        ->selectRaw('DATE(created_at) as day, COUNT(*) as cantidad, SUM(price) as total')
        ->where('status', 'ENTREGADO')
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->groupBy('day')
        ->orderBy('day')
        ->get();

    // Formato para Highcharts
    $data = [];
    foreach ($services as $service) {
        $data[] = [
            'day' => $service->day,
            'cantidad' => $service->cantidad,
            'total' => $service->total ? floatval($service->total) : 0,
        ];
    }

    return response()->json($data);
}
}

