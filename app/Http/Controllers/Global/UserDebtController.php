<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;

class UserDebtController extends Controller
{
    public function index()
    {
        // Traemos todos los perfiles de alumno de este usuario
        // Y cargamos el estudio y los talleres (con la información de la tabla pivote)
        $studentProfiles = Student::with(['studio', 'workshops' => function($query) {
            // Aquí puedes filtrar si quieres traer solo los talleres donde hay deuda
            // Por ejemplo, si usas credits_available < 0
            // $query->wherePivot('credits_available', '<', 0);
        }])
        ->where('user_id', Auth::id())
        ->get();

        return view('global.debts.index', compact('studentProfiles'));
    }
}