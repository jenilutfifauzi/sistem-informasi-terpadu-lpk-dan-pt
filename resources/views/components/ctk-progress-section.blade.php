<!-- CTK Progress Section Component -->
<div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
    <div class="mb-4">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
            📋 Progress Tahapan CTK
        </h3>
        <p class="text-sm text-gray-600 mt-1">
            Progress keseluruhan: {{ $record->completion_progress }} ({{ $record->completion_percentage }}%) - Centang otomatis saat data/dokumen diisi
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        @php
            $stages = [
                1 => ['name' => 'MCU', 'details' => 'Status: FIT'],
                2 => ['name' => 'Pembayaran', 'details' => $record->payment_progress . ' payments complete'],
                3 => ['name' => 'Soal / Berkas', 'details' => 'Min 1 dokumen'],
                4 => ['name' => 'Paspor', 'details' => 'No + Dokumen: ' . ($record->paspor_number ?? '...')],
                5 => ['name' => 'Belajar di LPK', 'details' => 'Selesai'],
                6 => ['name' => 'Screening 1', 'details' => 'Lolos'],
                7 => ['name' => 'Interview User', 'details' => 'Lolos'],
                8 => ['name' => 'Ijin Desa', 'details' => 'Ada'],
                9 => ['name' => 'Rekom', 'details' => 'Ada'],
                10 => ['name' => 'WP', 'details' => 'Lengkap'],
                11 => ['name' => 'Apply Visa', 'details' => 'Diajukan'],
                12 => ['name' => 'Medical Full', 'details' => 'Selesai'],
                13 => ['name' => 'Visa', 'details' => 'Terbit'],
                14 => ['name' => 'OPP', 'details' => 'Diterima'],
                15 => ['name' => 'Terbang', 'details' => 'Berangkat'],
            ];
        @endphp

        @foreach ($stages as $stageNum => $stageInfo)
            @php
                $isComplete = $record->{"stage{$stageNum}_complete"};
                $checkbox = $isComplete ? '✅' : '⬜';
                $textColor = $isComplete ? 'text-success-600 font-semibold' : 'text-gray-500';
                $bgColor = $isComplete ? 'bg-success-50 border-success-200' : 'bg-gray-50 border-gray-200';
            @endphp
            <div class="flex items-start gap-2 p-3 rounded-lg border {{ $bgColor }}">
                <span class="text-2xl flex-shrink-0">{{ $checkbox }}</span>
                <div class="flex-1 min-w-0">
                    <div class="{{ $textColor }} font-medium text-sm">
                        {{ $stageNum }}. {{ $stageInfo['name'] }}
                    </div>
                    <div class="text-xs text-gray-600 mt-1">
                        {{ $stageInfo['details'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
