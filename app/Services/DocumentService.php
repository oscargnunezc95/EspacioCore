<?php

namespace App\Services;

use App\Models\Country;

class DocumentService
{
    /**
     * Estandariza el documento quitando caracteres basura ANTES de guardar.
     */
    public static function standardize($document, $countryCode = null) // Quitamos el = 'CL'
    {
        if (empty($document)) return null;

        if ($countryCode === 'CL') {
            return preg_replace('/[^0-9K]/', '', strtoupper($document));
        }

        // Genérico para el resto del mundo (Y para cuando no hay país definido)
        return preg_replace('/[^A-Z0-9]/', '', strtoupper($document));
    }

    /**
     * Valida la estructura usando tu tabla 'countries' y la matemática.
     */
    public static function isValid($document, $countryCode = 'CL')
    {
        if (empty($document)) return true;

        $country = Country::where('code', $countryCode)->first();

        // 1. VALIDACIÓN POR REGEX (Desde tu base de datos)
        if ($country && !empty($country->tax_id_regex)) {
            // Nota: Asegúrate de que el regex en la BD acepta el formato "limpio"
            $regex = '/' . trim($country->tax_id_regex, '/') . '/i';
            if (!preg_match($regex, $document)) {
                return false;
            }
        }

        // 2. VALIDACIÓN MATEMÁTICA ESTRICTA (Módulo 11)
        if ($countryCode === 'CL') {
            return self::validateChileanRut($document);
        }

        return true; 
    }

    /**
     * Algoritmo del Módulo 11 para RUT Chileno
     */
    private static function validateChileanRut($rut)
    {
        // Por si acaso, lo limpiamos de nuevo internamente
        $rut = preg_replace('/[^0-9K]/', '', strtoupper($rut)); 
        
        if (strlen($rut) < 8 || strlen($rut) > 9) return false;

        $numero = substr($rut, 0, -1);
        $dvEsperado = substr($rut, -1);

        $suma = 0;
        $multiplo = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += $numero[$i] * $multiplo;
            $multiplo = $multiplo < 7 ? $multiplo + 1 : 2;
        }

        $dvCalculado = 11 - ($suma % 11);
        $dvCalculado = $dvCalculado == 11 ? '0' : ($dvCalculado == 10 ? 'K' : (string)$dvCalculado);

        return $dvEsperado === $dvCalculado;
    }
    
    /**
     * Formatea visualmente el documento según el país (Solo lectura).
     */
    public static function format($document, $countryCode = 'CL')
    {
        if (empty($document)) {
            return '—';
        }

        if ($countryCode === 'CL') {
            // Lógica visual para Chile (Ej: 191234567 -> 19.123.456-7)
            if (strlen($document) >= 8 && strlen($document) <= 9) {
                $dv = substr($document, -1);
                $numero = substr($document, 0, -1);
                return number_format((int)$numero, 0, ',', '.') . '-' . strtoupper($dv);
            }
        }

        // Si agregas formato para Argentina (DNI: XX.XXX.XXX) iría aquí:
        // if ($countryCode === 'AR') { ... }

        // Retorno genérico si no hay un formato específico o no es de Chile
        return $document;
    }
}