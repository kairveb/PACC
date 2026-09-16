{{-- Shared 3-step progress indicator for the ER intake -> triage/queue workflow. --}}
@php
    $workflowSteps = [
        1 => 'Intake',
        2 => 'Visit details',
        3 => 'Confirm priority & queue',
    ];
    $currentStep = $currentStep ?? 1;
@endphp
<div class="panel-card px-5 py-4">
    <ol class="flex flex-wrap items-center gap-x-6 gap-y-2">
        @foreach ($workflowSteps as $stepNumber => $stepLabel)
            <li class="flex items-center gap-2 text-sm">
                <span @class([
                    'flex h-6 w-6 items-center justify-center rounded-full text-xs font-semibold',
                    'bg-emerald-600 text-white' => $stepNumber < $currentStep,
                    'bg-rose-600 text-white' => $stepNumber === $currentStep,
                    'bg-slate-200 text-slate-500' => $stepNumber > $currentStep,
                ])>
                    @if ($stepNumber < $currentStep)
                        &#10003;
                    @else
                        {{ $stepNumber }}
                    @endif
                </span>
                <span @class([
                    'font-medium',
                    'text-slate-900' => $stepNumber <= $currentStep,
                    'text-slate-400' => $stepNumber > $currentStep,
                ])>{{ $stepLabel }}</span>
            </li>
            @if (!$loop->last)
                <li class="hidden h-px w-8 bg-slate-200 sm:block" aria-hidden="true"></li>
            @endif
        @endforeach
    </ol>
</div>
