<?php

namespace App\Http\Controllers;
use App\Models\Service;
use App\Models\Employee;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\Product;
use App\Models\ServiceDetail;
use App\Models\Bill;
use App\Models\ServicePayment;
use App\Models\ServiceTechnician;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
use App\DataTables\ServiceDataTable;
use App\Models\Currency;

class ServiceController extends Controller
{
    public function indexConsult(){
        return view('services.serviceConsult');
    }
    public function indexServiceCategory(){
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'EMPLEADO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'ADMINISTRATIVO') {
            return view('services.serviceCategory');
        } else {
            return redirect()->route('indexStore');
        }      
    }
    public function indexService(ServiceDataTable $dataTable){
        $currencies = Currency::select('*')->where('status', 1)->get();
        $products = Product::where('status',1)->get();
        $clients = User::select('*')->where('type','!=', 'ADMINISTRADOR')->get();
        $categories = ServiceCategory::select('*')->where('status', 1)->get();
        $technicians =  DB::table('employees')
        ->join('users as technician', 'technician.id','=','employees.id_employee'
        )->select('technician.id','technician.name','technician.last_name','technician.nationality','technician.ci')
        ->where(function ($query) {
            $query->where('technician.type', 'EMPLEADO')
                ->orWhere('technician.type', 'SUPERVISOR')
                ->orWhere('technician.type', 'ADMINISTRATIVO');
        })
        ->get();
        // Contadores por estado para los cards
        $countServiceRecibido = DB::table('services')->where('status', 'Recibido')->count();
        $countServiceRevisado = DB::table('services')->where('status', 'Revisado')->count();
        $countServiceTerminado = DB::table('services')->where('status', 'Terminado')->count();
        $countServiceEntregado = DB::table('services')->where('status', 'Entregado')->count();
        $id_shopper = session('id_shopper');
        if ($id_shopper) {
            return $dataTable->render('services.service', compact(
                'countServiceRecibido',
                'countServiceRevisado',
                'countServiceTerminado',
                'countServiceEntregado',
                'clients','categories','products','technicians','currencies','id_shopper'
            ));
        } else {
            return $dataTable->render('services.service', compact(
                'countServiceRecibido',
                'countServiceRevisado',
                'countServiceTerminado',
                'countServiceEntregado',
                'clients','categories','products','technicians','currencies'
            ));
        }  
    }
    public function ajaxServiceCategory(){
        if(request()->ajax()) {
            return datatables()->of(ServiceCategory::select('*')->orderBy('created_at','desc'))
            ->addColumn('action', 'services.serviceCategory-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return redirect()->route('indexStore');
    }
    public function ajaxService(){
        if(request()->ajax()) {
            return datatables()->of(DB::table('services')
            ->join('users as client', 'client.id','=','services.id_client')
            ->leftJoin('users as technician', 'technician.id','=','services.id_technician')
            ->join('service_categories as category', 'category.id','=','services.id_category')
            ->select('services.id','services.id_client','services.id_technician','services.ticker','services.model','services.brand','services.status','category.name','services.created_at',
            'client.name as clientName','client.last_name as clientLast_name','client.nationality','client.ci','client.phone',
            'technician.name as technicianName','technician.last_name as technicianLast_name')
            ->orderBy('services.created_at','desc')
            ->get())
            ->addColumn('action', 'services.service-action')
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
        }
        return redirect()->route('indexStore');
    }
  
    public function storeServiceCategory(Request $request)
    {
        if ($request->id) {
            $request->validate([
                'name'  => ['required', Rule::unique('service_categories')->ignore($request->id)],
            ]);
        } else {    
            $request->validate([
                'name'  => 'required|unique:service_categories,name',
            ]);
        }
        $ServiceCategoryId = $request->id;
        $ServiceCategory   =   ServiceCategory::updateOrCreate(
            [
                'id' => $ServiceCategoryId
            ],
            [
                'name' => $request->name, 
                'brand' => $request->brand, 
                'model' => $request->model, 
                'serial' => $request->serial, 
                'status' => 1,
            ]
        ); 
        return Response()->json($ServiceCategory);
    }
    public function storeService(Request $request)
    {
        $request->validate([
            'client' => 'required',
            'category' => 'required',
            'description'  => 'required|min:1|max:200|string'
        ]);
        $oldTicker = Service::orderByDesc(DB::raw('CAST(SUBSTRING(ticker, 2) AS UNSIGNED)'))
            ->first();
        if ($oldTicker) {
            $numberPart = intval(substr($oldTicker->ticker, 1));
            $tickerNew = 'T' . ($numberPart + 1);
        } else {
            $tickerNew = 'T1';
        }
        $Service   =   Service::Create(
            [
                'id_seller' => auth()->id(), 
                'id_client' => $request->client, 
                'id_category' => $request->category,
                'ticker' => $tickerNew,
                'brand' => $request->brand,
                'model' => $request->model,
                'serial' => $request->serial,
                'description' => $request->description,
                'status' => 'RECIBIDO',
            ]
        ); 
        return Response()->json($Service->id);
    }
    
    public function storeShopperService(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:250|string',
            'nationality'  => 'required',
            'ci'  => 'required|numeric|min:100000|max:9999999999|unique:users,ci',
        ]);
        $existingEmailUser = User::where('email', $request->email)->first();
        if ($existingEmailUser) {
            return back()->withErrors(['email' => 'El correo electrónico ya está registrado.'])->withInput();
        }

        $client   =   User::create(
            [
                'name' => $request->name,
                'last_name' => $request->last_name,
                'nationality' => $request->nationality,
                'ci' => $request->ci,
                'phone' => $request->phone ? $request->phone : '0000000000',
                'email' => $request->email ? $request->email : $request->name.''.$request->last_name.''. $request->ci,
                'status' => '1',
                'type' => 'CLIENTE',
                'password' => '123',
                'direction' => $request->direction ? $request->email : 'CARUPANO',
            ]
        );
        $id_shopper = $client->id;
        // Store the ID in the session as flash data
        session()->flash('id_shopper', $id_shopper);
        // Redirect to the indexBilling route
        return redirect()->route('indexService');
    }
    public function editServiceCategory(Request $request){   
        $where = array('id' => $request->id);
        $category  = ServiceCategory::where($where)->first();
        return Response()->json($category);
    }
    public function statusServiceCategory(Request $request){ 
        $serviceCategory = ServiceCategory::find($request-> id);
        if ( $serviceCategory->status == '1') {
            $serviceCategory->status = 0;
            $serviceCategory->save();
        } else {
            $serviceCategory->status = 1;
            $serviceCategory->save(); 
        }
        return Response()->json($serviceCategory);   
    }
    public function storeTechnician(Request $request)
    {
        $request->validate([
            'id_technician' => 'required',
            'id_service' => 'required|exists:services,id',
        ]);
        try {
            $service = Service::findOrFail($request->id_service);
            $service->update(['id_technician' => $request->id_technician, 'status' => 'REVISIÓN']);
            ServiceTechnician::where('id_service',$request->id_service)->delete();
            if ($request->tecnicosAyudantesIds) {
                foreach ($request->tecnicosAyudantesIds as $ids) {
                    $serviceTechnician = new ServiceTechnician();
                    $serviceTechnician->id_service = $request->id_service;
                    $serviceTechnician->id_technician = $ids;
                    $serviceTechnician->percent = $request->porcentajes[$ids];
                    $serviceTechnician->save();
                }
            }
            return response()->json(['message' => 'Técnico asignado correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
    }
    public function getTechnicianService($id)
    {
        try {
            $service = Service::findOrFail($id);
            // Obtener el técnico principal
            $technician = $service->technician;
            // Obtener los técnicos secundarios con sus porcentajes
           // Obtener los técnicos secundarios con la información adicional
            $secondary_technicians = $service->secondary_technicians->map(function ($serviceTechnician) {
                $technician = User::find($serviceTechnician->id_technician);
                return [
                    'id' => $technician->id,
                    'name' => $technician->name,
                    'last_name' => $technician->last_name,
                    'ci' => $technician->ci,
                    'nationality' => $technician->nationality,
                    'percent' => $serviceTechnician->percent,
                ];
            });

            return response()->json([
                'technician' => $technician,
                'secondary_technicians' => $secondary_technicians,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }
    }
    public function editSolution(Request $request){   
        $where = array('id' => $request->id);
        $service  = Service::where($where)->first();
        $id_product = $service->serviceDetails->pluck('id_product')->filter(function ($value) {
            return $value !== null;
        })->values()->toArray();

        return response()->json([
            'service' => $service,
            'id_product' => $id_product, // Devuelve un array de IDs
        ]);
        return Response()->json($service);
    }
    public function tableProductService(Request $request)
{
    $currency_rate = $request->currency_rate ?? 1;
    $serviceDetails = DB::table('service_details')
        ->leftJoin('products', 'products.id','=','service_details.id_product')
        ->select(
            'service_details.id',
            'products.name',
            'products.id as id_product',
            'products.name_fraction',
            'service_details.procedure',
            'service_details.quantity',
            'service_details.price',
            'service_details.type',
            'service_details.mode'
        )
        ->where('id_service', $request->id_service)
        ->get();

    // Agrega el tipo y modo para cada producto
    foreach ($serviceDetails as $detail) {
        $detail->type = $detail->type ?? 'NORMAL';
        $detail->mode = $detail->mode ?? '';
        if ($detail->id_product) {
            if ($detail->mode === 'COMPLETO') {
                $detail->name = $detail->name;
            } elseif ($detail->mode === 'FRACCION') {
                $detail->name = $detail->name_fraction;
            }
        } else {
            $detail->name =  $detail->procedure;
        }
    }

    return response()->json([
        'serviceDetails' => $serviceDetails,
        'currency_rate' => $currency_rate
    ]);
}
    public function tableTotalService(Request $request)
    {
        try {
            $products = Product::find($request->id_product);
            // Es importante verificar si se encontraron productos.
            $serviceDetails = ServiceDetail::select('*')->where('id_service',$request->id_service)->get();
            $totalDs  = 0;
            if ($serviceDetails) {
                foreach ($serviceDetails as $serviceDetail) {
                    $totalDs += $serviceDetail->price;
                }
            }
            return response()->json([
                'message' => 'Productos encontrados correctamente',
                'totalDs' => number_format($totalDs, 2),
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function addProcedure(Request $request)
    {
        $request->validate([
            'procedure' => 'required|min:1|max:200|string',
            'price' => 'required',
            'id_service' => 'required|exists:services,id',
        ]);
        try {
            ServiceDetail::Create(
                [
                    'id_service' => $request->id_service, 
                    'procedure' => $request->procedure, 
                    'quantity' => 1,
                    'priceU' => $request->price,
                    'price' => $request->price,
                ]
            ); 
            return response()->json(['message' => 'correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function addProduct(Request $request)
{
    $request->validate([
        'id_service' => 'required|exists:services,id',
    ]);
    try {
        DB::beginTransaction();
        if (!$request->has('id_product') || empty($request->id_product)) {
            ServiceDetail::where('id_service', $request->id_service)
                ->where('id_product', '!=', null)
                ->delete();
        } else {
            ServiceDetail::where('id_service', $request->id_service)
                ->whereNotIn('id_product', $request->id_product)
                ->delete();
            foreach ($request->id_product as $id_product) {
                $product = Product::find($id_product);
                if ($product->type == 'SERVICIO') {
                    $serviceDetail = ServiceDetail::firstOrCreate([
                        'id_service' => $request->id_service,
                        'id_product' => $product->id,
                    ], [
                        'quantity' => 1,
                        'priceU' => $product->price,
                        'price' => $product->price,
                        'type' => 'SERVICIO'
                    ]);
                    // Si es integral, busca el producto integral y agrega con cantidad
                    foreach ($product->productI as $productI) {
                        $product2 = Product::find($productI->id_product);
                        $serviceDetail = ServiceDetail::firstOrCreate([
                            'id_service' => $request->id_service,
                            'id_product' => $product2->id,
                        ], [
                            'quantity' => $productI->quantity,
                            'priceU' => $product2->price,
                            'price' => $product2->price * $productI->quantity,
                            'type' => 'NORMAL'
                        ]);
                    }
                } elseif ($product->type == 'FRACCIONADO') {
                    // Si es fraccionado, agrega con modo por defecto COMPLETO
                    $serviceDetail = ServiceDetail::firstOrCreate([
                        'id_service' => $request->id_service,
                        'id_product' => $id_product,
                    ], [
                        'quantity' => 1,
                        'priceU' => $product->price,
                        'price' => $product->price,
                        'type' => 'FRACCIONADO',
                        'mode' => 'COMPLETO'
                    ]);
                } else {
                    // Normal
                    $serviceDetail = ServiceDetail::firstOrCreate([
                        'id_service' => $request->id_service,
                        'id_product' => $id_product,
                    ], [
                        'quantity' => 1,
                        'priceU' => $product->price,
                        'price' => $product->price,
                        'type' => 'NORMAL'
                    ]);
                }
            }
        }
        DB::commit();
        return response()->json(['message' => 'Detalles del servicio sincronizados correctamente.'], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Error al sincronizar detalles del servicio: ' . $e->getMessage()], 500);
    }
}
public function updateFractionModeService(Request $request)
{
    $serviceDetail = ServiceDetail::find($request->id);
    if (!$serviceDetail) {
        return response()->json(['error' => 'Detalle no encontrado'], 404);
    }
    $product = Product::find($serviceDetail->id_product);
    if ($request->mode === 'COMPLETO') {
        $serviceDetail->priceU = $product->price;
        $serviceDetail->price = $product->price * $serviceDetail->quantity;
        $serviceDetail->mode = 'COMPLETO';
    } else if ($request->mode === 'FRACCION') {
        $serviceDetail->priceU = $product->price_fraction;
        $serviceDetail->price = $product->price_fraction * $serviceDetail->quantity;
        $serviceDetail->mode = 'FRACCION';
    }
    $serviceDetail->save();
    return response()->json(['success' => true]);
}
public function updateServiceCurrency(Request $request)
{
    $service = Service::find($request->id_service);
    if (!$service) {
        return response()->json(['error' => 'Servicio no encontrado'], 404);
    }
    $newCurrency = Currency::find($request->id_currency);
    // Actualiza la moneda del servicio
    $service->id_currency = $request->id_currency;
    $service->save();
    // Actualiza los precios de los detalles
    foreach ($service->serviceDetails as $serviceDetail) {
        if ($serviceDetail->id_product) {
            $product = Product::find($serviceDetail->id_product);
            if ($serviceDetail->mode === 'COMPLETO') {
                $serviceDetail->priceU = $product->price *  $newCurrency->rate2 /  $newCurrency->rate;
                $serviceDetail->price = $serviceDetail->priceU * $serviceDetail->quantity;
            } elseif ($serviceDetail->mode === 'FRACCION') {
                $serviceDetail->priceU = $product->price_fraction *  $newCurrency->rate2 /  $newCurrency->rate;
                $serviceDetail->price = $serviceDetail->priceU * $serviceDetail->quantity;
            } else {
                $serviceDetail->priceU = $product->price *  $newCurrency->rate2 /  $newCurrency->rate;
                $serviceDetail->price = $serviceDetail->priceU * $serviceDetail->quantity;
            }
            $serviceDetail->save();
        }
    }

    return response()->json(['success' => true]);
}
    public function updateQuantityService(Request $request){
        $serviceDetail = ServiceDetail::find($request-> id);
        $price = $serviceDetail->priceU * $request->quantity;
        $serviceDetail->quantity = $request-> quantity;
        $serviceDetail->price = $price;
        $serviceDetail->save();
        return Response()->json($serviceDetail);
    }
    public function storeSolution(Request $request)
    {
        $request->validate([
            'solution' => 'required|min:1|max:200|string',
            'id_serviceSolution' => 'required|exists:services,id',
        ]);
        try {
            $total = floatval($request->total); // O (float)$request->total;
            $service = Service::findOrFail($request->id_serviceSolution);
            $service->solution = $request-> solution;
            $service->price = $total;
            $service->status = 'REVISADO';
            $service->save();
            return response()->json(['message' => 'correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function deleteServiceDetail(Request $request){
        ServiceDetail::where('id_service', $request->id_service)->delete();
        return Response()->json(['message' => 'correctamente'], 200);
    }
    public function mostrarService(Request $request)
    {
        try {
            $service = Service::with(['serviceDetails' => function ($query) {
                $query->with('product'); // Carga la relación 'product' dentro de 'serviceDetails'
            }])->find($request->id);
    
            if (!$service) {
                return response()->json(['message' => 'Servicio no encontrado'], 404); // Manejo explícito si no se encuentra el servicio
            }
    
            $serviceDetailsConNombreProducto = $service->serviceDetails->map(function ($serviceDetail) {
                return [
                    'id' => $serviceDetail->id,
                    'quantity' => $serviceDetail->quantity,
                    'price' => $serviceDetail->price,
                    'procedure' => $serviceDetail->procedure,
                    'id_product' => $serviceDetail->id_product,
                    'product_name' => $serviceDetail->product ? $serviceDetail->product->name : null, // Obtiene el nombre del producto o null si no existe
                ];
            });
    
            return response()->json([
                'service' => $service,
                'serviceDetails' => $serviceDetailsConNombreProducto,
            ], 200);
    
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function approveService(Request $request)
    {
        try {
            $service = Service::findOrFail($request->id);
            $service->update(['status' => 'APROBADO']);
            return response()->json(['message' => 'correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function declineService(Request $request)
    {
        try {
            $service = Service::findOrFail($request->id);
            $service->update(['status' => 'RECHAZADO']);
            return response()->json(['message' => 'correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }   
    public function endService(Request $request)
    {
        try {
            $service = Service::findOrFail($request->id);
            $service->update(['status' => 'TERMINADO']);
            return response()->json(['message' => 'correctamente'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }   
    public function handService(Request $request)
    {
        $request->validate([
            'code' => [
                'required','unique:services,code,' . $request->id,
                function ($attribute, $value, $fail) {
                    if (!Bill::where('code', $value)->exists()) {
                        $fail('El código proporcionado no existe en las facturas.');
                    }
                },
            ]
        ]);
        try {
            $service = Service::findOrFail($request->id);
            $service->update(['status' => 'ENTREGADO','code' => $request->code]);

            // Obtener el porcentaje del técnico principal
            $lastPayment = ServicePayment::
                where('id_technician', $service->id_technician)
                ->orderBy('created_at', 'desc')
                ->first();
            $percentToApply = $lastPayment ? $lastPayment->percent : 40;

            // Calcular el monto total de los detalles del servicio sin producto
            $totalAmountToUpdate = $service->serviceDetails()
                ->whereNull('id_product')
                ->sum('price');

            // Calcular el monto a agregar al técnico principal
            $amountToAdd = $totalAmountToUpdate * ($percentToApply / 100);

            // Inicializar el ServicePayment del técnico principal
            $servicePayment = null;

            // Procesar los técnicos suplentes y restar sus montos del monto del técnico principal
            $serviceTechnicians = ServiceTechnician::where('id_service', $service->id)->get();
            foreach ($serviceTechnicians as $serviceTechnician) {
                // Calcular el monto a agregar al técnico suplente
                $amountToAddSuplente = $amountToAdd * ($serviceTechnician->percent / 100);

                // Buscar o crear el ServicePayment para el técnico suplente
                $servicePaymentSuplente = ServicePayment::where('id_technician', $serviceTechnician->id_technician)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$servicePaymentSuplente) {
                    // No hay pagos anteriores, crear uno nuevo para el técnico suplente
                    ServicePayment::create([
                        'id_seller' => auth()->id(),
                        'id_technician' => $serviceTechnician->id_technician,
                        'percent' => $serviceTechnician->percent,
                        'amount' => $amountToAddSuplente,
                        'dateStart' => Carbon::now(),
                    ]);
                } elseif ($servicePaymentSuplente->status == 0) {
                    $servicePaymentSuplente->update([
                        'amount' => $servicePaymentSuplente->amount + $amountToAddSuplente,
                    ]);
                } elseif ($servicePaymentSuplente->status == 1){
                    // Crear un nuevo pago para el técnico suplente
                    ServicePayment::create([
                        'id_seller' => auth()->id(),
                        'id_technician' => $serviceTechnician->id_technician,
                        'amount' => $amountToAddSuplente,
                        'percent' => $servicePaymentSuplente->percent,
                        'dateStart' => Carbon::now(),
                    ]);
                }

                // Restar el monto del técnico suplente del monto del técnico principal
                $amountToAdd -= $amountToAddSuplente;
            }

            // Crear o actualizar el ServicePayment del técnico principal con el monto ajustado
            if (!$lastPayment) {
                // No hay pagos anteriores, crear uno nuevo para el técnico principal
                $servicePayment = ServicePayment::create([
                    'id_seller' => auth()->id(),
                    'id_technician' => $service->id_technician,
                    'amount' => $amountToAdd,
                    'percent' => $percentToApply,
                    'dateStart' => Carbon::now(),
                ]);
            } elseif ($lastPayment->status == 0) {
                // El último pago está incompleto, actualizar el monto del técnico principal
                $lastPayment->update([
                    'amount' => $lastPayment->amount + $amountToAdd,
                ]);
            } elseif ($lastPayment->status == 1){
                // Crear un nuevo pago para el técnico principal
                $servicePayment = ServicePayment::create([
                    'id_seller' => auth()->id(),
                    'id_technician' => $service->id_technician,
                    'amount' => $amountToAdd,
                    'percent' => $lastPayment->percent,
                    'dateStart' => Carbon::now(),
                ]);
            }

            // Actualizar los detalles del servicio con el id_servicePayment del técnico principal
            if ($servicePayment) {
                foreach ($service->serviceDetails as $serviceDetail) {
                    if ($serviceDetail->id_product === null) {
                        $serviceDetail->update(['id_servicePayment' => $servicePayment->id]);
                    }
                }
            }

            return response()->json($service->id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'no correctamente'], 404);
        }
    }
    public function getCategoryDetails($id)
    {
        $category = ServiceCategory::find($id); // Find the category by its ID

        if (!$category) {
            return response()->json(['error' => 'Category not found'], 404); // Handle the case where the category doesn't exist
        }
        //dd($category);
        return response()->json([
            'brand' => $category->brand,
            'model' => $category->model,
            'serial' => $category->serial,
        ]);
    }
}
