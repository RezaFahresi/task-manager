@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs text-[#2B2D42] mb-1.5']) }}>
    {{ $value ?? $slot }}
</label>
