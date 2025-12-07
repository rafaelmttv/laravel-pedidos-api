<?php

namespace App\Http\Controllers;

use App\Events\OrderCreated;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return Order::with(['customer', 'items.product'])
            ->latest()
            ->paginate(10);
    }

    public function store(CreateOrderRequest $request)
    {
        $data = $request->validated();

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
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal,
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

    public function update(UpdateOrderRequest $request, Order $order)
    {
        $order->update($request->validated());
        return $order;
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json([], 204);
    }
}
