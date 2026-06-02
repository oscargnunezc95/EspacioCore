<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\SupportInquiryMail;
use App\Mail\DemoScheduledMail;

class SupportService
{
    /**
     * Procesa una consulta general.
     */
    public function handleInquiry(array $data): void
    {
        $adminEmail = 'oscar@estadoprisma.test';

        Mail::to($adminEmail)->send(new SupportInquiryMail(
            name: $data['name'],
            email: $data['email'],
            body: $data['message'] ?? null,
        ));
    }

    /**
     * Procesa una solicitud de demo/videollamada.
     */
    public function handleDemo(array $data): void
    {
        $adminEmail = 'oscar@estadoprisma.test';
        $jitsiLink = $this->generateJitsiLink();

        // Copia al usuario (prospecto)
        Mail::to($data['email'])->send(new DemoScheduledMail(
            name: $data['name'],
            date: $data['meeting_date'],
            time: $data['meeting_time'],
            jitsiLink: $jitsiLink,
            body: $data['message'] ?? null,
        ));

        // Copia al admin
        Mail::to($adminEmail)->send(new DemoScheduledMail(
            name: $data['name'],
            email: $data['email'],
            date: $data['meeting_date'],
            time: $data['meeting_time'],
            jitsiLink: $jitsiLink,
            body: $data['message'] ?? null,
            isAdminCopy: true,
        ));
    }

    /**
     * Genera un enlace único de Jitsi Meet.
     */
    public function generateJitsiLink(): string
    {
        $random = Str::random(10);
        return "https://meet.jit.si/EstadoPrisma-Demo-{$random}";
    }
}
