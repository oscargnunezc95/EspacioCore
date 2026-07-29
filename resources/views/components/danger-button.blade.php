<button {{ $attributes->merge(['type' => 'submit', 'class' => 'border border-red-500 text-red-600 hover:bg-red-50 font-bold py-2.5 px-4 rounded-xl transition-all duration-200 active:scale-95 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
