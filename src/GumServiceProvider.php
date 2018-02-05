<?php

namespace Code16\Gum;

use Code16\Gum\Middleware\BuildGumContext;
use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Observers\ContentUrlObserver;
use Code16\Gum\Models\Observers\PagegroupObserver;
use Code16\Gum\Models\Observers\PageObserver;
use Code16\Gum\Models\Observers\SectionObserver;
use Code16\Gum\Models\Observers\TileblockObserver;
use Code16\Gum\Models\Observers\TileObserver;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Sharp\SharpServiceProvider;
use Illuminate\Support\ServiceProvider;

class GumServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(dirname(__DIR__) . '/database/migrations');

        Tile::observe(TileObserver::class);
        Tileblock::observe(TileblockObserver::class);
        Page::observe(PageObserver::class);
        Pagegroup::observe(PagegroupObserver::class);
        Section::observe(SectionObserver::class);
        ContentUrl::observe(ContentUrlObserver::class);
    }

    public function register()
    {
        $this->registerMiddleware();
        $this->app->register(SharpServiceProvider::class);
    }

    protected function registerMiddleware()
    {
        $this->app['router']->aliasMiddleware(
            'build_gum_context', BuildGumContext::class
        );
    }
}