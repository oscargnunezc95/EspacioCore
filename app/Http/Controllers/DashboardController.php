<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Workshop;

class DashboardController extends Controller
{
    public function index()
    {
        // El Global Scope (StudioScope) hace el trabajo pesado aquí.
        // Solo contará los registros que pertenezcan al estudio actual.
        $studentsCount = Student::count();
        $workshopsCount = Workshop::count();

        return view('dashboard', compact('studentsCount', 'workshopsCount'));
    }
}