<x-mail::message>
# 🧾 Factura Mensual de Comisiones

Hola, **{{ $studio->user->name }}** 👋

Tu factura de comisiones por uso de **EstadoPrisma** correspondiente al período **{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->translatedFormat('F Y') }}** ya está disponible.

<x-mail::panel>
### 📊 Resumen del Mes

| Concepto | Monto |
|---|---|
| Ventas brutas del mes | ${{ number_format($invoice->gross_sales, 0, ',', '.') }} |
| Comisión calculada (5%) | ${{ number_format($invoice->calculated_commission, 0, ',', '.') }} |
| Piso mínimo aplicado | ${{ number_format($invoice->minimum_floor, 0, ',', '.') }} |

@if($invoice->founder_savings > 0)
| 💚 Ahorro Fundador este mes | **-${{ number_format($invoice->founder_savings, 0, ',', '.') }}** |
@endif

---

**💰 Total a Pagar: ${{ number_format($invoice->total_due, 0, ',', '.') }}**
</x-mail::panel>

@if($studio->isFounderActive())
> 👑 **Beneficio Fundador Activo** — Tu comisión mensual nunca superará los **${{ number_format($invoice->minimum_floor, 0, ',', '.') }}**. Te quedan **{{ $studio->founder_cycles_remaining }} meses** de este beneficio exclusivo.
@endif

📅 **Fecha límite de pago:** {{ $invoice->due_date->translatedFormat('d \d\e F Y') }}

<x-mail::button :url="route('account.billing', ['subdomain' => $studio->subdomain])">
Ir a Facturación
</x-mail::button>

Gracias por confiar en EstadoPrisma ❤️
</x-mail::message>
