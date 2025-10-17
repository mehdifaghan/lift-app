<?php

namespace App\Http\Controllers\Api\v1\Warehouse;

use App\Domain\InventoryItems\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q = InventoryItem::query();

        if ($s = $request->input('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('code', 'like', '%' . $s . '%')
                  ->orWhere('name', 'like', '%' . $s . '%');
            });
        }

        $limit = (int) ($request->input('limit', 15));
        return $q->orderByDesc('id')->paginate($limit);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'   => 'required|string|max:255',
            'name'   => 'required|string|max:255',
            'brand'  => 'nullable|string',
            'unit'   => 'nullable|string',
            'stock'  => 'nullable|integer',
        ]);

        $item = InventoryItem::create($data);
        return response()->json($item, 201);
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'code'  => 'sometimes|string|max:255',
            'name'  => 'sometimes|string|max:255',
            'brand' => 'nullable|string',
            'unit'  => 'nullable|string',
        ]);

        $item->update($data);
        return $item;
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return response()->noContent();
    }
}
