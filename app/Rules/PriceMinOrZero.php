<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida que un precio numérico sea exactamente 0 (gratis)
 * o al menos 100. MercadoPago no permite procesar pagos
 * de montos entre 1 y 99 (su API rechaza la transacción),
 * por lo que los precios en ese rango están prohibidos.
 */
class PriceMinOrZero implements ValidationRule
{
    /**
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_numeric($value)) {
            return; // deja que required|numeric lo maneje primero
        }

        $value = (float) $value;

        if ($value > 0 && $value < 100) {
            $fail(
                "El :attribute debe ser \$0 (gratuito) o al menos \$100. "
                . "MercadoPago no procesa pagos entre \$1 y \$99 — "
                . "si pones un precio en ese rango, las alumnas no podrán pagar online."
            );
        }
    }
}
