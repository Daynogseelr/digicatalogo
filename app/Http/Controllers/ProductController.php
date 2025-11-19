<?php

namespace App\Http\Controllers;

use App\DataTables\ProductDataTable;
use App\Models\Product;
use App\Models\ProductIntegral;
use App\Models\User;
use App\Models\Employee;
use App\Models\Stock;
use App\Models\AddCategory;
use App\Models\Currency;
use App\Models\ExchangeRate;
use App\Models\Category;
use App\Models\Inventory;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function indexProduct(ProductDataTable $dataTable)
    {
        $products = Product::get();
        $categories = Category::where('status', 1)->get();
        $inventories = Inventory::where('status', 1)->get();
        // Trae todas las monedas y sus tasas de cambio (incluye la principal con tasa 1)
        $currencies = Currency::where('status', 1)->get();
        $currencyPrincipal = Currency::where('is_principal', 1)->first();


        return $dataTable->render('products.product', compact('inventories', 'products', 'categories', 'currencies', 'currencyPrincipal'));
    }
    public function indexLabel()
    {
        if (auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRADOR' ||  auth()->user()->type == 'ADMINISTRATIVO' ||  auth()->user()->type == 'SUPERVISOR' ||  auth()->user()->type == 'EMPLEADO') {
            return view('products.label');
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function ajax(Request $request)
    {
        $inventoryId = $request->get('inventory_id');
        $currencyId = $request->get('currency_id');
        $stockFilter = $request->get('stock_filter', 'all');

        $query = Product::query();

        // Filtrar productos que tengan al menos un registro de stock en el inventario seleccionado
        if ($inventoryId) {
            $query->whereHas('stocks', function ($q) use ($inventoryId) {
                $q->where('id_inventory', $inventoryId);
            });
        }

        // Filtrar por tipo de stock
        if ($stockFilter == 'min') {
            $query->whereIn('id', function ($sub) use ($inventoryId) {
                $sub->select('id_product')
                    ->from('stocks')
                    ->where('id_inventory', $inventoryId)
                    ->whereRaw('
                    quantity <= (
                        SELECT stock_min FROM products WHERE products.id = stocks.id_product
                    )
                ')
                    ->whereRaw('id = (
                    SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product AND s2.id_inventory = stocks.id_inventory
                )');
            });
        } elseif ($stockFilter == 'max') {
            $query->whereIn('id', function ($sub) use ($inventoryId) {
                $sub->select('id_product')
                    ->from('stocks')
                    ->where('id_inventory', $inventoryId)
                    ->whereRaw('
                    quantity > (
                        SELECT stock_min FROM products WHERE products.id = stocks.id_product
                    )
                ')
                    ->whereRaw('id = (
                    SELECT MAX(id) FROM stocks s2 WHERE s2.id_product = stocks.id_product AND s2.id_inventory = stocks.id_inventory
                )');
            });
        }

        // Obtener tasa de cambio
        $currency = Currency::find($currencyId);
        $tasa = $currency->rate;

        return datatables()
            ->eloquent($query)
            ->addColumn('price_currency', function ($row) use ($tasa) {
                return number_format($row->price * $tasa, 2);
            })
            ->addColumn('stock', function ($row) use ($inventoryId) {
                $stock = Stock::where('id_product', $row->id)
                    ->where('id_inventory', $inventoryId)
                    ->latest()
                    ->first();
                return $stock ? $stock->quantity : 0;
            })
            ->addColumn('images', function ($row) {
                $imgs = [];
                for ($i = 1; $i <= 3; $i++) {
                    $url = $row->{'url' . $i};
                    if ($url) {
                        $imgs[] = '<img src="' . asset('storage/' . $url) . '" style="width:40px;height:40px;border-radius:6px;margin-right:4px;" />';
                    }
                }
                return implode('', $imgs);
            })
            ->addColumn('action', 'products.product-action')
            ->rawColumns(['images', 'action'])
            ->make(true);
    }
    public function ajaxLabel(Request $request)
    {
        DB::statement("SET SQL_MODE=''"); // Be very cautious with this. Disabling strict mode can mask underlying data issues.
        $products = Product::all();
        if ($request->ajax()) {
            return DataTables::of($products)
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && $request->input('search.value') != '') {
                        $searchValue = $request->input('search.value');
                        $query->where(function ($q) use ($searchValue) {
                            $q->where('code', 'like', "%{$searchValue}%")
                                ->orWhere('code2', 'like', "%{$searchValue}%")
                                ->orWhere('code3', 'like', "%{$searchValue}%")
                                ->orWhere('code4', 'like', "%{$searchValue}%")
                                ->orWhere('name', 'like', "%{$searchValue}%"); // Search in description too
                        });
                    }
                })
                ->addColumn('stock', function ($product) {
                    $latestStock = DB::table('stocks')->where('id_product', $product->id)->latest()->first();
                    return $latestStock ? $latestStock->quantity : 0;
                })
                ->addColumn('url1', 'products.url1')
                ->addIndexColumn()
                ->rawColumns(['url1'])
                ->make(true);
        }
        return view('index'); // This might not be necessary if this method is only for AJAX
    }
    public function storeProduct(Request $request)
    {
        try {
            $productId = $request->id;
            if ($productId == NULL || $productId == '') {
                $request->validate([
                    'code' => [
                        'required',
                        Rule::unique('products'),
                        function ($attribute, $value, $fail) {
                            if (Product::where('code2', $value)->exists()) {
                                $fail('EL codigo ya existe en codigo UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code3', $value)->exists()) {
                                $fail('EL codigo ya existe en codigo EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code4', $value)->exists()) {
                                $fail('El codigo ya existe en codigo Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code_fraction', $value)->exists()) {
                                $fail('El codigo ya existe en codigo Fracción.');
                            }
                        },
                        'different:code2',
                        'different:code3',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code2' => [
                        'nullable',
                        Rule::unique('products'),
                        function ($attribute, $value, $fail) {
                            if (Product::where('code', $value)->exists()) {
                                $fail('El codigo UPC ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code3', $value)->exists()) {
                                $fail('El codigo UPC ya existe en codigo EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code4', $value)->exists()) {
                                $fail('El codigo UPC ya existe en codigo Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code_fraction', $value)->exists()) {
                                $fail('El codigo UPC ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code3',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code3' => [
                        'nullable',
                        Rule::unique('products'),
                        function ($attribute, $value, $fail) {
                            if (Product::where('code', $value)->exists()) {
                                $fail('El codigo EAN ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code2', $value)->exists()) {
                                $fail('El codigo EAN ya existe en codigo UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code4', $value)->exists()) {
                                $fail('El codigo EAN ya existe en codigo Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code_fraction', $value)->exists()) {
                                $fail('El codigo EAN ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code4' => [
                        'nullable',
                        Rule::unique('products'),
                        function ($attribute, $value, $fail) {
                            if (Product::where('code', $value)->exists()) {
                                $fail('El codigo Alternativo ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code2', $value)->exists()) {
                                $fail('El codigo Alternativo ya existe en codigo UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code3', $value)->exists()) {
                                $fail('El codigo Alternativo ya existe en codigo EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code_fraction', $value)->exists()) {
                                $fail('El codigo Alternativo ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code3',
                        'different:code_fraction',
                    ],
                    'code_fraction' => [
                        'nullable',
                        Rule::unique('products'),
                        function ($attribute, $value, $fail) {
                            if (Product::where('code', $value)->exists()) {
                                $fail('El codigo Fracción ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code2', $value)->exists()) {
                                $fail('El codigo Fracción ya existe en codigo UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code3', $value)->exists()) {
                                $fail('El codigo Fracción ya existe en codigo EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) {
                            if (Product::where('code4', $value)->exists()) {
                                $fail('El codigo Fracción ya existe en codigo Alternativo.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code3',
                        'different:code4',
                    ],
                    'name' => [
                        'required',
                        Rule::unique('products'),
                    ],
                    'description'  => 'required|min:3|max:400|string',
                    'cost'  => 'required',
                    'utility'  => 'required',
                    'price'  => 'required',
                    'id_product' => Rule::requiredIf($request->type == 'SERVICIO'),
                ]);
                $product   =   Product::create(
                    [
                        'code' => $request->code,
                        'code2' => $request->code2,
                        'code3' => $request->code3,
                        'code4' => $request->code4,
                        'name' => $request->name,
                        'description' => $request->description,
                        'cost'  => $request->cost,
                        'utility'  => $request->utility,
                        'price'  => $request->price,
                        'stock_min'  => $request->stock_min,
                        'serial' => $request->serial,
                        'stock' => $request->stock,
                        'type' => $request->type,
                        'code_fraction' => $request->code_fraction,
                        'equivalence_fraction' => $request->equivalence_fraction,
                        'name_fraction' => $request->name_fraction,
                        'price_fraction' => $request->price_fraction,
                        'status' => '1'
                    ]
                );
                if ($request->file('url')) {
                    $i = 1;
                    foreach ($request->file('url') as $image) {
                        $path = $image->store('products', 'public');
                        $product->update(['url' . $i => $path]);
                        $i++;
                    }
                }
                if ($request->type == 'SERVICIO' || $request->type == 'INTEGRAL') {
                    foreach ($request->id_product as $id_product) {
                        ProductIntegral::create([
                            'id_productI' =>  $product->id,
                            'id_product' => $id_product,
                            'quantity' => isset($request->quantity[$id_product]) ? $request->quantity[$id_product] : 1,
                            'is_fraction' => isset($request->is_fraction[$id_product]) ? $request->is_fraction[$id_product] : 0,
                        ]);
                    }
                }
                if ($request->id_category) {
                    foreach ($request->id_category as $id_category) {
                        $category = AddCategory::Create([
                            'id_category' => $id_category,
                            'id_product' => $product->id,
                        ]);
                    }
                }
            } else {
                $product = Product::find($request->id);
                if (!$product) {
                    abort(404);
                }
                $request->validate([
                    'code' => [
                        'required',
                        Rule::unique('products')->ignore($request->id),
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code2', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo ya existe en codigo UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code3', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo ya existe en codigo EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code4', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo ya existe en codigo Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code_fraction', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo ya existe en codigo Fracción.');
                            }
                        },
                        'different:code2',
                        'different:code3',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code2' => [
                        'nullable',
                        Rule::unique('products')->ignore($request->id),
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo UPC ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code3', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo UPC ya existe en EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code4', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El C ya existe en Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code_fraction', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo UPC ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code3',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code3' => [
                        'nullable',
                        Rule::unique('products')->ignore($request->id),
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo EAN ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code2', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo EAN ya existe en UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code4', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo EAN ya existe en Alternativo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code_fraction', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo EAN ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code4',
                        'different:code_fraction',
                    ],
                    'code4' => [
                        'nullable',
                        Rule::unique('products')->ignore($request->id),
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Alternativo ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code2', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Alternativo ya existe en UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code3', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Alternativo ya existe en EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code_fraction', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('EL codigo Alternativo ya existe en codigo Fracción.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code3',
                        'different:code_fraction',
                    ],
                    'code_fraction' => [
                        'nullable',
                        Rule::unique('products')->ignore($request->id),
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Fracción ya existe en codigo.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code2', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Fracción ya existe en UPC.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code3', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Fracción ya existe en EAN.');
                            }
                        },
                        function ($attribute, $value, $fail) use ($request) {
                            if (Product::where('code4', $value)
                                ->where('id', '!=', $request->id) // Ignorar el ID actual
                                ->exists()
                            ) {
                                $fail('El codigo Fracción ya existe en Alternativo.');
                            }
                        },
                        'different:code',
                        'different:code2',
                        'different:code3',
                        'different:code4',
                    ],
                    'name' => [
                        'required',
                        Rule::unique('products')->ignore($request->id),
                    ],
                    'description'  => 'required|min:3|max:400|string',
                    'id_inventory'  => 'required',
                    'cost'  => 'required',
                    'utility'  => 'required',
                    'price'  => 'required',
                    'id_product' => Rule::requiredIf($request->type == 'SERVICIO'),
                ]);
                $product->update(
                    [
                        'code' => $request->code,
                        'code2' => $request->code2,
                        'code3' => $request->code3,
                        'code4' => $request->code4,
                        'name' => $request->name,
                        'description' => $request->description,
                        'cost'  => $request->cost,
                        'utility'  => $request->utility,
                        'stock_min' => $request->stock_min,
                        'price'  => $request->price,
                        'serial' => $request->serial,
                        'stock' => $request->stock,
                        'type' => $request->type,
                        'code_fraction' => $request->code_fraction,
                        'name_fraction' => $request->name_fraction,
                        'equivalence_fraction' => $request->equivalence_fraction,
                        'price_fraction' => $request->price_fraction,
                    ]
                );
                if ($request->file('url')) {
                    foreach ($request->file('url') as $index => $image) {
                        $columnName = 'url' . ($index + 1); // Usar $index + 1 porque $i empieza en 1

                        // Si ya existe una imagen en esta columna, la eliminamos del storage
                        if ($product->$columnName && Storage::disk('public')->exists($product->$columnName)) {
                            Storage::disk('public')->delete($product->$columnName);
                        }

                        $path = $image->store('products', 'public');
                        $product->update([$columnName => $path]);
                    }
                } else {
                    // Si no se subió una nueva imagen, mantenemos las URLs existentes
                    $product->update([
                        'url1' => $product->url1,
                        'url2' => $product->url2,
                        'url3' => $product->url3,
                    ]);
                }
                ProductIntegral::where('id_productI', $product->id)->delete();
                if ($request->type == 'SERVICIO' || $request->type == 'INTEGRAL') {
                    foreach ($request->id_product as $id_product) {
                        ProductIntegral::create([
                            'id_productI' =>  $product->id,
                            'id_product' => $id_product,
                            'quantity' => isset($request->quantity[$id_product]) ? $request->quantity[$id_product] : 1,
                            'is_fraction' => isset($request->is_fraction[$id_product]) ? $request->is_fraction[$id_product] : 0,
                        ]);
                    }
                }
                if ($request->stock == 1) {
                    if ($request->existencia != '' || $request->existencia != null) {
                        $latestStock = DB::table('stocks')->where('id_product', $product->id)->where('id_inventory', $request->id_inventory)->latest()->first();
                        if ($latestStock) {
                            if ($request->existencia != $latestStock->quantity) {
                                if ($request->existencia > $latestStock->quantity) {
                                    $quantity = $request->existencia - $latestStock->quantity;
                                    Stock::Create(
                                        [
                                            'id_product' => $product->id,
                                            'id_user' => auth()->id(),
                                            'id_inventory'  => $request->id_inventory,
                                            'addition' => $quantity,
                                            'subtraction' => 0,
                                            'quantity' => $request->existencia,
                                            'description' => 'Editar producto',
                                        ]
                                    );
                                } else {
                                    $quantity = $latestStock->quantity - $request->existencia;
                                    Stock::Create(
                                        [
                                            'id_product' => $product->id,
                                            'id_user' => auth()->id(),
                                            'id_inventory'  => $request->id_inventory,
                                            'addition' => 0,
                                            'subtraction' => $quantity,
                                            'quantity' => $request->existencia,
                                            'description' => 'Editar producto',
                                        ]
                                    );
                                }
                            }
                        } else {
                            Stock::Create(
                                [
                                    'id_product' => $product->id,
                                    'id_user' => auth()->id(),
                                    'id_inventory'  => $request->id_inventory,
                                    'addition' => $request->existencia,
                                    'subtraction' => 0,
                                    'quantity' => $request->existencia,
                                    'description' => 'Editar producto',
                                ]
                            );
                        }
                    }
                }
                if ($request->id_category) {
                    $category = AddCategory::where('id_product', $product->id)
                        ->delete();
                    foreach ($request->id_category as $id_category) {
                        $category = AddCategory::Create([
                            'id_category' => $id_category,
                            'id_product' => $product->id,
                        ]);
                    }
                }
            }
            return Response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al guardar el producto',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    public function storeLabel(Request $request)
    {
        $id  = $request->id;
        $quantity = $request->quantity;
        return Response()->json(['id' => $id, 'quantity' => $quantity]);
    }
    public function storeLabelAll(Request $request)
    {
        try {
            DB::statement("SET SQL_MODE=''");
            $code  = $request->code;
            // Consulta principal para obtener los stocks
            $stock = Stock::select('stocks.*', 'products.name as product_name', 'products.code as product_code') // Selecciona los campos necesarios
                ->join('products', 'stocks.id_product', '=', 'products.id') // Realiza el JOIN
                ->where('stocks.id_shopping', $code)
                ->get();
            if ($stock->isEmpty()) {
                return response()->json([
                    'status' => 'error', // Indica que es un error
                    'message' => 'No se encontraron registros de stock para este código y compañía.'
                ], 404);
            }
            $diff = 'no';
            foreach ($stock as $item) {
                $stockAnterior = Stock::where('id_product', $item->id_product)
                    ->where('id', '<', $item->id) // Stock con ID menor (anterior)
                    ->orderBy('id', 'desc') // Ordena descendente para obtener el más reciente
                    ->first();
                $diferencia = $item->quantity;
                if ($stockAnterior) {
                    $diferencia = $item->quantity - $stockAnterior->quantity;
                }
                if ($diferencia > 0) {
                    $diff = 'si';
                }
                if ($diff != 'no') {
                    break;
                }
            }
            if ($diff == 'no') {
                return response()->json([
                    'status' => 'error', // Indica que es un error
                    'message' => 'No hay stock positivos con este codigo.'
                ], 404);
            }

            return Response()->json(['code' => $code]);
        } catch (\Exception $e) {
            // Manejo de errores (importante para depuración)
            return response()->json(['message' => 'Error al obtener datos del stock: ' . $e->getMessage()], 500);
        }
    }
public function editProduct(Request $request)
{
    $where = array('id' => $request->id);
    $product  = Product::where($where)->first();
    if ($product->type == 'SERVICIO' || $product->type == 'INTEGRAL') {
        $productI  = ProductIntegral::where('id_productI', $product->id)->get();
    } else {
        $productI = null;
    }
    $latestStock = DB::table('stocks')->where('id_product', $product->id)->where('id_inventory', $request->id_inventory)->latest()->first();
    $categories  = AddCategory::where('id_product', $product->id)->get();

    // Prepara arrays para cantidades y modos fraccionados
    $quantities = [];
    $modosFraccion = [];
    if ($productI) {
        foreach ($productI as $pi) {
            $quantities[$pi->id_product] = $pi->quantity;
            $modosFraccion[$pi->id_product] = $pi->is_fraction == 1 ? 'fraccion' : 'completo';
        }
    }

    return Response()->json([
        'product' => $product,
        'productI' => $productI,
        'categories' => $categories,
        'quantity' => $latestStock,
        'quantities' => $quantities,
        'modosFraccion' => $modosFraccion
    ]);
}
    public function destroyProduct(Request $request)
    {
        $product = Product::where('id', $request->id)->delete();
        return Response()->json($product);
    }
    public function statusProduct(Request $request)
    {
        $product = Product::find($request->id);
        if ($product->status == '1') {
            $product->update(['status' => '0']);
        } else {
            $product->update(['status' => '1']);
        }
        return Response()->json($product);
    }
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'id' => 'required|exists:products,id', // Validate product ID
            'url' => 'required|in:url1,url2,url3', // Validate url field
        ]);

        $base64Image = $request->input('image');
        $imageData = base64_decode($base64Image);

        if ($imageData === false) {
            return response()->json(['error' => 'Invalid base64 image data'], 400);
        }

        $product = Product::find($request->id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $imageColumn = $request->url; // Get the column name to update (e.g., 'url1', 'url2')

        // Delete the old image if it exists
        if ($product->$imageColumn && Storage::disk('public')->exists($product->$imageColumn)) {
            Storage::disk('public')->delete($product->$imageColumn);
        }

        $filename = uniqid() . '.jpg';
        $newImagePath = 'products/' . $filename;

        // Store the new image
        $path = Storage::disk('public')->put($newImagePath, $imageData);

        if (!$path) {
            return response()->json(['error' => 'Failed to store new image'], 500);
        }

        // Update the product record with the new image path
        $product->update([$imageColumn => $newImagePath]);

        return response()->json($product->id);
    }
    public function deleteProductImage(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:products,id',
            'url' => 'required|in:url1,url2,url3',
        ]);
        $product = Product::find($request->id);
        $column = $request->url;
        if ($product->$column && Storage::disk('public')->exists($product->$column)) {
            Storage::disk('public')->delete($product->$column);
        }
        $product->$column = null;
        $product->save();
        return response()->json(['success' => true]);
    }
}
