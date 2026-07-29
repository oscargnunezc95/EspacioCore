@component('mail::message')
# 🏢 ¡Un estudio ha pagado su mensualidad!

El sistema ha registrado exitosamente el pago de una factura de uso de plataforma. 
El subdominio del estudio se mantiene 100% operativo.

**Detalles de la Facturación:**
* **Estudio:** {{ $studio->name }}
* **Período Facturado:** {{ $invoice->billing_period }}
* **Ventas Brutas del Mes:** ${{ number_format($invoice->gross_sales, 0, ',', '.') }}
* **Ingreso para la Plataforma:** **${{ number_format($invoice->total_due, 0, ',', '.') }}**

@if($studio->is_founder)
* **Nota:** Este estudio tiene el beneficio Founder activo (Le quedan {{ $studio->founder_cycles_remaining }} ciclos).
@endif

---
*Este es un mensaje automático de control interno de EstadoPrisma.*
@endcomponent