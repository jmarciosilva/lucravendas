<?php

declare(strict_types=1);

namespace App\Modules\Admin\Presentation\Resources\ScheduledPostResource\Pages;

use App\Modules\Admin\Presentation\Resources\ScheduledPostResource;
use Filament\Resources\Pages\ViewRecord;

class ViewScheduledPost extends ViewRecord
{
    protected static string $resource = ScheduledPostResource::class;
}
