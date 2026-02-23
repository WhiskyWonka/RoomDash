<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // El asterisco al principio captura http, https y cualquier subdominio
        Http::fake([
            '*api.pwnedpasswords.com*' => Http::response([], 200),
            '*' => Http::response([], 200),
        ]);
    }
}
