 <div class="fixed inset-0 w-full h-full pointer-events-none z-[-1] overflow-hidden bg-[#0f0f12]">
    
    {{-- Capa 1: Imagen con Desenfoque Extremo y Sobreescalado
         - blur-[140px]: Más del doble que blur-3xl (desintegra cualquier línea o píxel de la foto).
         - scale-125: Estira la imagen un 25% fuera de la pantalla para eliminar el anillo oscuro de los bordes.
         - transform-gpu: Fuerza a la tarjeta de video a renderizar la mezcla de color con máxima suavidad. --}}
    <div aria-hidden="true" 
         class="absolute inset-0 bg-cover bg-center opacity-25 blur-3xl transform-gpu transition-opacity duration-1000" 
         style="background-image: url('{{ asset('images/fondo-aurora.webp') }}');">
    </div>

    {{-- Capa 2: Degradado Vertical Armónico (Sin franjas grises ni saltos bruscos)
         - En lugar de 'via-transparent', usamos tu color base #0f0f12 con opacidad gradual.
         - Esto obliga al navegador a mezclar los tonos en la misma temperatura, eliminando el banding. --}}
<!--     <div aria-hidden="true" 
          class="absolute inset-0 bg-gradient-to-b from-stone-900 via-red-600 to-orange-600 opacity-50 transition-opacity duration-1000">
    </div> -->

</div>

<!-- <div class="fixed inset-0 w-full h-screen pointer-events-none z-[-1] overflow-hidden bg-stone-900">

</div> -->
