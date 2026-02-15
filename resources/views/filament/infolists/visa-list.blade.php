<div class="space-y-2">
    @foreach($visas as $visa)
        <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <p class="font-semibold text-sm">
                            @if($visa->visa_number)
                                Visa #{{ $visa->visa_number }}
                            @else
                                Pengajuan Visa
                            @endif
                        </p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $visa->application_status === 'Terbit'
                                ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }}">
                            {{ $visa->application_status }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Tanggal Pengajuan: {{ $visa->application_date->format('d M Y') }}
                    </p>
                    @if($visa->application_status === 'Terbit')
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Tanggal Terbit: {{ $visa->issuance_date->format('d M Y') }}
                        </p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Kadaluarsa: {{ $visa->expiry_date->format('d M Y') }}
                        </p>
                        @if($visa->issuing_country)
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Negara: {{ $visa->issuing_country }} | Jenis: {{ $visa->visa_type }}
                            </p>
                        @endif
                        @if($visa->expiry_date->diffInDays(now()) <= 30 && $visa->expiry_date->isFuture())
                            <p class="text-sm text-orange-600 dark:text-orange-400 mt-1 font-semibold">
                                ⚠️ Visa akan kadaluarsa dalam {{ $visa->expiry_date->diffInDays(now()) }} hari
                            </p>
                        @elseif($visa->expiry_date->isPast())
                            <p class="text-sm text-red-600 dark:text-red-400 mt-1 font-semibold">
                                ⚠️ Visa sudah kadaluarsa
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>
