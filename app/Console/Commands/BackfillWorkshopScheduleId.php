<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillWorkshopScheduleId extends Command
{
    protected $signature = 'estadoprisma:backfill-schedule-id';
    protected $description = 'Rellena workshop_schedule_id en class_sessions existentes usando el JOIN con workshop_schedules';

    public function handle()
    {
        // SQLite: strftime('%w', date) = 0 (Dom) a 6 (Sáb), igual que day_of_week en workshop_schedules
        $updated = DB::update("
            UPDATE class_sessions
            SET workshop_schedule_id = (
                SELECT ws.id
                FROM workshop_schedules ws
                WHERE ws.workshop_id = class_sessions.workshop_id
                  AND ws.start_time = class_sessions.start_time
                  AND ws.day_of_week = CAST(strftime('%w', class_sessions.date) AS INTEGER)
                LIMIT 1
            )
            WHERE workshop_schedule_id IS NULL
              AND EXISTS (
                SELECT 1 FROM workshop_schedules ws2
                WHERE ws2.workshop_id = class_sessions.workshop_id
                  AND ws2.start_time = class_sessions.start_time
                  AND ws2.day_of_week = CAST(strftime('%w', class_sessions.date) AS INTEGER)
              )
        ");

        $this->info("Backfill completado. {$updated} sesiones actualizadas.");
    }
}
