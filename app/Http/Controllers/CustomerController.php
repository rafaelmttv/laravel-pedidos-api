<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::paginate(10);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:30',
        ]);

        $customer = Customer::create($data);

        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        return $customer;
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => "sometimes|email|unique:customers,email,{$customer->id}",
            'phone' => 'nullable|string|max:30',
        ]);

        $customer->update($data);

        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return response()->json([], 204);
    }
}
