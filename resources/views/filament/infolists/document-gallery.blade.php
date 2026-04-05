<div style="display: block;">
    <div style="border: 1px solid rgba(156, 163, 175, 0.25); border-radius: 12px; padding: 16px; background: rgba(17, 24, 39, 0.35);">
        <div style="padding-bottom: 12px; border-bottom: 1px solid rgba(156, 163, 175, 0.2); margin-bottom: 12px;">
            <div style="font-size: 14px; font-weight: 600;">Dokumen Tahap</div>
            <div style="font-size: 12px; opacity: 0.75;">Total {{ $documents->count() }} dokumen</div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($documents as $doc)
                @php
                    $url = \Illuminate\Support\Facades\Storage::disk('public')->url($doc->file_path);
                    $ext = strtoupper(pathinfo($doc->filename, PATHINFO_EXTENSION) ?: 'FILE');
                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                    $type = $doc->document_type?->getLabel() ?? 'Dokumen';
                    $date = $doc->upload_timestamp?->translatedFormat('d F Y') ?? '-';
                    $uploader = $doc->uploader?->name ?? '-';
                @endphp

                <div style="display: flex; align-items: flex-start; gap: 12px; border: 1px solid rgba(156, 163, 175, 0.18); border-radius: 10px; padding: 10px; background: rgba(31, 41, 55, 0.35);">
                    <div style="flex: 0 0 56px; width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; overflow: hidden; border-radius: 8px; border: 1px solid rgba(156, 163, 175, 0.2); background: rgba(17, 24, 39, 0.45); display: flex; align-items: center; justify-content: center;">
                        @if($isImage)
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: block; width: 56px; height: 56px;">
                                <img src="{{ $url }}" alt="{{ $type }}" style="display: block; width: 56px; height: 56px; min-width: 56px; min-height: 56px; max-width: 56px; max-height: 56px; object-fit: cover;">
                            </a>
                        @else
                            <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; justify-content: center; width: 38px; height: 24px; border-radius: 6px; background: rgba(255, 255, 255, 0.9); color: #374151; font-size: 10px; font-weight: 700; letter-spacing: 0.06em; line-height: 1; text-decoration: none;">
                                {{ $ext }}
                            </a>
                        @endif
                    </div>

                    <div style="flex: 1 1 auto; min-width: 0;">
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: block; font-size: 14px; font-weight: 600; color: #60a5fa; text-decoration: none; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $type }}</a>
                        <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" style="display: block; font-size: 12px; opacity: 0.78; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; color: inherit; text-decoration: none;">{{ $doc->filename }}</a>
                        <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px 14px; font-size: 12px; opacity: 0.75;">
                            <span>Upload: {{ $date }}</span>
                            <span>Oleh: {{ $uploader }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
