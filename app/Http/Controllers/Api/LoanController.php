<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Loan::with(['user', 'items.item'])->latest();

        if ($user->isCustomer()) {
            $query->where('user_id', $user->id);
        }

        return LoanResource::collection($query->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'note' => 'nullable|string',
            'loan_date' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $loan = Loan::create([
                'reason' => $validated['reason'],
                'note' => $validated['note'] ?? null,
                'loan_date' => $validated['loan_date'] ?? null,
                'user_id' => $request->user()->id,
                'status' => 'pending',
            ]);

            foreach ($validated['items'] as $itemData) {
                $item = Item::lockForUpdate()->find($itemData['item_id']);
                if ($item->stock < $itemData['quantity']) {
                    throw new \Exception("Stock for item {$item->name} is insufficient.");
                }

                LoanItem::create([
                    'loan_id' => $loan->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Loan requested successfully',
                'data' => new LoanResource($loan->load(['user', 'items.item']))
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Request $request, Loan $loan)
    {
        if ($request->user()->isCustomer() && $loan->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        return response()->json([
            'success' => true,
            'data' => new LoanResource($loan->load(['user', 'items.item']))
        ]);
    }

    public function update(Request $request, Loan $loan)
    {
        $user = $request->user();
        
        if ($user->isCustomer() && $loan->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'reason' => 'sometimes|required|string',
            'note' => 'nullable|string',
            'loan_date' => 'nullable|date',
        ];

        if ($user->isAdmin()) {
            $rules['status'] = ['sometimes', 'required', Rule::in(['pending', 'approved', 'rejected', 'returned'])];
        }

        $validated = $request->validate($rules);

        if (isset($validated['status']) && $validated['status'] !== $loan->status) {
            try {
                DB::beginTransaction();
                
                $oldStatus = $loan->status;
                $newStatus = $validated['status'];
                
                // Perform the update
                $loan->update($validated);
                
                // Deduct stock if transitioning from pending to approved
                if ($oldStatus === 'pending' && $newStatus === 'approved') {
                    foreach ($loan->items as $loanItem) {
                        $item = Item::lockForUpdate()->find($loanItem->item_id);
                        if ($item->stock < $loanItem->quantity) {
                            throw new \Exception("Stok barang {$item->name} tidak mencukupi untuk disetujui.");
                        }
                        $item->stock -= $loanItem->quantity;
                        $item->save();
                    }
                }
                
                // Restore stock if transitioning from approved to returned or rejected
                if ($oldStatus === 'approved' && in_array($newStatus, ['returned', 'rejected'])) {
                    foreach ($loan->items as $loanItem) {
                        $item = Item::lockForUpdate()->find($loanItem->item_id);
                        $item->stock += $loanItem->quantity;
                        $item->save();
                    }
                }
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
        } else {
            $loan->update($validated);
        }

        return response()->json([
            'success' => true,
            'message' => 'Loan updated successfully',
            'data' => new LoanResource($loan->load(['user', 'items.item']))
        ]);
    }

    public function destroy(Request $request, Loan $loan)
    {
        if ($request->user()->isCustomer() && $loan->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($loan->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending loans can be deleted'
            ], 422);
        }

        $loan->items()->delete();
        $loan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Loan deleted successfully'
        ]);
    }
}
