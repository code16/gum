<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RefactorWithoutSectionsAndContentUrls extends Migration
{
    public function up()
    {
        $this->migrateSectionsToPages();
        
        $this->linkTileblocksToPages();
        
        $this->linkSidePanelsToPages();
        
        $this->movePagegroupsToSubpages();
        
        $this->linkTilesToPages();
        
        $this->adaptPagesTable();

        Schema::dropIfExists("sections");
        Schema::dropIfExists("pagegroups");
        Schema::dropIfExists("content_urls");
    }

    private function migrateSectionsToPages()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('domain')->nullable();
            $table->string('style_key')->nullable();
        });

        DB::table("sections")
            ->get()
            ->each(function($section) {
                DB::table("pages")
                    ->insert([
                        'id' => $section->id,
                        'slug' => $section->slug,
                        'title' => $section->title,
                        'short_title' => $section->short_title,
                        'body_text' => null,
                        'pagegroup_order' => 100,
                        'pagegroup_id' => null,
                        'created_at' => $section->created_at,
                        'updated_at' => $section->updated_at,
                        'heading_text' => $section->heading_text,
                        'has_news' => $section->has_news,
                        'domain' => $section->domain,
                        'style_key' => $section->style_key,
                    ]);
            });
        
        DB::table("taggables")
            ->where("taggable_type", "Code16\Gum\Models\Section")
            ->update([
                "taggable_type" => "Code16\Gum\Models\Page"
            ]);
    }

    private function linkTileblocksToPages()
    {
        Schema::table('tileblocks', function (Blueprint $table) {
            $table->string('page_id')->nullable();
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
        });

        DB::table("tileblocks")
            ->get()
            ->each(function($tileblock) {
                DB::table("tileblocks")
                    ->where("id", $tileblock->id)
                    ->update([
                        "page_id" => $tileblock->section_id
                    ]);
            });

        Schema::table('tileblocks', function (Blueprint $table) {
            if(config("database.default") != "sqlite") {
                $table->dropForeign("tileblocks_section_id_foreign");
            }
            $table->dropColumn("section_id");
        });
    }

    private function linkSidePanelsToPages()
    {
        Schema::table('sidepanels', function (Blueprint $table) {
            $table->string('page_id')->nullable();
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('cascade');
        });
        
        DB::table("sidepanels")
            ->get()
            ->each(function($sidepanel) {
                $page = DB::table("pages")->where("id", $sidepanel->container_id)->first();
                
                DB::table("sidepanels")
                    ->where("id", $sidepanel->id)
                    ->update([
                        "page_id" => $page ? $page->id : null
                    ]);
            });

        Schema::table('sidepanels', function (Blueprint $table) {
            $table->dropColumn("container_id");
        });

        Schema::table('sidepanels', function (Blueprint $table) {
            $table->dropColumn("container_type");
        });

        Schema::table('sidepanels', function (Blueprint $table) {
            $table->dropColumn("related_content_id");
        });

        Schema::table('sidepanels', function (Blueprint $table) {
            $table->dropColumn("related_content_type");
        });
        
        DB::table("sidepanels")
            ->whereNull("page_id")
            ->delete();
    }

    private function linkTilesToPages()
    {
        Schema::table('tiles', function (Blueprint $table) {
            $table->string('page_id')->nullable();
            $table->foreign('page_id')
                ->references('id')
                ->on('pages')
                ->onDelete('set null');
        });

        DB::table("tiles")
            ->get()
            ->filter(function($tile) {
                return strlen($tile->linkable_id) > 0;
            })
            ->each(function($tile) {
                if($page = DB::table("pages")->where("id", $tile->linkable_id)->first()) {
                    DB::table("tiles")
                        ->where("id", $tile->id)
                        ->update([
                            "page_id" => $page->id
                        ]);
                } else {
                    Log::warning("Delete tile " . $tile->id);
                    DB::table("tiles")
                        ->where("id", $tile->id)
                        ->delete();
                }
            });

        Schema::table('tiles', function (Blueprint $table) {
            $table->dropColumn("linkable_id");
        });

        Schema::table('tiles', function (Blueprint $table) {
            $table->dropColumn("linkable_type");
        });
        
        Schema::table('tiles', function (Blueprint $table) {
            if(config("database.default") != "sqlite") {
                $table->dropForeign("tiles_content_url_id_foreign");
            }
        });

        Schema::table('tiles', function (Blueprint $table) {
            $table->dropColumn("content_url_id");
        });
    }

    private function movePagegroupsToSubpages()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->boolean('is_pagegroup')->default(false);
            if(config("database.default") != "sqlite") {
                $table->dropForeign("pages_pagegroup_id_foreign");
            }
        });

        DB::table("pagegroups")
            ->get()
            ->each(function($pagegroup) {
                $page = DB::table("pages")
                    ->insert([
                        'id' => $pagegroup->id,
                        'slug' => $pagegroup->slug,
                        'title' => $pagegroup->title,
                        'short_title' => $pagegroup->short_title,
                        'is_pagegroup' => true,
                        'body_text' => null,
                        'pagegroup_order' => 100,
                        'pagegroup_id' => null,
                        'created_at' => $pagegroup->created_at,
                        'updated_at' => $pagegroup->updated_at,
                        'heading_text' => null,
                        'has_news' => false,
                        'domain' => null,
                        'style_key' => null,
                    ]);
            });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreign('pagegroup_id')
                ->references('id')
                ->on('pages')
                ->onDelete('set null');
        });
    }

    private function adaptPagesTable()
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('admin_label')->nullable();
        });
        
        DB::table("pages")
            ->whereNull("domain")
            ->get()
            ->each(function($page) {
                DB::table("pages")
                    ->where("id", $page->id)
                    ->update(["domain" => $this->findDomainForPage($page)]);
            });
    }

    private function findDomainForPage(object $page): ?string
    {
        if($page->domain) {
            return $page->domain;
        }
        
        if($page->pagegroup_id) {
            return $this->findDomainForPage(
                DB::table("pages")->find($page->pagegroup_id)
            );
        }
        
        $tiles = DB::table("tiles")
            ->where("page_id", $page->id)
            ->get();

        if(count($tiles) > 1) {
            $domains = [];
            foreach($tiles as $tile) {
                $domains[$this->findDomainForPage(
                    DB::table("pages")
                        ->find(
                            DB::table("tileblocks")->find($tile->tileblock_id)->page_id
                        )
                )] = true;
            }
            
            if(count(array_keys($domains)) > 1) {
                Log::warning("Page {$page->id} is multidomain: " . implode(", ", $domains));
            }
            
            return array_key_first($domains);
        }
        
        if(count($tiles) == 1) {
            return $this->findDomainForPage(
                DB::table("pages")
                    ->find(
                        DB::table("tileblocks")->find($tiles[0]->tileblock_id)->page_id
                    )
            );
        }
        
        // Orphan page
        return null;
    }
}
