<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    /**
     * Display a listing of units
     */
    public function index(Request $request)
    {
        $units = Unit::when($request->q, function ($query, $q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('code', 'like', '%' . $q . '%');
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Dashboard/Units/Index', [
            'units' => $units,
        ]);
    }

    /**
     * Show the form for creating a new unit
     */
    public function create()
    {
        return Inertia::render('Dashboard/Units/Create');
    }

    /**
     * Store a newly created unit
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:units,code|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil ditambahkan.');
    }

    /**
     * Show the form for editing a unit
     */
    public function edit(Unit $unit)
    {
        return Inertia::render('Dashboard/Units/Edit', [
            'unit' => $unit,
        ]);
    }

    /**
     * Update the specified unit
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:units,code,' . $unit->id . '|max:20',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('success', 'Satuan berhasil diperbarui.');
    }

    /**
     * Remove the specified unit
     */
    public function destroy(Unit $unit)
    {
        // Check if unit is used by any product
        if ($unit->productUnits()->exists()) {
            return redirect()->route('units.index')->with('error', 'Satuan tidak bisa dihapus karena masih digunakan oleh produk.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Satuan berhasil dihapus.');
    }

    /**
     * Get units for API/AJAX
     */
    public function list()
    {
        return Unit::active()->orderBy('name')->get();
    }
}
