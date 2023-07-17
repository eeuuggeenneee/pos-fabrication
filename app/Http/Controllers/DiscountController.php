<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DiscountController extends Controller
{
    //

    public function create()
    {
        $discounts = Discount::all();

        return view('discounts.create', ['discounts' => $discounts]);
    }
    public function show($code)
    {
        $discount = Discount::where('code', $code)->first();
    
        if ($discount && $discount->expires_at >= now()) {
            return response()->json([
                'isValid' => true,
                'discount_id' => $discount->id,
                'code' => $discount->code,
                'discountAmount' => $discount->amount,
            ]);
        } else {
            return response()->json([
                'isValid' => false,
                'message' => 'Invalid discount code.',
            ], 404);
        }
    }
    public function index()
    {
        $discounts = Discount::all();

        return view('discounts.index', ['discounts' => $discounts]);
    }
    public function promocode()
    {
        $promoCodes = Discount::all();
        return response()->json($promoCodes);
    }
    public function edit(Discount $discount)
    {
        return view('discounts.edit', compact('discount'));
    }
    public function update(Request $request, Discount $discount)
    {
        $request->validate([
            'code' => 'required|unique:discounts,code,' . $discount->id,
            'amount' => 'required|numeric|min:0',
            'available_from' => 'required|date',
            'expires_at' => 'required|date|after:available_from',
        ]);

        $discount->update([
            'code' => $request->code,
            'amount' => $request->amount,
            'available_from' => $request->available_from,
            'expires_at' => $request->expires_at,
        ]);

        return redirect()->route('discounts.edit', $discount)
            ->with('success', 'Discount updated successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:discounts',
            'amount' => 'required|numeric|min:0',
            'available_from' => 'required|date',
            'expires_at' => 'required|date|after:available_from',
        ]);
    
        Discount::create([
            'code' => $request->code,
            'amount' => $request->amount,
            'available_from' => $request->available_from,
            'expires_at' => $request->expires_at,
        ]);
    
        return redirect()->route('discounts.index')
            ->with('success', 'Discount code created successfully.');
    }

    public function destroy(Discount $discount)
    {
        if ($discount->avatar) {
            Storage::delete($discount->avatar);
        }

        $discount->delete();

        return redirect()->route('discounts.index')->with('success', 'You have deleted the discount.');
    }
    
}
