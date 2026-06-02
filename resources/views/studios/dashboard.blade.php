<x-app-layout>
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Cabecera Unificada --}}
        <div class="mt-2 mb-8 p-1">

            {{-- Breadcrumbs --}}
            <div class="flex text-xs font-bold text-zinc-500 mb-3 gap-2 items-center">
                <span class="text-zinc-900">Dashboard</span>
            </div>
            <div class="mt-2 mb-8">
                <h1 class="text-3xl font-black text-zinc-900 tracking-tight">Dashboard Operativo</h1>
            </div>
        </div>

        {{-- 1. PASTILLAS DE KPI OPERATIVOS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Alumnas Activas</p>
                <p class="text-2xl font-black text-zinc-900">{{ $activeStudentsCount }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Ocupación Prom.</p>
                <p class="text-2xl font-black text-zinc-900">{{ $occupancyRate }}%</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Nuevas (Mes)</p>
                <p class="text-2xl font-black text-zinc-900">{{ $newStudentsCount }}</p>
            </div>
        </div>

        {{-- 2. PASTILLAS DE KPI FINANCIEROS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Ingresos (Ventas)</p>
                <p class="text-2xl font-black text-emerald-600">${{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Nómina (Profesores)</p>
                <p class="text-2xl font-black text-amber-600">${{ number_format($monthlyPayroll, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-zinc-200 shadow-sm transition-all duration-200 hover:shadow-md hover:border-zinc-300">
                <p class="text-[10px] font-black text-zinc-500 uppercase tracking-widest mb-1">Margen Operativo</p>
                <p class="text-2xl font-black {{ $netMargin >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $netMargin >= 0 ? '+' : '' }}${{ number_format($netMargin, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- NUEVO: 2. GRÁFICOS HISTÓRICOS (3 MESES) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Evolución de Ingresos --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm">
                <h4 class="text-sm font-bold text-zinc-900 mb-4 uppercase tracking-wider">Tendencia de Ingresos</h4>
                <div class="relative h-48 w-full">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            {{-- Evolución de Alumnas --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm">
                <h4 class="text-sm font-bold text-zinc-900 mb-4 uppercase tracking-wider">Crecimiento de Alumnas</h4>
                <div class="relative h-48 w-full">
                    <canvas id="studentsChart"></canvas>
                </div>
            </div>
        </div>

        {{-- 3. GRILLA PRINCIPAL DE 3 COLUMNAS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            {{-- FINANZAS --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col md:col-span-2 lg:col-span-1">
                <h4 class="text-lg font-bold text-zinc-900 mb-6 flex items-center gap-2">Ingresos Brutos</h4>
                <div class="flex flex-col items-center justify-center py-4">
                    <div class="flex items-start gap-1">
                        <span class="text-xl font-bold text-zinc-400 mt-1">$</span>
                        <span class="text-4xl font-black text-zinc-900">{{ number_format($monthlyRevenue, 0, ',', '.') }}</span>
                    </div>
                    <span class="text-[10px] font-black text-zinc-500 bg-zinc-100 px-3 py-1 rounded-full mt-3 uppercase tracking-widest">
                        Mes actual
                    </span>
                </div>
                <div class="mt-6 pt-6 border-t border-zinc-100 space-y-4">
                    @foreach(['online' => 'Web/App', 'transferencia' => 'Transferencia', 'efectivo' => 'Efectivo'] as $key => $label)
                        <div class="group">
                            <div class="flex justify-between text-[10px] font-bold text-zinc-500 uppercase mb-1 transition-colors group-hover:text-zinc-700">
                                <span>{{ $label }}</span> <span>{{ $revenuePercentages[$key] }}%</span>
                            </div>
                            <div class="w-full bg-zinc-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-zinc-900 h-1.5 rounded-full transition-all duration-500 ease-out" style="width: {{ $revenuePercentages[$key] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- CLASES DE HOY --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col min-h-[350px] max-h-[500px]">
                <h4 class="text-lg font-bold text-zinc-900 mb-6">En curso hoy</h4>
                <div class="space-y-2 overflow-y-auto custom-scrollbar pr-2 flex-1">
                    @forelse($todayClasses as $class)
                        <div class="flex items-center gap-3 p-3 bg-white hover:bg-zinc-50 rounded-xl border border-zinc-100 transition-all duration-200 cursor-default group">
                            <div class="text-center shrink-0 w-12 py-1 bg-zinc-50 group-hover:bg-white rounded-lg border border-zinc-100 transition-colors">
                                <div class="text-xs font-black text-zinc-900">{{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }}</div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-zinc-900 truncate">{{ $class->workshop->name }}</p>
                                <p class="text-[11px] font-medium text-zinc-500">{{ $class->workshop->teacher->name ?? 'Sin asignar' }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center py-8">
                            <div class="w-12 h-12 bg-zinc-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Sin clases programadas</p>
                            <p class="text-xs text-zinc-500 mt-1">Tu agenda está libre por hoy.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- MOROSAS --}}
            <div class="bg-white rounded-3xl p-6 border border-zinc-200 shadow-sm flex flex-col min-h-[350px] max-h-[500px]">
                <h4 class="text-lg font-bold text-zinc-900 mb-6 flex items-center justify-between">
                    Clases Impagas 
                    @if($studentsWithDebt->count() > 0)
                        <span class="text-xs font-bold bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full">{{ $studentsWithDebt->count() }}</span>
                    @endif
                </h4>
                <div class="space-y-2 overflow-y-auto custom-scrollbar pr-2 flex-1">
                    @forelse($studentsWithDebt as $student)
                        <div class="p-3 bg-white hover:bg-rose-50/30 rounded-xl border border-zinc-100 hover:border-rose-100 transition-all duration-200 cursor-default">
                            <p class="text-sm font-bold text-zinc-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                            <div class="mt-2 space-y-1">
                                @foreach($student->classSessions as $session)
                                    <div class="text-[11px] font-medium flex justify-between items-center">
                                        <span class="text-zinc-600 truncate mr-2">{{ $session->workshop->name }}</span>
                                        <span class="font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md shrink-0">Pendiente</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-center py-8">
                            <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <p class="text-sm font-bold text-zinc-900">Todo al día</p>
                            <p class="text-xs text-zinc-500 mt-1">No hay alumnas con pagos pendientes.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e4e4e7; border-radius: 10px; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const historical = @json($historicalData);

            // Configuración general elegante
            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = "#a1a1aa"; // zinc-400
            
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, // Apagado por defecto, lo prendemos específico para barras
                    tooltip: {
                        backgroundColor: '#18181b', // zinc-900
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13 },
                        bodyFont: { size: 12 },
                        displayColors: true,
                        usePointStyle: true,
                        boxPadding: 6
                    }
                },
                scales: {
                    x: { grid: { display: false, drawBorder: false } },
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], color: '#f4f4f5', drawBorder: false }, // zinc-100
                        ticks: { precision: 0 }
                    }
                }
            };

            // Gráfico 1: Ingresos (Barras Apiladas / Stacked Bar)
            new Chart(document.getElementById('revenueChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: historical.labels,
                    datasets: [
                        {
                            label: 'Web/App',
                            data: historical.revenue.online,
                            backgroundColor: '#18181b', // zinc-900 (El más fuerte para dinero digital)
                            barThickness: 32
                        },
                        {
                            label: 'Transferencia',
                            data: historical.revenue.transferencia,
                            backgroundColor: '#71717a', // zinc-500 (Tono medio)
                            barThickness: 32
                        },
                        {
                            label: 'Efectivo',
                            data: historical.revenue.efectivo,
                            backgroundColor: '#e4e4e7', // zinc-200 (El más claro para efectivo)
                            borderRadius: { topLeft: 4, topRight: 4 }, // Solo redondeamos la punta de la pirámide
                            barThickness: 32
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    interaction: {
                        mode: 'index', // Hace que el tooltip muestre los 3 valores al pasar el mouse por la columna
                        intersect: false,
                    },
                    plugins: {
                        ...commonOptions.plugins,
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true, // Bolitas en lugar de cuadrados en la leyenda
                                boxWidth: 8,
                                padding: 20,
                                font: { size: 11, weight: 'bold' },
                                color: '#52525b' // zinc-600
                            }
                        }
                    },
                    scales: {
                        x: { ...commonOptions.scales.x, stacked: true },
                        y: {
                            ...commonOptions.scales.y,
                            stacked: true, // Esto convierte las 3 barras en 1 sola apilada
                            ticks: {
                                callback: function(value) { return '$' + value.toLocaleString('es-CL'); }
                            }
                        }
                    }
                }
            });

            // Gráfico 2: Alumnas Nuevas (Línea suave con relleno - Sin cambios visuales)
            new Chart(document.getElementById('studentsChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: historical.labels,
                    datasets: [{
                        label: 'Nuevas Alumnas',
                        data: historical.newStudents,
                        borderColor: '#18181b',
                        backgroundColor: 'rgba(24, 24, 27, 0.04)', // Relleno súper sutil
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#18181b',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4 // Curva elegante
                    }]
                },
                options: commonOptions
            });
        });
    </script>
</x-app-layout>