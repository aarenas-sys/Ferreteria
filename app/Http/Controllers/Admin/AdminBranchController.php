<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminBranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $branches = Branch::withCount('users')->paginate(10);
        return view('admin.branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBranchRequest $request): RedirectResponse
    {
        Branch::create($request->validated());
        return redirect()->route('admin.branches.index')->with('success', 'Sucursal creada exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch): View
    {
        return view('admin.branches.show', compact('branch'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch): View
    {
        return view('admin.branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($request->validated());
        return redirect()->route('admin.branches.index')->with('success', 'Sucursal actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->users()->count() > 0) {
            return redirect()->route('admin.branches.index')->with('error', 'No se puede eliminar la sucursal porque tiene usuarios asociados.');
        }

        $branch->delete();
        return redirect()->route('admin.branches.index')->with('success', 'Sucursal eliminada exitosamente.');
    }
}
