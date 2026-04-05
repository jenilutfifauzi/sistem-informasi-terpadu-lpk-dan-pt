<div style="display: flex; flex-direction: column; gap: 10px;">
    @foreach($mcuRecords as $mcu)
        <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
            <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: center; background: rgba(17, 24, 39, 0.45); font-size: 11px; font-weight: 700; line-height: 1.1; text-align: center;">MCU</div>
            <div style="flex: 1 1 auto; min-width: 0;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $mcu->examination_date?->format('d M Y') ?? '-' }}</div>
                        @php
                            $statusValue = $mcu->status instanceof \App\Enums\MCUStatus ? $mcu->status->value : $mcu->status;
                            $statusColor = match($statusValue) {
                                'FIT'     => 'background: rgba(34, 197, 94, 0.16); color: #86efac;',
                                'UNFIT'   => 'background: rgba(239, 68, 68, 0.16); color: #fca5a5;',
                                default   => 'background: rgba(245, 158, 11, 0.16); color: #fcd34d;',
                            };
                        @endphp
                        <div style="font-size: 12px; opacity: 0.78; margin-top: 2px;">Klinik: {{ $mcu->clinic_name ?? '-' }}</div>
                    </div>
                    <span style="flex: 0 0 auto; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; line-height: 1; {{ $statusColor }}">
                            {{ $statusValue }}
                    </span>
                </div>

                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                    <span>Pemeriksa: {{ $mcu->examiner_name ?? '-' }}</span>
                    @if($mcu->notes)
                        <span>Catatan: {{ $mcu->notes }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
