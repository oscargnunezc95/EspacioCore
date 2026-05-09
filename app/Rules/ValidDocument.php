<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Services\DocumentService;
use App\Models\Country;

class ValidDocument implements ValidationRule
{
    protected $countryCode;

    public function __construct($countryCode = 'CL')
    {
        $this->countryCode = $countryCode;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!DocumentService::isValid($value, $this->countryCode)) {
            
            // Un toque extra de Arquitecto: Obtener el nombre correcto del documento (RUT, DNI, etc)
            $country = Country::where('code', $this->countryCode)->first();
            $label = $country ? $country->tax_id_label : 'documento de identidad';
            
            $fail("El {$label} ingresado no tiene un formato válido o no existe.");
        }
    }
}