<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-[#4361EE] hover:bg-[#3751D4] focus:bg-[#3751D4] active:bg-[#2e45bf] border border-transparent rounded-lg font-medium text-xs text-white focus:outline-none focus:ring-2 focus:ring-[#4361EE]/40 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
