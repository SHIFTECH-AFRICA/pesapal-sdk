<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_demo_page_is_available(): void
    {
        $this->get('/')->assertOk();
    }
}
