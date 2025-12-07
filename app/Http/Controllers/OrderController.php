<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\OrderCreated;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with(['customer', 'items.product'])
            ->latest()
            ->paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($data) {

            $order = Order::create([
                'customer_id' => $data['customer_id'],
                'total' => 0,
                'status' => 'pending',
            ]);

            $total = 0;

            foreach ($data['items'] as $itemData) {
                $product = Product::find($itemData['product_id']);

                if ($product->stock < $itemData['quantity']) {
                    abort(422, "Estoque insuficiente para o produto: {$product->name}");
                }

                $product->decrement('stock', $itemData['quantity']);

                $subtotal = $product->price * $itemData['quantity'];

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $itemData['quantity'],
                    'price'      => $product->price,
                    'subtotal'   => $subtotal,
                ]);

                $total += $subtotal;
            }

            $order->update(['total' => $total]);

            event(new OrderCreated($order));

            return response()->json(
                $order->load(['customer', 'items.product']),
                201
            );
        });
    }

    public function show(Order $order)
    {
        return $order->load(['customer', 'items.product']);
    }

    public function update(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending,paid,canceled'
        ]);

        $order->update(['status' => $data['status']]);

        return $order;
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json([], 204);
    }
}
