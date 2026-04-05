<div style="display: block;">
    @php
        $totalPaid = $payments->filter(fn ($p) => ($p->payment_status instanceof \App\Enums\PaymentStatus ? $p->payment_status : \App\Enums\PaymentStatus::tryFrom($p->payment_status)) === \App\Enums\PaymentStatus::Lunas)->count();
        $totalAmount = $payments->filter(fn ($p) => ($p->payment_status instanceof \App\Enums\PaymentStatus ? $p->payment_status : \App\Enums\PaymentStatus::tryFrom($p->payment_status)) === \App\Enums\PaymentStatus::Lunas)->sum('amount');
    @endphp

    <div style="border: 1px solid rgba(156, 163, 175, 0.25); border-radius: 12px; padding: 16px; background: rgba(17, 24, 39, 0.35);">
        <div style="padding-bottom: 12px; border-bottom: 1px solid rgba(156, 163, 175, 0.2); margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 600;">Ringkasan Pembayaran</div>
            <div style="font-size: 12px; opacity: 0.75; margin-top: 2px;">{{ $totalPaid }}/5 pembayaran lunas • Rp {{ number_format($totalAmount, 0, ',', '.') }}</div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($payments as $payment)
                @php
                    $statusValue = $payment->payment_status instanceof \App\Enums\PaymentStatus ? $payment->payment_status->value : $payment->payment_status;
                    $isLunas = $statusValue === 'Lunas';
                    $proofUrl = $payment->payment_proof_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($payment->payment_proof_path) : null;
                    $proofExtension = strtoupper(pathinfo((string) $payment->payment_proof_path, PATHINFO_EXTENSION) ?: 'FILE');
                    $isImageProof = in_array(strtolower(pathinfo((string) $payment->payment_proof_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
                @endphp
                <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
                    <div style="flex: 0 0 56px; width: 56px; min-width: 56px;">
                        @if ($proofUrl)
                            @if ($isImageProof)
                                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" style="display: block; width: 56px; height: 56px; overflow: hidden; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2);">
                                    <img src="{{ $proofUrl }}" alt="Bukti pembayaran tahap {{ $payment->stage_number }}" style="display: block; width: 56px; height: 56px; object-fit: cover;">
                                </a>
                            @else
                                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); background: rgba(17, 24, 39, 0.45); text-decoration: none;">
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 24px; border-radius: 6px; background: rgba(255, 255, 255, 0.9); color: #374151; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; line-height: 1;">{{ $proofExtension }}</span>
                                </a>
                            @endif
                        @else
                            <div style="width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: center; background: rgba(17, 24, 39, 0.45);">
                                <span style="font-size: 11px; font-weight: 700; line-height: 1.1; text-align: center;">Tahap<br>{{ $payment->stage_number }}</span>
                            </div>
                        @endif
                    </div>

                    <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); display: flex; align-items: center; justify-content: center; background: rgba(17, 24, 39, 0.45);">
                        <span style="font-size: 11px; font-weight: 700; line-height: 1.1; text-align: center;">Tahap<br>{{ $payment->stage_number }}</span>
                    </div>

                    <div style="flex: 1 1 auto; min-width: 0;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                            <div style="min-width: 0; flex: 1 1 auto;">
                                <div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                                <div style="font-size: 12px; opacity: 0.78; margin-top: 2px;">Bank: {{ $payment->bank_name ?? '-' }}</div>
                            </div>
                            <span style="flex: 0 0 auto; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; line-height: 1; {{ $isLunas ? 'background: rgba(34, 197, 94, 0.16); color: #86efac;' : 'background: rgba(245, 158, 11, 0.16); color: #fcd34d;' }}">
                                {{ $statusValue }}
                            </span>
                        </div>

                        <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                            <span>Tanggal: {{ $payment->payment_date?->format('d M Y') ?? '-' }}</span>
                            @if($payment->payment_proof_path)
                                <span>Bukti: {{ basename($payment->payment_proof_path) }}</span>
                            @endif
                        </div>

                        @if ($proofUrl)
                            <div style="margin-top: 8px;">
                                <a href="{{ $proofUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; justify-content: center; padding: 6px 10px; border-radius: 8px; background: rgba(59, 130, 246, 0.14); color: #93c5fd; font-size: 12px; font-weight: 600; line-height: 1; text-decoration: none;">
                                    Buka dokumen
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
