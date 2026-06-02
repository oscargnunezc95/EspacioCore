<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupportRequest;
use App\Services\SupportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function __construct(
        protected SupportService $supportService,
    ) {}

    /**
     * Muestra el formulario público de soporte.
     */
    public function create(): View
    {
        return view('support.index');
    }

    /**
     * Procesa la solicitud de soporte.
     */
    public function store(StoreSupportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($data['type'] === 'inquiry') {
            $this->supportService->handleInquiry($data);
        } else {
            $this->supportService->handleDemo($data);
        }

        return redirect()
            ->route('support.create')
            ->with('success', $this->successMessage($data['type']));
    }

    /**
     * Mensaje de éxito según el tipo de solicitud.
     */
    protected function successMessage(string $type): string
    {
        return $type === 'demo'
            ? '✅ ¡Tu demo ha sido agendada! Recibirás un correo con el enlace de videollamada en breve.'
            : '✅ ¡Tu consulta ha sido enviada! Te responderemos a la brevedad.';
    }
}
