<x-admin-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-stone-900">Planes de Suscripción (Deprecado)</h2>
        <p class="mt-1 text-sm text-stone-500">El sistema de planes fue reemplazado por Facturación por Uso.</p>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
                <div class="text-4xl mb-4">📢</div>
                <h3 class="text-lg font-black text-amber-800 mb-2">Sistema de Planes Descontinuado</h3>
                <p class="text-sm text-amber-700 leading-relaxed max-w-md mx-auto">
                    El sistema de suscripciones SaaS con planes fijos (<strong>PreApproval de Mercado Pago</strong>)
                    fue reemplazado en julio 2026 por el nuevo modelo de
                    <strong>Facturación Mensual por Uso (Floor-Capped Usage Pricing)</strong>.
                </p>
                <div class="mt-6 p-4 bg-white rounded-xl border border-amber-200 text-left text-sm text-stone-600 space-y-2">
                    <p>✅ <strong>Comisión fija del 5%</strong> sobre ventas brutas mensuales.</p>
                    <p>✅ <strong>Piso mínimo de $15.000</strong> (prorrateado para estudios nuevos).</p>
                    <p>✅ <strong>Beneficio Founder</strong>: la comisión nunca supera los $15.000.</p>
                    <p>✅ <strong>Facturación automática</strong> el día 1 de cada mes vía <code class="text-xs bg-stone-100 px-1 py-0.5 rounded">billing:generate</code>.</p>
                </div>
                <p class="mt-4 text-xs text-stone-400">
                    Esta sección se conserva por referencia histórica. La creación/edición de planes ya no está disponible.
                </p>
            </div>
        </div>
    </div>
</x-admin-layout>
