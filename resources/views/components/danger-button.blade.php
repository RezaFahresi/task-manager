<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#EF233C] hover:bg-[#d91a32] active:bg-[#bf1228] border border-transparent rounded-lg font-medium text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#EF233C]/40 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
