@props(['href', 'icon', 'label', 'active' => false])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors ' . ($active ? 'bg-teal-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white')]) }}>
    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
    <span>{{ $label }}</span>
</a>
