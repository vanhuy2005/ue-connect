<?php

namespace Tests\Unit\Models;

use App\Models\Community;
use App\Models\Post;
use Tests\TestCase;

class PostModelTest extends TestCase
{
    public function test_community_relation_uses_scope_id(): void
    {
        $post = new Post;

        $relation = $post->community();

        $this->assertSame('scope_id', $relation->getForeignKeyName());
        $this->assertSame((new Community)->getTable(), $relation->getRelated()->getTable());
    }
}
