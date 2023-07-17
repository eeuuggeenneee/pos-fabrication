<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request) {
        $orders = new Order(); 
        $selectedCustomerId = $request->input('customer_id', 'all');

            if($request->start_date) {
                $orders = $orders->where('created_at', '>=', $request->start_date);
            }
            if($request->end_date) {
                $orders = $orders->where('created_at', '<=', $request->end_date . ' 23:59:59');
            }
            if($request->customer_id){
                $orders =  $orders->where('customer_id', $selectedCustomerId);
            }

            
        $orders = $orders->with(['items', 'payments', 'customer'])->latest()->paginate(10);

        $total = $orders->map(function($i) {
            return $i->total();
        })->sum();
        $receivedAmount = $orders->map(function($i) {
            return $i->receivedAmount();
        })->sum();

        return view('orders.index', compact('orders', 'total', 'receivedAmount'));
    }

    public function store(OrderStoreRequest $request)
    {
        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
        ]);

        $cart = $request->user()->cart()->get();
        foreach ($cart as $item) {
            $order->items()->create([
                'price' => $request->netTotal,
                'quantity' => $item->pivot->quantity,
                'product_id' => $item->id,
                'discount_id' => $request->discount_id,
            ]);
            $item->quantity = $item->quantity - $item->pivot->quantity;
            $item->save();
        }
        $request->user()->cart()->detach();
        $order->payments()->create([
            'amount' => $request->amount,
            'user_id' => $request->user()->id,
        ]);
        return 'success';
    }

    public function customerFilter(Request $request){
        dd($request);
      

        return view('orders.index', compact('orders', 'total', 'receivedAmount'));
    }
    public function destroy(Order $order)
    {
        if ($order->image) {
            Storage::delete($order->image);
        }
        $order->delete();

        return redirect()->route('orders.index')->with('success', 'You have deleted the order.');

    }
}
