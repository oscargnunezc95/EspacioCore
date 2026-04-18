@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4">
    <div class="flex space-x-4 mb-8 border-b border-gray-200">
        <a href="{{ route('workshops.index') }}" class="py-2 px-6 font-medium text-gray-500 hover:text-blue-600 transition">Talleres (Configuración)</a>
        <button class="py-2 px-6 font-bold text-blue-600 border-b-2 border-blue-600">Entrenamientos (Meses)</button>
    </div>

    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800 italic">Ciclos Mensuales</h1>
        <button onclick="document.getElementById('monthModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-8 rounded-2xl shadow-lg transition transform hover:scale-105">
            + PLANIFICAR MES
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($months as $month)
            @php $d = \Carbon\Carbon::parse($month->first_date); @endphp
            <a href="{{ route('entrenamientos.show', $month->month_id) }}" class="bg-white rounded-3xl p-8 border border-gray-200 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all group">
                <div class="text-xs font-black text-gray-400 uppercase mb-2 tracking-widest">{{ $d->format('Y') }}</div>
                <h2 class="text-3xl font-black text-gray-900 capitalize group-hover:text-blue-600 transition">{{ $d->translatedFormat('F') }}</h2>
                <div class="mt-4 flex items-center text-blue-500 font-bold text-xs uppercase">Ver calendario &rarr;</div>
            </a>
        @empty
            <div class="col-span-full py-20 text-center text-gray-400 italic text-2xl">Todavía no has generado ningún mes de entrenamiento.</div>
        @endforelse
    </div>
</div>

{{-- MODAL GENERAR MES --}}
<div id="monthModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-gray-200">
        <h3 class="text-2xl font-bold mb-6 text-gray-900">Generar Nuevo Mes</h3>
        
        <form action="{{ route('entrenamientos.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Elegir Mes y Año</label>
                <input type="month" name="month_year" value="{{ old('month_year') }}" 
                       class="w-full rounded-xl border-2 p-3 focus:ring-0 transition {{ $errors->has('month_year') ? 'border-red-500 bg-red-50' : 'border-gray-400 focus:border-blue-500' }}" required>
            </div>
            
            <div class="mb-8">
                <label class="block text-sm font-bold text-gray-700 mb-2">Talleres Mensuales a incluir</label>
                <div class="space-y-2 max-h-60 overflow-y-auto border-2 rounded-xl p-3 {{ $errors->has('workshops') ? 'border-red-500 bg-red-50' : 'border-gray-400 bg-gray-50' }}">
                    @forelse($workshops as $w)
                        @php $dias = ['Domingos', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábados']; @endphp
                        <label class="flex items-center gap-3 p-2 bg-white border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-100 transition">
                            <input type="checkbox" name="workshops[]" value="{{ $w->id }}" checked class="w-5 h-5 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-800 text-sm">{{ $w->name }}</span>
                                <span class="text-gray-400 text-[10px] font-bold uppercase tracking-widest mt-0.5">Se repite los {{ $dias[$w->repeat_day] }}</span>
                            </div>
                        </label>
                    @empty
                        <div class="text-sm text-gray-500 italic text-center py-2">No hay talleres mensuales configurados.</div>
                    @endforelse
                </div>
                @error('workshops') <p class="text-xs text-red-600 font-bold mt-1">Debes seleccionar al menos un taller.</p> @enderror
            </div>

            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('monthModal').classList.add('hidden')" class="flex-1 font-bold text-gray-500 bg-gray-100 hover:bg-gray-200 py-3 rounded-xl transition">Cancelar</button>
                <button type="submit" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-blue-700 transition">Crear Calendario</button>
            </div>
        </form>
    </div>
</div>

<script>
    @if($errors->any())
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById('monthModal').classList.remove('hidden');
        });
    @endif
</script>
@endsection