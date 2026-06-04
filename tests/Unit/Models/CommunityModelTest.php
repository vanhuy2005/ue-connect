<?php

namespace Tests\Unit\Models;

use App\Models\Community;
use App\Models\User;
use Tests\TestCase;

class CommunityModelTest extends TestCase
{
    public function test_owner_relation_uses_owner_id(): void
    {
        $community = new Community;

        $relation = $community->owner();

        $this->assertSame('owner_id', $relation->getForeignKeyName());
        $this->assertSame((new User)->getTable(), $relation->getRelated()->getTable());
    }

    public function test_creator_relation_uses_created_by(): void
    {
        $community = new Community;

        $relation = $community->creator();

        $this->assertSame('created_by', $relation->getForeignKeyName());
        $this->assertSame((new User)->getTable(), $relation->getRelated()->getTable());
    }
}
