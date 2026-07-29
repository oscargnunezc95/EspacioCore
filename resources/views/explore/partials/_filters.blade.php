{{-- ============================================================ --}}
{{-- PARTIAL: Filtros del Explorer con Alpine.js Reactivo          --}}
{{-- Se incluye en sidebar (desktop) y drawer off-canvas (mobile)  --}}
{{-- ============================================================ --}}
@php
    $selectedCountry    = request('country', '');
    $selectedRegion     = request('region', '');
    $selectedCity       = request('city', '');
    $selectedArea       = request('area', '');
    $selectedDiscipline = request('discipline', '');
    $selectedTarget     = request('target_audience', '');
    $dateFrom           = request('date_from', '');
    $dateTo             = request('date_to', '');
@endphp

<div x-data="exploreFilters()" x-init="init()" class="w-full">
    <form action="{{ route('explore') }}" method="GET">

        {{-- País --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                País
            </label>
            <select name="country"
                x-model="selectedCountry"
                @change="onCountryChange()"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300">
                <option value="">Todos</option>
                @foreach($countries ?? [] as $country)
                    <option value="{{ $country->name }}" {{ $selectedCountry == $country->name ? 'selected' : '' }}>
                        {{ $country->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Región (dependiente de país) --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h4l2 5H8l-2 7h12l-2-7h-1l2-5h4"></path></svg>
                Región
            </label>
            <select name="region"
                x-model="selectedRegion"
                @change="onRegionChange()"
                :disabled="!selectedCountry && regions.length === 0"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Todas las regiones</option>
                <template x-for="reg in regions" :key="reg">
                    <option :value="reg" x-text="reg"></option>
                </template>
            </select>
            <span x-show="loading.regions" class="text-[10px] text-red-400 font-medium mt-1 inline-block">
                <svg class="w-3 h-3 inline animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando regiones...
            </span>
        </div>

        {{-- Ciudad (dependiente de región) --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.243-4.243a8 8 0 1111.314 0z"></path></svg>
                Ciudad
            </label>
            <select name="city"
                x-model="selectedCity"
                :disabled="!selectedRegion && cities.length === 0"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Todas las ciudades</option>
                <template x-for="cit in cities" :key="cit">
                    <option :value="cit" x-text="cit"></option>
                </template>
            </select>
            <span x-show="loading.cities" class="text-[10px] text-red-400 font-medium mt-1 inline-block">
                <svg class="w-3 h-3 inline animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando ciudades...
            </span>
        </div>

        {{-- Área --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                Área
            </label>
            <select name="area"
                x-model="selectedArea"
                @change="onAreaChange()"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300">
                <option value="">Todas las áreas</option>
                @foreach($areas as $area)
                    <option value="{{ $area->name }}" {{ $selectedArea == $area->name ? 'selected' : '' }}>{{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Disciplina (dependiente de área) --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Disciplina
            </label>
            <select name="discipline"
                x-model="selectedDiscipline"
                :disabled="!selectedArea"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300 disabled:opacity-50 disabled:cursor-not-allowed">
                <option value="">Todas las disciplinas</option>
                <template x-for="disc in disciplines" :key="disc.id">
                    <option :value="disc.name" x-text="disc.name"></option>
                </template>
            </select>
            <span x-show="loading.disciplines" class="text-[10px] text-red-400 font-medium mt-1 inline-block">
                <svg class="w-3 h-3 inline animate-spin mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                Cargando disciplinas...
            </span>
        </div>

        {{-- Público Objetivo --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Público Objetivo
            </label>
            <select name="target_audience"
                x-model="selectedTarget"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300">
                <option value="">Todos los públicos</option>
                @foreach($targetAudiences as $ta)
                    <option value="{{ $ta->value }}" {{ $selectedTarget == $ta->value ? 'selected' : '' }}>{{ $ta->label }}</option>
                @endforeach
            </select>
        </div>

        {{-- Fecha Desde --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Desde
            </label>
            <input type="date" name="date_from" value="{{ $dateFrom }}"
                min="{{ \Carbon\Carbon::today()->toDateString() }}"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300">
        </div>

        {{-- Fecha Hasta --}}
        <div class="mb-4">
            <label class="flex items-center gap-1.5 text-[11px] font-black text-red-400 uppercase tracking-widest mb-1.5">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                Hasta
            </label>
            <input type="date" name="date_to" value="{{ $dateTo }}"
                min="{{ \Carbon\Carbon::today()->toDateString() }}"
                class="w-full rounded-xl border border-red-100 bg-white px-4 py-3 text-sm focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none cursor-pointer transition-all hover:border-red-300">
        </div>

        {{-- Botones de acción --}}
        <div class="flex gap-2 pt-2 border-t border-red-50">
            <a href="{{ route('explore') }}"
                class="flex-1 flex items-center justify-center bg-stone-100 text-stone-600 font-bold py-3 rounded-xl hover:bg-stone-200 transition-all duration-200 text-sm active:scale-95"
                title="Limpiar Filtros">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            </a>
            <button type="submit"
                class="flex-[3] bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold py-3 rounded-xl shadow-md shadow-red-200 hover:shadow-lg hover:shadow-red-300 hover:from-red-500 hover:to-rose-500 transition-all duration-300 active:scale-95 text-sm flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                Buscar
            </button>
        </div>
    </form>
</div>

{{-- ============================================================ --}}
{{-- ALPINE.JS: Estado reactivo y carga asíncrona de dropdowns    --}}
{{-- ============================================================ --}}
<script>
function exploreFilters() {
    return {
        // Estado actual de los selects
        selectedCountry: '{{ $selectedCountry }}',
        selectedRegion: '{{ $selectedRegion }}',
        selectedCity: '{{ $selectedCity }}',
        selectedArea: '{{ $selectedArea }}',
        selectedDiscipline: '{{ $selectedDiscipline }}',
        selectedTarget: '{{ $selectedTarget }}',

        // Opciones cargadas dinámicamente
        disciplines: @json($disciplines ?? []),
        regions: @json($regions ?? []),
        cities: @json($cities ?? []),

        // Estado de carga
        loading: {
            disciplines: false,
            regions: false,
            cities: false,
        },

        // Inicialización
        init() {
            // Si ya hay valores seleccionados pero las listas están vacías, cargarlas
            // (esto cubre el caso donde el controlador no precargó los datos)
            if (this.selectedCountry && this.regions.length === 0) {
                this.fetchRegions();
            }
            if (this.selectedRegion && this.cities.length === 0) {
                this.fetchCities();
            }
            if (this.selectedArea && this.disciplines.length === 0) {
                this.fetchDisciplines();
            }
        },

        // ─── Handlers de cambio ───────────────────────────────

        onCountryChange() {
            this.selectedRegion = '';
            this.selectedCity = '';
            this.regions = [];
            this.cities = [];
            if (this.selectedCountry) {
                this.fetchRegions();
            }
        },

        onRegionChange() {
            this.selectedCity = '';
            this.cities = [];
            if (this.selectedRegion) {
                this.fetchCities();
            }
        },

        onAreaChange() {
            this.selectedDiscipline = '';
            this.disciplines = [];
            if (this.selectedArea) {
                this.fetchDisciplines();
            }
        },

        // ─── Fetch asíncrono ───────────────────────────────────

        async fetchDisciplines() {
            if (!this.selectedArea) return;
            this.loading.disciplines = true;
            try {
                const res = await fetch(`/api/explore/disciplines?area=${encodeURIComponent(this.selectedArea)}`);
                if (res.ok) {
                    this.disciplines = await res.json();
                }
            } catch (e) {
                console.error('Error cargando disciplinas:', e);
            } finally {
                this.loading.disciplines = false;
            }
        },

        async fetchRegions() {
            if (!this.selectedCountry) return;
            this.loading.regions = true;
            try {
                const res = await fetch(`/api/explore/regions?country=${encodeURIComponent(this.selectedCountry)}`);
                if (res.ok) {
                    this.regions = await res.json();
                }
            } catch (e) {
                console.error('Error cargando regiones:', e);
            } finally {
                this.loading.regions = false;
            }
        },

        async fetchCities() {
            if (!this.selectedRegion) return;
            this.loading.cities = true;
            try {
                const res = await fetch(`/api/explore/cities?region=${encodeURIComponent(this.selectedRegion)}`);
                if (res.ok) {
                    this.cities = await res.json();
                }
            } catch (e) {
                console.error('Error cargando ciudades:', e);
            } finally {
                this.loading.cities = false;
            }
        },
    };
}
</script>