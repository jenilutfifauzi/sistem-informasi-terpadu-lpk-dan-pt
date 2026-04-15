<div style="display: block;">
    <div style="border: 1px solid rgba(156, 163, 175, 0.25); border-radius: 12px; padding: 16px; background: rgba(17, 24, 39, 0.35);">
        <div style="padding-bottom: 12px; border-bottom: 1px solid rgba(156, 163, 175, 0.2); margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 600;">Dokumen Terlampir</div>
            <div style="font-size: 12px; opacity: 0.75;">Total {{ count($documents) }} file tersimpan</div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach ($documents as $document)
                @php
                    $isPublic = $document['disk'] === 'public';
                    $url = $isPublic ? \Illuminate\Support\Facades\Storage::disk('public')->url($document['path']) : null;
                    $extension = strtoupper(pathinfo($document['filename'], PATHINFO_EXTENSION) ?: 'FILE');
                    
                    // Generate download URL for private files
                    $privateUrl = null;
                    if (!$isPublic && isset($document['private_type'], $document['private_id'])) {
                        $privateUrl = match($document['private_type']) {
                            'medical' => route('ctk.medical.download', $document['private_id']),
                            'visa' => route('ctk.visa.download', $document['private_id']),
                            'opp' => route('ctk.opp.download', $document['private_id']),
                            default => null,
                        };
                    }
                @endphp

                <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
                    <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; overflow: hidden; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); background: rgba(17, 24, 39, 0.45); display: flex; align-items: center; justify-content: center;">
                        @if ($isPublic && $document['is_image'])
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: block; width: 56px; height: 56px;">
                                <img src="{{ $url }}" alt="{{ $document['title'] }}" style="display: block; width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; object-fit: cover;">
                            </a>
                        @else
                            <span style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 24px; border-radius: 6px; background: rgba(255, 255, 255, 0.9); color: #374151; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; line-height: 1;">{{ $extension }}</span>
                        @endif
                    </div>

                    <div style="flex: 1 1 auto; min-width: 0;">
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                            <div style="min-width: 0; flex: 1 1 auto;">
                                @if ($isPublic)
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: block; font-size: 14px; font-weight: 600; color: #60a5fa; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $document['title'] }}
                                    </a>
                                @elseif ($privateUrl)
                                    <a href="{{ $privateUrl }}" target="_blank" rel="noopener noreferrer" style="display: block; font-size: 14px; font-weight: 600; color: #fbbf24; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $document['title'] }}
                                    </a>
                                @else
                                    <div style="font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $document['title'] }}</div>
                                @endif

                                <div style="font-size: 12px; opacity: 0.78; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px;">{{ $document['filename'] }}</div>
                            </div>

                            <span style="flex: 0 0 auto; padding: 3px 8px; border-radius: 999px; font-size: 10px; font-weight: 600; line-height: 1; {{ $isPublic ? 'background: rgba(59, 130, 246, 0.16); color: #93c5fd;' : 'background: rgba(245, 158, 11, 0.16); color: #fcd34d;' }}">
                                {{ $isPublic ? 'Public' : 'Private' }}
                            </span>
                        </div>

                        <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                            <span>Sumber: {{ $document['source'] }}</span>
                            @if ($document['uploaded_at'])
                                <span>Upload: {{ $document['uploaded_at'] }}</span>
                            @endif
                            @if ($document['uploader'])
                                <span>Oleh: {{ $document['uploader'] }}</span>
                            @endif
                        </div>

                        <div style="margin-top: 6px;">
                            @if ($isPublic)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="font-size: 12px; font-weight: 600; color: #60a5fa; text-decoration: none;">Buka dokumen</a>
                            @elseif ($privateUrl)
                                <a href="{{ $privateUrl }}" target="_blank" rel="noopener noreferrer" style="font-size: 12px; font-weight: 600; color: #fbbf24; text-decoration: none;">Download dokumen</a>
                            @else
                                <span style="font-size: 12px; color: #fbbf24;">File private tersimpan di server</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
