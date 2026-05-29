<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\TenantResource\Pages;

use App\Modules\Admin\Presentation\Resources\TenantResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

final class EditTenant extends EditRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    /** Preenche o formulário lendo features e theme do JSON bruto do tenant. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $tenantData = $this->record->tenantData();

        // Tema visual
        $data['theme_choice'] = $tenantData['theme'] ?? null;

        // Feature flags — expande para campos individuais no formulário
        $features = $tenantData['features'] ?? [];
        foreach (array_keys(config('storefront.feature_labels', [])) as $key) {
            $data["feat_{$key}"] = $features[$key]
                ?? config("storefront.profiles.{$this->record->profile()}.features.{$key}", false);
        }

        return $data;
    }

    /** Antes de salvar, serializa theme e features de volta ao data JSON via DB. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $features = [];
        foreach (array_keys(config('storefront.feature_labels', [])) as $key) {
            if (isset($data["feat_{$key}"])) {
                $features[$key] = (bool) $data["feat_{$key}"];
            }
            unset($data["feat_{$key}"]);
        }

        $themeChoice = $data['theme_choice'] ?? null;
        unset($data['theme_choice']);

        // Salva features e theme no JSON data via DB (contorna serialização do stancl)
        $newData = [];
        if (! empty($features)) {
            $newData['features'] = $features;
        }
        if ($themeChoice !== null) {
            $newData['theme'] = $themeChoice;
        }

        if (! empty($newData)) {
            $this->record->saveTenantData($newData);
        }

        return $data;
    }
}
