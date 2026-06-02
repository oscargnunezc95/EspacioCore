<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSupportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'type'         => ['required', Rule::in(['inquiry', 'demo'])],
            'message'      => ['required_if:type,inquiry', 'nullable', 'string'],
            'meeting_date' => ['required_if:type,demo', 'nullable', 'date', 'after:today'],
            'meeting_time' => [
                'required_if:type,demo',
                'nullable',
                Rule::in($this->allowedHours()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'            => 'El nombre es obligatorio.',
            'email.required'           => 'El correo electrónico es obligatorio.',
            'email.email'              => 'El correo electrónico no es válido.',
            'type.required'            => 'Debes seleccionar el tipo de solicitud.',
            'type.in'                  => 'El tipo de solicitud no es válido.',
            'message.required_if'      => 'El mensaje es obligatorio para consultas.',
            'meeting_date.required_if' => 'La fecha de reunión es obligatoria para agendar una demo.',
            'meeting_date.after'       => 'La fecha debe ser a partir de mañana.',
            'meeting_time.required_if' => 'La hora de reunión es obligatoria para agendar una demo.',
            'meeting_time.in'          => 'La hora debe ser una hora entera entre las 09:00 y las 22:00.',
        ];
    }

    /**
     * Genera el array de horas permitidas: 09:00, 10:00, ..., 22:00
     */
    protected function allowedHours(): array
    {
        return array_map(
            fn (int $h) => sprintf('%02d:00', $h),
            range(9, 22),
        );
    }
}
