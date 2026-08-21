{{-- resources/views/admin/documents/preview.blade.php --}}
@php
    // Use extension passed from the table, fallback to mime if available
    $ext = strtolower($extension ?? '');
    $mime = strtolower($mime ?? '');
@endphp

<div class="p-6">
    <h2 class="text-lg font-semibold mb-4">Document Preview</h2>

    @if ($ext === 'pdf' || str_contains($mime, 'pdf'))
        {{-- Render PDF securely in an iframe --}}
        <iframe src="{{ $url }}"
                class="w-full h-[600px] border rounded"
                frameborder="0">
        </iframe>
    @elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) || str_contains($mime, 'image'))
        {{-- Render image --}}
        <img src="{{ $url }}"
             alt="Document Preview"
             class="max-w-full h-auto border rounded shadow">
    @else
        {{-- Fallback for unsupported file types --}}
        <div class="bg-gray-100 p-4 rounded border">
            <p class="text-sm text-gray-700">
                Preview not available for this file type (.<strong>{{ $ext ?: $mime }}</strong>).
            </p>
            <a href="{{ $url }}" target="_blank"
               class="inline-block mt-2 text-blue-600 hover:underline">
                Download &amp; Open in New Tab
            </a>
        </div>
    @endif

    {{-- Always provide a secure download link --}}
    <div class="mt-4">
        <a href="{{ $url }}" target="_blank"
           class="inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Open in New Tab
        </a>
    </div>
</div>
