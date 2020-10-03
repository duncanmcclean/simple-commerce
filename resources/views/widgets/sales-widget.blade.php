<div class="card px-3">
    <sales-widget
        :seven-days-count="{{ $sevenDays['count'] }}"
        seven-days-total="{{ $sevenDays['total'] }}"
        :fourteen-days-count="{{ $fourteenDays['count'] }}"
        fourteen-days-total="{{ $fourteenDays['total'] }}"
        :thirty-days-count="{{ $thirtyDays['count'] }}"
        thirty-days-total="{{ $thirtyDays['total'] }}"
    ></sales-widget>
</div>
