@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-[#DFE5EC] bg-white text-[#2B2D42] placeholder-[#8D99AE] focus:border-[#4361EE] focus:ring-2 focus:ring-[#4361EE]/20 rounded-lg text-xs sm:text-sm transition shadow-none']) }}>
