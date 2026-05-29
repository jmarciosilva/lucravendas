<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\SocialAccountResource\Pages;

use App\Modules\Admin\Presentation\Resources\SocialAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListSocialAccounts extends ListRecords
{
    protected static string $resource = SocialAccountResource::class;
}
