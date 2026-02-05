<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Customer::query()
            ->withCount('sales')
            ->withSum('sales', 'total');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->wantsJson()) {
            $customers = $query->orderBy('name')->limit(20)->get();

            return response()->json([
                'data' => $customers->map(fn ($customer) => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'points' => $customer->points,
                    'sales_count' => $customer->sales_count,
                ]),
            ]);
        }

        $customers = $query->orderBy('name')->paginate(15);

        return view('pos.customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('pos.customers.create');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil ditambahkan',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'points' => $customer->points,
                ],
            ]);
        }

        return redirect()->route('pos.customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan');
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->loadCount('sales');
        $customer->loadSum('sales', 'total');

        $recentSales = $customer->sales()
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'address' => $customer->address,
            'points' => $customer->points,
            'birth_date' => $customer->birth_date?->format('d M Y'),
            'sales_count' => $customer->sales_count,
            'total_spent' => $customer->sales_sum_total ?? 0,
            'recent_sales' => $recentSales->map(fn ($sale) => [
                'id' => $sale->id,
                'invoice_number' => $sale->invoice_number,
                'total' => $sale->total,
                'date' => $sale->created_at->format('d M Y H:i'),
            ]),
        ]);
    }

    public function edit(Customer $customer): View
    {
        return view('pos.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',
            'birth_date' => 'nullable|date',
        ]);

        $customer->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pelanggan berhasil diperbarui',
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'points' => $customer->points,
                ],
            ]);
        }

        return redirect()->route('pos.customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui');
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->get('q', '');

        $customers = Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $customers->map(fn ($customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'points' => $customer->points,
            ]),
        ]);
    }
}
