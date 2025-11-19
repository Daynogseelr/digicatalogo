<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryDataTable;
use App\Models\Inventory;
use App\Models\Employee;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function indexInventory(InventoryDataTable $dataTable)
    {
        if (auth()->user()->type == 'ADMINISTRADOR' || auth()->user()->type == 'EMPRESA' || auth()->user()->type == 'ADMINISTRATIVO') {
            return $dataTable->render('inventories.inventory');
        } else {
            return redirect()->route('indexStore');
        }
    }

    public function storeInventory(Request $request)
    {
        $request->validate([
            'name' => 'required|min:2|max:50|string',
            'description'  => 'required|min:5|max:200|string'
        ]);
        $inventoryId = $request->id;
        $inventory = Inventory::updateOrCreate(
            ['id' => $inventoryId],
            [
                'name' => $request->name,
                'description' => $request->description,
                'status' => '1',
            ]);
        return Response()->json($inventory);
    }

    public function editInventory(Request $request)
    {
        $where = array('id' => $request->id);
        $inventory  = Inventory::where($where)->first();
        return Response()->json($inventory);
    }

    public function destroyInventory(Request $request)
    {
        $inventory = Inventory::where('id',$request->id)->delete();
        return Response()->json($inventory);
    }

    public function statusInventory(Request $request)
    {
        $inventory = Inventory::find($request->id);
        if ($inventory->status == '1') {
            $inventory->update(['status' => '0']);
        } else {
            $inventory->update(['status' => '1']);
        }
        return Response()->json($inventory);
    }
}