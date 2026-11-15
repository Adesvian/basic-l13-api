<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourierRequest;
use App\Http\Requests\UpdateCourierRequest;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search'   => ['nullable', 'string', 'max:150'],
            'level'    => ['nullable', 'regex:/^[1-5](,[1-5])*$/'],
            'sort'     => ['nullable', Rule::in(['name', 'created_at'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Courier::with('level');

        // Search by first name and last name
        if (!empty($validated['search'])) {
            $keywords = preg_split(
                '/\s+/',
                trim($validated['search'])
            );

            foreach ($keywords as $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'LIKE', "%{$keyword}%")
                      ->orWhere('last_name', 'LIKE', "%{$keyword}%");
                });
            }
        }

        // Filter by level
        if (!empty($validated['level'])) {
            $levels = explode(',', $validated['level']);

            $query->whereIn('level_id', $levels);
        }

        // Sorting
        $sort = $validated['sort'] ?? 'name';

        if ($sort === 'created_at') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy('first_name')
                  ->orderBy('last_name');
        }

        // Pagination
        $couriers = $query->paginate(
            $validated['per_page'] ?? 10
        );

        return response()->json([
            'message' => 'Couriers retrieved successfully.',
            'data' => $couriers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourierRequest $request)
    {
        $courier = Courier::create($request->validated());

        return response()->json([
            'message' => 'Courier created successfully.',
            'data' => $courier->load('level'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $courier = Courier::with('level')->find($id);

        if (!$courier) {
            return response()->json([
                'message' => 'Courier not found.',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'message' => 'Courier retrieved successfully.',
            'data' => $courier,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourierRequest $request, string $id)
    {
        $courier = Courier::findOrFail($id);

        $courier->update($request->validated());

        return response()->json([
            'message' => 'Courier updated successfully.',
            'data' => $courier->fresh()->load('level'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $courier = Courier::findOrFail($id);

        $courier->delete();

        return response()->json([
            'message' => 'Courier deleted successfully.',
        ]);
    }
}
