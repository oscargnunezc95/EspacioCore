<x-app-layout>
    <x-studio-tabs />

    <div class="pt-6 pb-12 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Cabecera --}}
        <div class="mt-2 mb-8 p-1">
            <div class="flex text-xs font-bold text-white mb-3 gap-2 items-center">
                <span class="text-amber-600">Dashboard</span>
            </div>
            <div class="mt-2">
                <h1 class="text-3xl font-black tracking-tight">Panel de estudio</h1>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- SECCIÓN 1: RESUMEN DEL MES                              --}}
        {{-- ======================================================= --}}
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-6 bg-stone-800 rounded-full"></div>
                <h2 class="text-xl font-black text-amber-600 tracking-tight">Resumen del Mes</h2>
            </div>

            {{-- KPI Operativos --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Alumnas Activas</p>
                    <p class="text-3xl font-black text-stone-900">{{ $activeStudentsCount }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Ocupación Promedio</p>
                    <p class="text-3xl font-black text-stone-900">{{ $occupancyRate }}%</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Nuevas (Mes)</p>
                    <p class="text-3xl font-black text-stone-900">{{ $newStudentsCount }}</p>
                </div>
            </div>

            {{-- KPI Financieros --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Ingresos (Ventas)</p>
                    <p class="text-3xl font-black text-emerald-600">${{ number_format($monthlyRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Nómina (Profesores)</p>
                    <p class="text-3xl font-black text-amber-600">${{ number_format($monthlyPayroll, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-sm transition-all duration-200 hover:shadow-md">
                    <p class="text-[10px] font-black text-stone-500 uppercase tracking-widest mb-1.5">Margen Operativo</p>
                    <p class="text-3xl font-black {{ $netMargin >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $netMargin >= 0 ? '+' : '' }}${{ number_format($netMargin, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            {{-- Composición de Ingresos (Convertido a formato horizontal) --}}
            <div class="bg-white rounded-2xl p-6 border border-stone-200 shadow-sm flex flex-col md:flex-row items-center gap-8">
                <div class="shrink-0 text-center md:text-left md:w-1/3">
                    <h4 class="text-sm font-bold text-stone-900 mb-2">Composición de Ingresos</h4>
                    <span class="text-[10px] font-black text-stone-500 bg-stone-100 px-3 py-1 rounded-full uppercase tracking-widest">
                        Distribución del mes
                    </span>
                </div>
                <div class="flex-1 w-full space-y-3">
                    @foreach(['online' => 'Web/App', 'transferencia' => 'Transferencia', 'efectivo' => 'Efectivo'] as $key => $label)
                        <div class="group">
                            <div class="flex justify-between text-[11px] font-bold text-stone-500 uppercase mb-1.5 transition-colors group-hover:text-stone-700">
                                <span>{{ $label }}</span> <span>{{ $revenuePercentages[$key] }}%</span>
                            </div>
                            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                                <div class="h-2 rounded-full transition-all duration-500 ease-out {{ $key === 'online' ? 'bg-stone-900' : ($key === 'transferencia' ? 'bg-stone-500' : 'bg-stone-300') }}" style="width: {{ $revenuePercentages[$key] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- SECCIÓN 2: EVOLUCIÓN HISTÓRICA                          --}}
        {{-- ======================================================= --}}
        <div class="mb-14">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-6 bg-stone-300 rounded-full"></div>
                <h2 class="text-xl font-black text-amber-600 tracking-tight">Evolución Histórica</h2>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Evolución de Ingresos --}}
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm">
                    <h4 class="text-sm font-bold text-stone-900 mb-4 uppercase tracking-wider">Tendencia de Ingresos</h4>
                    <div class="relative h-56 w-full">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
                {{-- Evolución de Alumnas --}}
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm">
                    <h4 class="text-sm font-bold text-stone-900 mb-4 uppercase tracking-wider">Crecimiento de Alumnas</h4>
                    <div class="relative h-56 w-full">
                        <canvas id="studentsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- SECCIÓN 3: LO DEL DÍA (Acciones Rápidas)                --}}
        {{-- ======================================================= --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-1.5 h-6 bg-red-600 rounded-full"></div>
                <h2 class="text-xl font-black text-amber-600 tracking-tight">Lo del Día</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- CLASES DE HOY --}}
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm flex flex-col min-h-[350px] max-h-[450px]">
                    <h4 class="text-sm font-bold text-stone-900 mb-6 uppercase tracking-wider">En curso hoy</h4>
                    <div class="space-y-2 overflow-y-auto custom-scrollbar pr-2 flex-1">
                        @forelse($todayClasses as $class)
                            <div class="flex items-center gap-3 p-3 bg-white hover:bg-stone-50 rounded-xl border border-stone-100 transition-all duration-200 cursor-default group">
                                <div class="text-center shrink-0 w-12 py-1 bg-stone-50 group-hover:bg-white rounded-lg border border-stone-100 transition-colors">
                                    <div class="text-xs font-black text-stone-900">{{ \Carbon\Carbon::parse($class->start_time)->format('H:i') }}</div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-stone-900 truncate">{{ $class->workshop->name }}</p>
                                    {{-- CORRECCIÓN 1: Concatenar first_name y last_name --}}
                                    <p class="text-[11px] font-medium text-stone-500">
                                        {{ $class->workshop->teacher ? $class->workshop->teacher->first_name . ' ' . $class->workshop->teacher->last_name : 'Sin asignar' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full text-center py-8">
                                <div class="w-12 h-12 bg-stone-50 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-stone-900">Sin clases programadas</p>
                                <p class="text-xs text-stone-500 mt-1">Tu agenda está libre por hoy.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- MOROSAS --}}
                <div class="bg-white rounded-3xl p-6 border border-stone-200 shadow-sm flex flex-col min-h-[350px] max-h-[450px]">
                    <h4 class="text-sm font-bold text-stone-900 mb-6 uppercase tracking-wider flex items-center justify-between">
                        Clases Impagas 
                        @if($studentsWithDebt->count() > 0)
                            <span class="text-xs font-bold bg-rose-100 text-rose-700 px-2.5 py-1 rounded-full">{{ $studentsWithDebt->count() }}</span>
                        @endif
                    </h4>
                    <div class="space-y-2 overflow-y-auto custom-scrollbar pr-2 flex-1">
                        @forelse($studentsWithDebt as $student)
                            <div class="p-3 bg-white hover:bg-rose-50/30 rounded-xl border border-stone-100 hover:border-rose-100 transition-all duration-200 cursor-default">
                                <p class="text-sm font-bold text-stone-900">{{ $student->first_name }} {{ $student->last_name }}</p>
                                <div class="mt-2 space-y-1.5">
                                    @foreach($student->classSessions as $session)
                                        <div class="text-[11px] font-medium flex justify-between items-center bg-stone-50 p-1.5 rounded-lg">
                                            {{-- CORRECCIÓN 2: Mostrar Fecha y Hora específica de la deuda --}}
                                            <div class="flex items-center gap-2 truncate mr-2">
                                                <span class="font-black text-stone-900">{{ \Carbon\Carbon::parse($session->date)->format('d/m') }}</span>
                                                <span class="text-stone-600 truncate">{{ $session->workshop->name }}</span>
                                            </div>
                                            <span class="font-bold text-rose-600 bg-rose-100/50 px-2 py-0.5 rounded-md shrink-0">Pendiente</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center h-full text-center py-8">
                                <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                                <p class="text-sm font-bold text-stone-900">Todo al día</p>
                                <p class="text-xs text-stone-500 mt-1">No hay alumnas con pagos pendientes.</p>
                            </div>
                        @endforelse
                    </div>
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

            Chart.defaults.font.family = "'Inter', sans-serif";
            Chart.defaults.color = "#a1a1aa";
            
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#18181b',
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
                        grid: { borderDash: [4, 4], color: '#f4f4f5', drawBorder: false },
                        ticks: { precision: 0 }
                    }
                }
            };

            // Gráfico 1: Ingresos
            new Chart(document.getElementById('revenueChart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: historical.labels,
                    datasets: [
                        {
                            label: 'Web/App',
                            data: historical.revenue.online,
                            backgroundColor: '#18181b',
                            barThickness: 32
                        },
                        {
                            label: 'Transferencia',
                            data: historical.revenue.transferencia,
                            backgroundColor: '#71717a',
                            barThickness: 32
                        },
                        {
                            label: 'Efectivo',
                            data: historical.revenue.efectivo,
                            backgroundColor: '#e4e4e7',
                            borderRadius: { topLeft: 4, topRight: 4 },
                            barThickness: 32
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        ...commonOptions.plugins,
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 20,
                                font: { size: 11, weight: 'bold' },
                                color: '#52525b'
                            }
                        }
                    },
                    scales: {
                        x: { ...commonOptions.scales.x, stacked: true },
                        y: {
                            ...commonOptions.scales.y,
                            stacked: true,
                            ticks: {
                                callback: function(value) { return '$' + value.toLocaleString('es-CL'); }
                            }
                        }
                    }
                }
            });

            // Gráfico 2: Alumnas
            new Chart(document.getElementById('studentsChart').getContext('2d'), {
                type: 'line',
                data: {
                    labels: historical.labels,
                    datasets: [{
                        label: 'Nuevas Alumnas',
                        data: historical.newStudents,
                        borderColor: '#e11d48', // Usando el acento rojo suave
                        backgroundColor: 'rgba(225, 29, 72, 0.05)',
                        borderWidth: 3,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#e11d48',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: commonOptions
            });
        });
    </script>
</x-app-layout>