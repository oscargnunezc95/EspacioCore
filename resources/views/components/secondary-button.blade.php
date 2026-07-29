<button {{ $attributes->merge(['type' => 'button', 'class' => 'bg-stone-100 text-stone-700 hover:bg-stone-200 border border-stone-200 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
