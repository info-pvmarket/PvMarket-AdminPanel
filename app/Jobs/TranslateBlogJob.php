<?php

namespace App\Jobs;

use App\Models\Blog;
use App\Services\TranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use RuntimeException;

class TranslateBlogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;
    public int $timeout = 1800;
    public array $backoff = [30, 120];

    public function __construct(public string $blogId, public string $revision, public string $locale)
    {
        // Explicit durable connection: QUEUE_CONNECTION=sync must not put
        // translation back inside the save request.
        $this->onConnection('blog-translations');
        $this->onQueue('blog-translations');
    }

    public function handle(TranslationService $translator): void
    {
        if ($this->locale === 'en' || ! array_key_exists($this->locale, config('languages.available', []))) return;
        $query = fn () => Blog::whereKey($this->blogId)->where('translation_revision', $this->revision);
        $blog = $query()->first();
        if (! $blog) return;

        $translated = [];
        foreach (['title', 'data'] as $field) {
            $source = (string) ($blog->{$field} ?? '');
            $result = $source === '' ? '' : $translator->translateText($source, $this->locale, 'en');
            if ($result === null) throw new RuntimeException('Blog translation failed; retry required.');
            $translated[$this->locale.'.'.$field] = $result;
        }

        // Atomic condition also protects an edit made while AWS was running.
        // Only translated fields change; source, charts, links and SEO survive.
        $query()->update($translated);
    }
}
