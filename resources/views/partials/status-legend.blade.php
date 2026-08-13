<div class="panel-card p-4">
    <div class="flex flex-wrap items-center gap-3">
        <span class="text-sm font-semibold text-slate-700">Status legend:</span>
        @include('partials.status-badge', ['label' => 'Waiting', 'variant' => 'info'])
        @include('partials.status-badge', ['label' => 'Seen', 'variant' => 'success'])
        @include('partials.status-badge', ['label' => 'In consult', 'variant' => 'warning'])
        @include('partials.status-badge', ['label' => 'Completed', 'variant' => 'info'])
    </div>
</div>
