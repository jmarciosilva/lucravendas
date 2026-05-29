<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\TenantResource\Pages;

use App\Modules\Admin\Presentation\Resources\TenantResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    /**
     * Extrai os campos virtuais (theme_choice e feat_*) do payload do formulário
     * e os serializa diretamente no campo data JSON antes do INSERT.
     *
     * Necessário porque esses campos não existem como colunas reais na tabela
     * e o stancl/tenancy redirecionaria qualquer chave desconhecida para data[].
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $features    = [];
        $themeChoice = $data['theme_choice'] ?? null;

        foreach (array_keys(config('storefront.feature_labels', [])) as $key) {
            if (isset($data["feat_{$key}"])) {
                $features[$key] = (bool) $data["feat_{$key}"];
            }
            unset($data["feat_{$key}"]);
        }

        unset($data['theme_choice']);

        $tenantData = [];

        if (! empty($features)) {
            $tenantData['features'] = $features;
        }

        if ($themeChoice !== null) {
            $tenantData['theme'] = $themeChoice;
        }

        if (! empty($tenantData)) {
            $data['data'] = json_encode($tenantData);
        }

        return $data;
    }
}
