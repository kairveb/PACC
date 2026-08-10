<form method="GET" class="bg-white rounded-xl border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
    <div><label class="block text-xs text-slate-500 mb-1">Start</label><input type="date" name="start" value="{{ $start ?? '' }}" class="px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
    <div><label class="block text-xs text-slate-500 mb-1">End</label><input type="date" name="end" value="{{ $end ?? '' }}" class="px-3 py-2 text-sm border border-slate-300 rounded-lg"></div>
    <button type="submit" class="px-4 py-2 text-sm bg-slate-800 text-white rounded-lg">Apply</button>
</form>
