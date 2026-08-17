@if(is_array($record->castValue()))
    <div class="grid grid-cols-3 gap-4">
        @foreach($record->castValue() as $media)
            @php
                $url = $media['url'] ?? null;
                $type = $media['type'] ?? 'image';

                // Normalize URL to public storage path
                if ($url && !Str::startsWith($url, ['http://', 'https://', '/'])) {
                    $url = asset('storage/' . ltrim($url, '/'));
                }
            @endphp

            @if($url)
                <div class="border rounded p-2 shadow-sm bg-white">
                    @if($type === 'video')
                        <video src="{{ $url }}" controls class="w-full h-32 rounded"></video>
                    @else
                        <img src="{{ $url }}" alt="Hero media" class="w-full h-32 rounded object-cover">
                    @endif

                    <p class="text-xs mt-1 text-center text-gray-600">{{ ucfirst($type) }}</p>

                    <div class="flex justify-between items-center mt-2">
                        {{-- Download button --}}
                        <a href="{{ $url }}" target="_blank"
                           class="text-blue-600 text-xs hover:underline">
                            Download
                        </a>

                        {{-- Delete button (wired to Livewire method in ViewSetting.php) --}}
                        <button
                            wire:click="deleteMedia('{{ $media['url'] }}')"
                            class="text-red-600 text-xs hover:underline"
                        >
                            Delete
                        </button>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
@else
    <p class="text-sm text-gray-500">No media uploaded yet.</p>
@endif
