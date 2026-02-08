<div class="space-y-3">
    @forelse($activities as $activity)
        <div class="border-l-4 border-blue-500 bg-blue-50 p-3 rounded">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-semibold text-sm text-gray-900">
                        {{ $activity->causer?->name ?? 'System' }} - {{ $activity->event }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">
                        {{ $activity->created_at->format('d F Y H:i:s') }}
                    </p>
                </div>
                @if($activity->getChanges())
                    <span class="inline-block bg-blue-200 text-blue-800 text-xs px-2 py-1 rounded">
                        {{ $activity->description ?? 'Perubahan' }}
                    </span>
                @endif
            </div>

            @if($activity->getChanges())
                <div class="mt-2 text-xs text-gray-700">
                    <details class="cursor-pointer">
                        <summary class="font-medium hover:text-blue-600">Lihat detail perubahan</summary>
                        <div class="mt-2 bg-white p-2 rounded border border-gray-200">
                            @foreach($activity->getChanges() as $field => $change)
                                <div class="mb-2">
                                    <strong>{{ $field }}:</strong>
                                    @if(is_array($change))
                                        <div class="ml-2 text-red-600">Dari: {{ $change['old'] ?? '-' }}</div>
                                        <div class="ml-2 text-green-600">Ke: {{ $change['new'] ?? '-' }}</div>
                                    @else
                                        <span>{{ $change }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </details>
                </div>
            @endif
        </div>
    @empty
        <p class="text-gray-500 text-sm italic">Tidak ada aktivitas tercatat untuk CTK ini</p>
    @endforelse
</div>
