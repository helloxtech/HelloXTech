<?php

namespace Tenweb_Builder\Apps\CoPilot\Actions\BasePostActions;


use WP_Query;

class FindPostAndPageByTitle
{

    private string $title;
    private array $searchColumns;

    protected function __construct(string $title, array $searchColumns)
    {
        $this->title = $title;
        $this->searchColumns = array_intersect($searchColumns, array('post_title', 'post_content', 'post_excerpt'));
    }

    public static function find(string $title, array $searchColumns = array('post_title', 'post_content', 'post_excerpt')): array
    {
        return (new static($title, $searchColumns))->handle();
    }

    protected function handle()
    {

        // search columns, search
        $args = array(
            's' => $this->title,
            'post_type' => array('post', 'page'),
            'post_status' => array('publish', 'draft', 'pending', 'private', 'future', 'trash'),
            'posts_per_page' => -1,
            'search_columns' => $this->searchColumns
        );

        return (new WP_Query($args))->posts;
    }
}
