<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Modules\Marketing\Application\UseCases\PublishPost\PublishPostCommand;
use App\Modules\Marketing\Application\UseCases\PublishPost\PublishPostHandler;
use App\Modules\Marketing\Domain\Repositories\ScheduledPostRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job horário que publica todos os posts com publish_at vencido e status pending.
 *
 * Agendado via Schedule em routes/console.php (hourly).
 */
class PublishScheduledPost implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(
        PublishPostHandler                $handler,
        ScheduledPostRepositoryInterface  $repository,
    ): void {
        $duePosts = $repository->findDuePosts(Carbon::now());

        if (empty($duePosts)) {
            return;
        }

        $published = 0;
        $failed    = 0;

        foreach ($duePosts as $post) {
            try {
                $handler->handle(new PublishPostCommand($post->id()));
                $published++;
            } catch (\Throwable $e) {
                // Atualiza o post como falho sem interromper os demais
                $post->markFailed($e->getMessage());
                $repository->save($post);
                $failed++;

                Log::error("PublishScheduledPost: falha ao publicar post #{$post->id()}: {$e->getMessage()}");
            }
        }

        Log::info("PublishScheduledPost concluído: {$published} publicado(s), {$failed} com falha.");
    }
}
