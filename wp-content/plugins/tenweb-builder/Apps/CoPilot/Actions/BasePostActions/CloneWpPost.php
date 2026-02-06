<?php

namespace Tenweb_Builder\Apps\CoPilot\Actions\BasePostActions;

use Tenweb_Builder\Apps\CoPilot\Enums\WpPostType;

class CloneWpPost extends BaseWpPostAction
{
    private ?int $postId;


    protected function __construct(string $postType, ?int $postId)
    {
        parent::__construct($postType);
        $this->postId = $postId;
    }

    public static function runById(?int $postId, string $postType = WpPostType::PAGE, ?string $newTitle = null, ?string $newStatus = null)
    {
        return (new static($postType, $postId))->handle($newTitle, $newStatus);
    }

    protected function handle(?string $newTitle = null, ?string $newStatus = null)
    {
        if (! $this->postId) {
            return null;
        }

        return $this->repo->clone($this->postId, $newTitle, $newStatus);
    }

    public static function runByTitle(string $title, string $postType = WpPostType::PAGE, ?string $newTitle = null, ?string $newStatus = null)
    {
        $postId = FindWpPostIdByTitle::find($title, $postType);

        return (new static($postType, $postId))->handle($newTitle, $newStatus);
    }
}
