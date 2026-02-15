<div class="space-y-2">
    @foreach($screenings as $screening)
        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <p class="font-semibold text-sm">
                        Interviewer: {{ $screening->interviewer->name }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Tanggal: {{ $screening->interview_date->format('d M Y') }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Lokasi: {{ $screening->interview_location }}
                    </p>
                    @if($screening->interview_notes)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Catatan: {{ $screening->interview_notes }}
                        </p>
                    @endif
                </div>
                <div class="ml-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $screening->screening_result === 'Lolos'
                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }}">
                        {{ $screening->screening_result }}
                    </span>
                </div>
            </div>
        </div>
    @endforeach
</div>
