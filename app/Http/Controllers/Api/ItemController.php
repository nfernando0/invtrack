<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResources;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    public function index()
    {
        return ItemResources::collection(Item::with('categories')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $item = Item::create([
            'name' => $validated['name'],
            'stock' => $validated['stock'],
            'image' => $validated['image'] ?? null,
        ]);

        $item->categories()->sync($request->category_ids);

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil ditambahkan',
            'data' => new ItemResources($item->load('categories'))
        ], 201);
    }

    public function show(Item $item)
    {
        return response()->json([
            'success' => true,
            'data' => new ItemResources($item->load('categories'))
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'stock' => 'sometimes|required|integer|min:0',
            'category_ids' => 'sometimes|required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) {
                Storage::disk('public')->delete($item->image);
            }
            $validated['image'] = $request->file('image')->store('items', 'public');
        }

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (isset($validated['stock'])) $updateData['stock'] = $validated['stock'];
        if (array_key_exists('image', $validated)) $updateData['image'] = $validated['image'];
        
        if (!empty($updateData)) {
            $item->update($updateData);
        }

        if ($request->has('category_ids')) {
            $item->categories()->sync($request->category_ids);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil diperbarui',
            'data' => new ItemResources($item->load('categories'))
        ]);
    }

    public function destroy(Item $item)
    {
        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }
        
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item berhasil dihapus'
        ]);
    }
}
