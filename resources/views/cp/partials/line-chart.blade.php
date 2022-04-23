<div class="text-4xs w-full relative" style="height: 100px; font-variant-numeric: tabular-nums;">
    @php($maxValue = collect($data)->map(function ($point) { return $point[0]; })->max())
    @php($points = collect($data)->map(function ($point, $index) use ($maxValue) { return "L {$index} ". ($maxValue - $point[0]); })->join(' '))
    @foreach ($data as $index => [$value, $label])
        <div class="bg-gray-200 bottom-0 absolute top-0 w-px" style="left: {{ ($index / ((count($data) - 1) ?: 1)) * 100 }}%">
            <div class="absolute text-center" style="bottom: -20px; left: 50%; transform: translateX(-50%); min-width: 35px;">{{ $label }}</div>
            <div class="bg-blue-500 rounded-sm text-white font-bold absolute text-center z-10 text-4xs" style="left: 50%; padding: 0 3px; bottom: {{ ($value / ($maxValue ?: 1)) * 100 }}%; transform: translateX(-50%) translateY(6px)">
                {{ $value }}
            </div>
        </div>
    @endforeach
    <div class="absolute left-0 right-0 h-px bg-gray-200 text-4xs" style="bottom: 50%"></div>
    <div class="absolute left-0 right-0 h-px bg-gray-200 text-4xs" style="bottom: 0"></div>
    <svg class="h-full relative w-full" viewBox="0 0 {{ count($data) - 1 }} {{ $maxValue }}" preserveAspectRatio="none">
        <defs>
            <linearGradient id="background" x1="0%" y1="0%" x2="0%" y2="100%">
                <stop offset="0%" style="stop-color: #19a1e6; stop-opacity: 0.6;" />
                <stop offset="100%" style="stop-color: #19a1e6; stop-opacity: 0.2;" />
            </linearGradient>
        </defs>
        <path d="M 0 {{ $maxValue + 1 }} {{ $points }} L 23 {{ $maxValue + 1 }} Z" fill="url(#background)" />
        <path
                d="{{ str_replace('L', 'M', $points) }}"
                fill="none"
                stroke="#19a1e6"
                strokeOpacity="0.3"
                strokeWidth="1"
                strokeLinecap="round"
                vectorEffect="non-scaling-stroke"
        />
    </svg>
</div>
