<?php

namespace Code16\Gum\Console;

use Code16\Gum\Jobs\RebuildUrls;
use Illuminate\Console\Command;

class RebuildUrlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gum:rebuild_urls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncate and rebuild all content_urls based on Tiles';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        RebuildUrls::dispatch();

        $this->info("Done.");
    }
}