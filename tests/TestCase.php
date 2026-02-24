<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Ensure the central pgsql connection is default before migrate:fresh runs.
     *
     * stancl/tenancy can switch the default DB connection to 'tenant' during
     * app bootstrapping. Without this, RefreshDatabase's migrate:fresh would
     * run on the tenant connection (no schema set), causing:
     *   SQLSTATE[3F000]: no schema has been selected
     *
     * Note: no return type to stay compatible with RefreshDatabase trait signature.
     */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.ReturnTypeHint
    protected function beforeRefreshingDatabase()
    {
        $this->app['config']['database.default'] = 'pgsql';
    }
}
