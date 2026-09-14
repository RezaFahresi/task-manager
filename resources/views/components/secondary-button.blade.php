<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center justify-center px-4 py-2 bg-white border border-[#DFE5EC] rounded-lg font-medium text-xs text-[#2B2D42] hover:bg-[#EDF2F4] hover:text-[#2B2D42] focus:outline-none focus:ring-2 focus:ring-[#4361EE]/40 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
