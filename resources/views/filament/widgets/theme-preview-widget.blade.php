<x-filament-widgets::widget>
    <x-filament::section>
        <div class="p-4 theme-surface rounded shadow">
            <h2 class="text-lg font-bold theme-primary mb-3">
                Theme Preview
            </h2>

            <p class="text-sm theme-text mb-4">
                Active Preset: <span class="theme-primary font-semibold">{{ ucfirst($preset) }}</span> /
                Mode: <span class="theme-secondary font-semibold">{{ ucfirst($mode) }}</span>
            </p>

            <div class="flex gap-4 mb-4">
                <button class="btn-primary px-3 py-2 rounded">Primary Button</button>
                <button class="btn-secondary px-3 py-2 rounded">Secondary Button</button>
            </div>

            <div class="mb-4">
                <input type="text" class="form-input w-full" placeholder="Form input preview">
            </div>

            <div class="flex gap-4">
                <a href="#" class="theme-link hover:underline">Link Preview</a>
                <span class="theme-text">Body Text Preview</span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
