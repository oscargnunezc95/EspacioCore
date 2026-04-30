<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;

class HomeController extends Controller
{
    public function index()
    {
        // En el futuro, aquí puedes mandar los estudios destacados al landing page
        // $featuredStudios = Studio::latest()->take(3)->get();
        
        // Por ahora, solo retornamos la vista
        return view('welcome'); 
    }
}