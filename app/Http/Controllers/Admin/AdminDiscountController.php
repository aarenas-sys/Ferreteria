<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Discount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class AdminDiscountController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        $discounts = Discount::with('branches')->paginate(10);

        return view('admin.discounts.index', compact('discounts'));
    }

    public function create(): \Illuminate\View\View
    {
        $branches = Branch::all();

        return view('admin.discounts.create', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'active' => ['required', Rule::in(['0', '1'])],
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $branches = $validated['branches'] ?? [];
        unset($validated['branches']);

        $validated['active'] = $request->input('active') === '1';

        $discount = Discount::create($validated);
        $discount->branches()->sync($branches);

        return Redirect::route('admin.discounts.index')->with('success', 'Descuento creado correctamente.');
    }

    public function show(Discount $discount): \Illuminate\View\View
    {
        return view('admin.discounts.show', compact('discount'));
    }

    public function edit(Discount $discount): \Illuminate\View\View
    {
        $branches = Branch::all();

        return view('admin.discounts.edit', compact('discount', 'branches'));
    }

    public function update(Request $request, Discount $discount): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['percentage', 'fixed'])],
            'value' => ['required', 'numeric', 'min:0'],
            'active' => ['required', Rule::in(['0', '1'])],
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'branches' => 'nullable|array',
            'branches.*' => 'exists:branches,id',
        ]);

        $branches = $validated['branches'] ?? [];
        unset($validated['branches']);

        $validated['active'] = $request->input('active') === '1';

        $discount->update($validated);
        $discount->branches()->sync($branches);

        return Redirect::route('admin.discounts.index')->with('success', 'Descuento actualizado correctamente.');
    }

    public function destroy(Discount $discount): RedirectResponse
    {
        $discount->branches()->detach();
        $discount->delete();

        return Redirect::route('admin.discounts.index')->with('success', 'Descuento eliminado correctamente.');
    }
}
