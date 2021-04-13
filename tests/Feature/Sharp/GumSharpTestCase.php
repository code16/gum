<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Tests\Feature\Utils\UserModel;
use Code16\Gum\Tests\TestCase;
use Code16\Sharp\Utils\Testing\SharpAssertions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GumSharpTestCase extends TestCase
{
    use RefreshDatabase, SharpAssertions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initSharpAssertions();
        $this->loginAsSharpUser(factory(UserModel::class)->create());
    }
}