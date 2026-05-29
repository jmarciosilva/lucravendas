<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Pages;

use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Página de configurações da loja do lojista autenticado.
 * Permite editar nome e CEP de origem (usado no cálculo de frete).
 */
class LojistaConfiguracoes extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Configurações';

    protected static ?string $navigationGroup = 'Configurações';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.lojista.configuracoes';

    public ?array $data = [];

    public function mount(): void
    {
        $tenant = TenantModel::find(auth()->user()->tenant_id);

        $this->form->fill([
            'name'           => $tenant?->name,
            'origin_zipcode' => $tenant?->origin_zipcode,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Dados da Loja')->schema([
                    TextInput::make('name')
                        ->label('Nome da Loja')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('origin_zipcode')
                        ->label('CEP de Origem (para cálculo de frete)')
                        ->mask('99999-999')
                        ->maxLength(9)
                        ->placeholder('00000-000'),
                ])->columns(2),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        $data   = $this->form->getState();
        $tenant = TenantModel::find(auth()->user()->tenant_id);

        if (! $tenant) {
            Notification::make()->title('Loja não encontrada.')->danger()->send();
            return;
        }

        $tenant->update([
            'name'           => $data['name'],
            'origin_zipcode' => $data['origin_zipcode'] ?? null,
        ]);

        Notification::make()->title('Configurações salvas com sucesso.')->success()->send();
    }
}
