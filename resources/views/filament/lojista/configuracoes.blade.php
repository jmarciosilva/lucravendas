<x-filament-panels::page>
    <form wire:submit="salvar">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Salvar Configurações
            </x-filament::button>
        </div>
    </form>

    <x-filament-actions::modals />
</x-filament-panels::page>
