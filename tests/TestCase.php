<?php

namespace Tests;

use App\Actions\Media\GenerateMediaUrlAction;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        GenerateMediaUrlAction::clearCache();
    }
}
