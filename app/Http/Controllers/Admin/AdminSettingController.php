<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'iva' => 'required|numeric|min:0|max:100',
        ]);

        Setting::updateOrCreate(['key' => 'iva'], ['value' => $request->iva]);

        return redirect()->route('admin.settings.index')->with('success', 'Configuraciones actualizadas exitosamente.');
    }
}
