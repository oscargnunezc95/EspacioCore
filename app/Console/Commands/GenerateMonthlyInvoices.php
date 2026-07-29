<?php

namespace App\Console\Commands;

use App\Models\Studio;
use App\Services\BillingService;
use App\Mail\StudioInvoiceGeneratedMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:generate
                            {--period= : Período a facturar en formato YYYY-MM (default: mes anterior)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera las facturas mensuales para todos los estudios activos usando el algoritmo Floor-Capped Usage Pricing.';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService): int
    {
        // Determinar el período a facturar (default: mes recién cerrado)
        $periodOption = $this->option('period');
        if ($periodOption) {
            $period = Carbon::createFromFormat('Y-m', $periodOption)->startOfMonth();
        } else {
            $period = Carbon::now()->subMonth()->startOfMonth();
        }

        $billingPeriod = $period->format('Y-m');

        $this->info("📊 Iniciando generación de facturas para el período {$billingPeriod}...");
        Log::info("📊 Comando billing:generate iniciado para período {$billingPeriod}");

        // Obtener todos los estudios (activos e inactivos; todos facturan si tuvieron ventas)
        $studios = Studio::all();

        $generated = 0;
        $skipped  = 0;
        $errors   = 0;

        $progressBar = $this->output->createProgressBar($studios->count());
        $progressBar->start();

        foreach ($studios as $studio) {
            try {
                // Verificar si ya existe una factura para este período (idempotencia)
                $exists = $studio->invoices()
                    ->where('billing_period', $billingPeriod)
                    ->exists();

                if ($exists) {
                    $this->line("");
                    $this->warn("  ⏭️  Studio #{$studio->id} ({$studio->name}): ya tiene factura para {$billingPeriod}. Saltando.");
                    Log::info("⏭️  Factura {$billingPeriod} ya existe para Studio #{$studio->id}. Saltando.");
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Generar la factura
                $invoice = $billingService->generateInvoice($studio, $period);

                // Encolar correo asíncrono al dueño del estudio
                try {
                    if ($studio->user && $studio->user->email) {
                        Mail::to($studio->user->email)
                            ->queue(new StudioInvoiceGeneratedMail($studio, $invoice));
                        Log::info("📧 Mail encolado para Studio #{$studio->id}: factura {$billingPeriod}");
                    }
                } catch (\Exception $mailException) {
                    Log::error("Error encolando mail de factura para Studio #{$studio->id}: " . $mailException->getMessage());
                    // No detenemos el proceso por un fallo de correo
                }

                $generated++;

                if ($this->output->isVerbose()) {
                    $this->line("");
                    $this->info("  ✅ Studio #{$studio->id} ({$studio->name}):");
                    $this->line("     Ventas Brutas: \${$invoice->gross_sales}");
                    $this->line("     Comisión (5%): \${$invoice->calculated_commission}");
                    $this->line("     Piso Mínimo:   \${$invoice->minimum_floor}");
                    if ($invoice->founder_savings > 0) {
                        $this->line("     💚 Ahorro Founder: \${$invoice->founder_savings}");
                    }
                    $this->line("     TOTAL A PAGAR: \${$invoice->total_due}");
                }
            } catch (\Exception $e) {
                $this->line("");
                $this->error("  ❌ Error en Studio #{$studio->id}: " . $e->getMessage());
                Log::error("❌ Error generando factura para Studio #{$studio->id} período {$billingPeriod}: " . $e->getMessage());
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->line("");
        $this->line("");

        // Resumen final
        $this->info("═══════════════════════════════════════════");
        $this->info("  RESUMEN DE FACTURACIÓN — {$billingPeriod}");
        $this->info("═══════════════════════════════════════════");
        $this->info("  ✅ Facturas generadas: {$generated}");
        $this->info("  ⏭️  Saltadas (ya existían): {$skipped}");
        if ($errors > 0) {
            $this->error("  ❌ Errores: {$errors}");
        }
        $this->info("═══════════════════════════════════════════");

        Log::info("📊 billing:generate completado para {$billingPeriod}: {$generated} generadas, {$skipped} saltadas, {$errors} errores.");

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }
}
