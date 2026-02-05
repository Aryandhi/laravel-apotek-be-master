<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Doctor::query()->active();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sip_number', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('hospital_clinic', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $doctors->through(fn ($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'sip_number' => $doctor->sip_number,
                'specialization' => $doctor->specialization,
                'phone' => $doctor->phone,
                'hospital_clinic' => $doctor->hospital_clinic,
            ]),
            'meta' => [
                'current_page' => $doctors->currentPage(),
                'last_page' => $doctors->lastPage(),
                'per_page' => $doctors->perPage(),
                'total' => $doctors->total(),
            ],
        ]);
    }

    public function show(Doctor $doctor): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'sip_number' => $doctor->sip_number,
                'specialization' => $doctor->specialization,
                'phone' => $doctor->phone,
                'hospital_clinic' => $doctor->hospital_clinic,
                'address' => $doctor->address,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sip_number' => 'nullable|string|max:100',
            'specialization' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'hospital_clinic' => 'nullable|string|max:255',
        ]);

        $doctor = Doctor::create([
            ...$validated,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokter berhasil ditambahkan',
            'data' => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'sip_number' => $doctor->sip_number,
                'specialization' => $doctor->specialization,
            ],
        ], 201);
    }
}
