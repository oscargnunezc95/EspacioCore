<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        // Traemos los planes y contamos cuántos estudios activos hay en cada uno
        $plans = SubscriptionPlan::withCount(['studios' => function ($query) {
            $query->whereIn('subscription_status', ['pro', 'elite', 'past_due']);
        }])->orderBy('price', 'asc')->get();

        return view('admin.plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'platform_fee_percent' => 'required|numeric|min:0|max:100',
            'capacity_limit' => 'nullable|integer|min:1',
            'max_billing_cycles' => 'nullable|integer|min:1',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        // Asegurar que el slug sea único
        if (SubscriptionPlan::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . time();
        }

        SubscriptionPlan::create($validated);

        return back()->with('success', 'Plan de suscripción creado exitosamente.');
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'platform_fee_percent' => 'required|numeric|min:0|max:100',
            'capacity_limit' => 'nullable|integer|min:1',
            'max_billing_cycles' => 'nullable|integer|min:1',
        ]);

        // Solo actualizamos el slug si cambió el nombre
        if ($plan->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $plan->update($validated);

        return back()->with('success', 'Plan actualizado exitosamente.');
    }

    public function toggle(SubscriptionPlan $plan)
    {
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'ok' => true,
            'is_active' => $plan->is_active,
            'message' => $plan->is_active ? 'Plan activado.' : 'Plan desactivado.'
        ]);
    }
}