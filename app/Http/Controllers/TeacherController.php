<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;

class TeacherController extends Controller
{
    public function index($subdomain)
    {
        $teachers = Teacher::orderBy('name', 'asc')->get();
        return view('teachers.index', compact('teachers'));
    }

    public function store(Request $request, $subdomain)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        Teacher::create($request->all());

        return back()->with('success', 'Profesor registrado. Si el correo coincide con una cuenta, se vinculará automáticamente.');
    }

    public function update(Request $request, $subdomain, Teacher $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
        ]);

        $teacher->update($request->all());

        return back()->with('success', 'Datos del profesor actualizados.');
    }

    public function destroy($subdomain, Teacher $teacher)
    {
        // Esto solo desvincula al profesor (SoftDelete), no borra sus talleres.
        $teacher->delete();
        return back()->with('success', 'Profesor eliminado del estudio.');
    }
}