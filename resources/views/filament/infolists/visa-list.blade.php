<div style="display: flex; flex-direction: column; gap: 10px;">
    @foreach($visas as $visa)
        <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
            <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: center; background: rgba(17, 24, 39, 0.45); font-size: 11px; font-weight: 700; line-height: 1.1; text-align: center;">Visa</div>
            <div style="flex: 1 1 auto; min-width: 0;">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                    <div style="min-width: 0; flex: 1 1 auto;">
                        <div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            @if($visa->visa_number)
                                Visa #{{ $visa->visa_number }}
                            @else
                                Pengajuan Visa
                            @endif
                        </div>
                        <div style="font-size: 12px; opacity: 0.78; margin-top: 2px;">Tanggal Pengajuan: {{ $visa->application_date->format('d M Y') }}</div>
                    </div>
                    <span style="flex: 0 0 auto; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; line-height: 1; {{ $visa->application_status === 'Terbit' ? 'background: rgba(34, 197, 94, 0.16); color: #86efac;' : 'background: rgba(245, 158, 11, 0.16); color: #fcd34d;' }}">
                            {{ $visa->application_status }}
                    </span>
                </div>

                <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                    @if($visa->application_status === 'Terbit')
                        <span>Tanggal Terbit: {{ $visa->issuance_date->format('d M Y') }}</span>
                        <span>Kadaluarsa: {{ $visa->expiry_date->format('d M Y') }}</span>
                        @if($visa->issuing_country)
                            <span>Negara: {{ $visa->issuing_country }}</span>
                            <span>Jenis: {{ $visa->visa_type }}</span>
                        @endif
                        @if($visa->visa_document_path)
                            <span>Dokumen: {{ basename($visa->visa_document_path) }}</span>
                        @endif
                        @if($visa->expiry_date->diffInDays(now()) <= 30 && $visa->expiry_date->isFuture())
                            <span style="color: #fcd34d; font-weight: 600;">
                                ⚠️ Visa akan kadaluarsa dalam {{ $visa->expiry_date->diffInDays(now()) }} hari
                            </span>
                        @elseif($visa->expiry_date->isPast())
                            <span style="color: #fca5a5; font-weight: 600;">
                                ⚠️ Visa sudah kadaluarsa
                            </span>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
