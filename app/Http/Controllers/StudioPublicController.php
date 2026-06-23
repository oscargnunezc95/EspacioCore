<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Services\ExploreService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StudioPublicController extends Controller
{
    public function show(Request $request, $subdomain, ExploreService $exploreService)
    {
        $studio = Studio::where('subdomain', $subdomain)
            ->with([
                'workshops.teacher',
                'workshops.discipline',
                'workshops.prices',
                'promotions.workshopPrices.workshop',

                'classSessions' => function($query) use ($request) {
                    $query->where('date', '>=', Carbon::today())
                          ->where('is_cancelled', false);

                    if ($request->filled('workshop')) {
                        $query->where('workshop_id', $request->workshop);
                    }

                    if ($request->filled('day')) {
                        $driver = $query->getConnection()->getDriverName();
                        $day = $request->day;

                        if ($driver === 'sqlite') {
                            $sqliteDay = $day == 7 ? 0 : $day;
                            $query->whereRaw("strftime('%w', date) = ?", [(string) $sqliteDay]);
                        } else {
                            $mysqlDay = ($day % 7) + 1;
                            $query->whereRaw('DAYOFWEEK(date) = ?', [$mysqlDay]);
                        }
                    }

                    $query->with('schedule', 'workshop.teacher', 'workshop.discipline.area', 'workshop.prices', 'workshop.studio')
                          ->orderBy('date', 'asc')
                          ->orderBy('start_time', 'asc');
                }
            ])
            ->firstOrFail();

        // ─── Enriquecer sesiones con cupos disponibles ──────────────
        $studio->setRelation('classSessions',
            $exploreService->enrichSessionCollection($studio->classSessions)
        );

        // ─── Estado del usuario: dbSelectionsBySession + familiares ──
        $dbSelectionsBySession = [];
        $activeDependents = collect();

        if (auth()->check()) {
            $user = auth()->user();
            $userId = $user->id;
            $activeDependents = $user->activeDependents;
            $dependentNationalIds = $activeDependents->pluck('national_id')->toArray();

            $userSessions = ClassSession::withoutGlobalScopes()
                ->whereHas('students', function ($q) use ($userId, $dependentNationalIds) {
                    $q->withoutGlobalScopes()
                      ->where(function ($sub) use ($userId, $dependentNationalIds) {
                          $sub->where('students.user_id', $userId);
                          if (!empty($dependentNationalIds)) {
                              $sub->orWhereIn('students.national_id', $dependentNationalIds);
                          }
                      });
                })
                ->where('date', '>=', Carbon::today()->toDateString())
                ->with(['students' => function ($q) use ($userId, $dependentNationalIds) {
                    $q->withoutGlobalScopes()
                      ->where(function ($sub) use ($userId, $dependentNationalIds) {
                          $sub->where('students.user_id', $userId);
                          if (!empty($dependentNationalIds)) {
                              $sub->orWhereIn('students.national_id', $dependentNationalIds);
                          }
                      });
                }])
                ->get();

            foreach ($userSessions as $session) {
                $selections = [];
                foreach ($session->students as $st) {
                    $status = $st->pivot->payment_status;
                    if (!empty($st->national_id)) {
                        if ($st->national_id === $user->national_id) {
                            $selections['titular'] = $status;
                        } else {
                            $dep = $activeDependents->where('national_id', $st->national_id)->first();
                            if ($dep) {
                                $selections[$dep->id] = $status;
                            }
                        }
                    } else {
                        if ($st->first_name === $user->name) {
                            $selections['titular'] = $status;
                        }
                    }
                }
                $dbSelectionsBySession[$session->id] = $selections;
            }
        }

        $workshops = $studio->workshops;

        return view('public.studio.show', compact(
            'studio', 'workshops', 'dbSelectionsBySession', 'activeDependents'
        ));
    }
}