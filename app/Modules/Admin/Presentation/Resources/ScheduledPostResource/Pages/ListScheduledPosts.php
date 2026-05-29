<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ScheduledPostResource\Pages;

use App\Modules\Admin\Presentation\Resources\ScheduledPostResource;
use Filament\Resources\Pages\ListRecords;

class ListScheduledPosts extends ListRecords
{
    protected static string $resource = ScheduledPostResource::class;
}
