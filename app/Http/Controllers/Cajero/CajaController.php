<?php

namespace App\Http\Controllers\Cajero;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cajero\AbrirCajaRequest;
use App\Http\Requests\Cajero\ArqueoCajaRequest;
use App\Services\CajaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CajaController extends Controller
{
    private CajaService $service;

    public function __construct(CajaService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $cajaAbierta = $this->service->obtenerCajaAbierta();
        $cajaCerradaHoy = $this->service->cajaCerradaHoy();

        return view('cajero.caja.index', compact('cajaAbierta', 'cajaCerradaHoy'));
    }

    public function abrirForm()
    {
        $cajaCerradaHoy = $this->service->cajaCerradaHoy();

        return view('cajero.caja.abrir', compact('cajaCerradaHoy'));
    }

    public function abrir(AbrirCajaRequest $request): RedirectResponse
    {
        try {
            $this->service->abrirCaja((float) $request->monto_inicial);

            return redirect()->route('cajero.caja.index')
                ->with('success', 'Caja abierta correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al abrir caja', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function arqueoForm()
    {
        $caja = $this->service->obtenerCajaAbierta();

        if (! $caja) {
            return redirect()->route('cajero.caja.index')
                ->withErrors(['error' => 'No hay caja abierta para realizar el arqueo.']);
        }

        $totales = $this->service->calcularTotales($caja);

        return view('cajero.caja.arqueo', compact('caja', 'totales'));
    }

    public function arqueo(ArqueoCajaRequest $request): RedirectResponse
    {
        $caja = $this->service->obtenerCajaAbierta();

        if (! $caja) {
            return redirect()->route('cajero.caja.index')
                ->withErrors(['error' => 'No hay caja abierta para realizar el arqueo.']);
        }

        try {
            $this->service->arquearCaja($caja, (float) $request->monto_real);

            return redirect()->route('cajero.caja.cierre.form')
                ->with('success', 'Arqueo realizado correctamente. Revise los totales antes de cerrar la caja.');
        } catch (\Exception $e) {
            Log::error('Error en arqueo de caja', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cierreForm()
    {
        $caja = $this->service->obtenerCajaAbierta();

        if (! $caja) {
            return redirect()->route('cajero.caja.index')
                ->withErrors(['error' => 'No hay caja abierta para cerrar.']);
        }

        $totales = $this->service->calcularTotales($caja);

        return view('cajero.caja.cierre', compact('caja', 'totales'));
    }

    public function cerrar(Request $request): RedirectResponse
    {
        $caja = $this->service->obtenerCajaAbierta();

        if (! $caja) {
            return redirect()->route('cajero.caja.index')
                ->withErrors(['error' => 'No hay caja abierta para cerrar.']);
        }

        if ($caja->monto_real === null) {
            return redirect()->route('cajero.caja.arqueo')
                ->withErrors(['error' => 'Debe realizar el arqueo antes de cerrar la caja.']);
        }

        try {
            $this->service->cerrarCaja($caja);

            return redirect()->route('cajero.caja.index')
                ->with('success', 'Caja cerrada correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al cerrar caja', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
