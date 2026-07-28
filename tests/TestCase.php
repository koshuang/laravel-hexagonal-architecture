<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\App;

/**
 * @method \Illuminate\Foundation\Application app()
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}
