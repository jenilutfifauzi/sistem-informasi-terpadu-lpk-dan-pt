<div class="space-y-2">
    @foreach($medicals as $medical)
        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-sm">
                            Pemeriksaan: {{ $medical->examination_date->format('d M Y') }}
                        </p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $medical->status === 'Selesai'
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                            {{ $medical->status }}
                        </span>
                        @if($medical->isExpiringSoon() && $medical->status === 'Selesai')
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                ⚠️ Perlu Perpanjangan (>90 hari)
                            </span>
                        @endif
                    </div>
                    @if($medical->examination_findings)
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Hasil: {{ Str::limit($medical->examination_findings, 200) }}
                        </p>
                    @endif
                    @if($medical->medical_report_path && $medical->status === 'Selesai')
                        <p class="text-sm text-blue-600 dark:text-blue-400 mt-1">
                            📄 Dokumen terlampir
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
