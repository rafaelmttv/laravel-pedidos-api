<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        return Customer::paginate(10);
    }

    public function store(CreateProductRequest $request)
    {
        $customer = Customer::create($request->validated());

        return response()->json($customer, 201);
    }

    public function show(Customer $customer)
    {
        return $customer;
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return $customer;
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json([], 204);
    }
}
