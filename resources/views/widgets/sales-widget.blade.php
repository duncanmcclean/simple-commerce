<div class="card px-3">
    <sales-widget
        :seven-days-count="{{ $sevenDays->count() }}"
        :fourteen-days-count="{{ $fourteenDays->count() }}"
        :thirty-days-count="{{ $thirtyDays->count() }}"
    ></sales-widget>
</div>
