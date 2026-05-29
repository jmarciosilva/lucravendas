<?php

declare(strict_types=1);

namespace App\Modules\Lojista\Presentation\Pages;

use App\Modules\Tenant\Infrastructure\Models\TenantModel;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
        $tenant   = TenantModel::find(auth()->user()->tenant_id);
        $features = $tenant?->tenantData()['features'] ?? [];

        $featureData = [];
        foreach (array_keys(config('storefront.feature_labels', [])) as $key) {
            $featureData["feature_{$key}"] = $features[$key]
                ?? config("storefront.profiles.{$tenant?->profile()}.features.{$key}", false);
        }

        $this->form->fill(array_merge([
            'name'           => $tenant?->name,
            'origin_zipcode' => $tenant?->origin_zipcode,
        ], $featureData));
    }

    public function form(Form $form): Form
    {
        $featureLabels = config('storefront.feature_labels', []);

        // Monta toggles apenas para features que fazem sentido no perfil do lojista
        // (marketplace e seller_events são controladas pelo super_admin)
        $featuresToggles = collect($featureLabels)
            ->except(['marketplace', 'seller_events_on_marketplace'])
            ->map(fn (string $label, string $key) => Toggle::make("feature_{$key}")
                ->label($label)
                ->inline(false)
            )
            ->values()
            ->all();

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

                Section::make('Módulos da Vitrine')
                    ->description('Ative ou desative seções da sua loja. As opções disponíveis dependem do seu plano.')
                    ->schema($featuresToggles)
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        $formData = $this->form->getState();
        $tenant   = TenantModel::find(auth()->user()->tenant_id);

        if (! $tenant) {
            Notification::make()->title('Loja não encontrada.')->danger()->send();
            return;
        }

        // Extrai os valores de feature dos dados do formulário
        $features = [];
        foreach (array_keys(config('storefront.feature_labels', [])) as $key) {
            if (isset($formData["feature_{$key}"])) {
                $features[$key] = (bool) $formData["feature_{$key}"];
            }
        }

        $tenant->update([
            'name'           => $formData['name'],
            'origin_zipcode' => $formData['origin_zipcode'] ?? null,
        ]);

        if (! empty($features)) {
            $tenant->saveTenantData(['features' => $features]);
        }

        Notification::make()->title('Configurações salvas com sucesso.')->success()->send();
    }
}
