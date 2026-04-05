<div style="display: block;">
    <div style="padding-bottom: 10px; font-size: 12px; opacity: 0.75;">Total {{ $trainings->count() }} pelatihan • {{ $trainings->sum('training_hours') }} jam</div>
    <div style="display: flex; flex-direction: column; gap: 10px;">
    @foreach($trainings as $training)
        <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
            <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: center; background: rgba(17, 24, 39, 0.45); font-size: 11px; font-weight: 700; line-height: 1.1; text-align: center;">LPK</div>
            <div style="flex: 1 1 auto; min-width: 0;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Instruktur: {{ $training->instructor?->nama_lengkap ?? 'N/A' }}</div>
                        <div style="font-size: 12px; opacity: 0.78; margin-top: 2px;">{{ $training->training_start_date?->format('d M Y') }} — {{ $training->training_end_date?->format('d M Y') ?? 'Belum selesai' }}</div>
                    </div>
                    <span style="flex: 0 0 auto; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; line-height: 1; {{ $training->completion_status === 'Selesai' ? 'background: rgba(34, 197, 94, 0.16); color: #86efac;' : 'background: rgba(245, 158, 11, 0.16); color: #fcd34d;' }}">
                        {{ $training->completion_status }}
                    </span>
                </div>

                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                    <span>Lokasi: {{ $training->training_location ?? '-' }}</span>
                    <span>Jam: {{ $training->training_hours ?? 0 }}</span>
                    @if($training->completion_notes)
                        <span>Catatan: {{ $training->completion_notes }}</span>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    </div>
</div>
