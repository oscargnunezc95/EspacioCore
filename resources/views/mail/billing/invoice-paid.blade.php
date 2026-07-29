<x-mail::message>
# ✅ Pago de Factura Confirmado

Hola, **{{ $studio->user->name }}** 👋

Tu pago de la factura de comisiones de **EstadoPrisma** correspondiente al período **{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->translatedFormat('F Y') }}** ha sido **confirmado exitosamente**.

<x-mail::panel>
### 📊 Detalle de la Factura

| Concepto | Monto |
|---|---|
| Período facturado | **{{ \Carbon\Carbon::createFromFormat('Y-m', $invoice->billing_period)->translatedFormat('F Y') }}** |
| Ventas brutas del mes | ${{ number_format($invoice->gross_sales, 0, ',', '.') }} |
| Comisión calculada (5%) | ${{ number_format($invoice->calculated_commission, 0, ',', '.') }} |
| Piso mínimo aplicado | ${{ number_format($invoice->minimum_floor, 0, ',', '.') }} |

@if($invoice->founder_savings > 0)
| 💚 Ahorro Fundador este mes | **-${{ number_format($invoice->founder_savings, 0, ',', '.') }}** |
@endif

---

**💰 Total Pagado: ${{ number_format($invoice->total_due, 0, ',', '.') }}**

📅 **Fecha de pago:** {{ $invoice->paid_at?->translatedFormat('d \d\e F Y \a \l\a\s H:i') }}
</x-mail::panel>

@if($studio->isFounderActive())
> 👑 **Beneficio Fundador Activo** — Tu comisión mensual nunca superará los **${{ number_format($invoice->minimum_floor, 0, ',', '.') }}**. Te quedan **{{ $studio->founder_cycles_remaining }} meses** de este beneficio exclusivo.
@endif

Tu estudio está **al día y sin restricciones**. Todas las funcionalidades de gestión, reservas y pagos están habilitadas con normalidad.

<x-mail::button :url="route('account.billing', ['subdomain' => $studio->subdomain])">
Ver Panel de Facturación
</x-mail::button>

Gracias por ser parte de EstadoPrisma ❤️
</x-mail::message>
