@props(['breadcrumbs' => [], 'title'])

<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <!-- Lado Izquierdo: Migajas y Título -->
    <div>
        @if(count($breadcrumbs) > 0)
            <!-- Aumentamos de mb-2 a mb-6 para separar las migajas del título -->
            <nav class="flex text-sm text-stone-500 font-medium mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-2">
                    @foreach($breadcrumbs as $crumb)
                        <li>
                            @if(isset($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="hover:text-stone-900 transition-colors duration-200">
                                    {{ $crumb['name'] }}
                                </a>
                            @else
                                <span class="text-stone-900 font-bold">{{ $crumb['name'] }}</span>
                            @endif
                        </li>
                        @if(!$loop->last)
                            <li><span class="mx-1 text-stone-300">/</span></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        <h2 class="font-bold text-2xl text-stone-900 leading-tight tracking-tight">
            {{ $title }}
        </h2>
    </div>

    <!-- Lado Derecho: Acciones (Botones) -->
    @if(isset($actions))
        <div class="flex items-center gap-3">
            {{ $actions }}
        </div>
    @endif
</div>