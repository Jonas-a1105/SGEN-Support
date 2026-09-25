<?php
/**
 * Pest Configuration - Testing Framework
 */

uses()->in('Unit')->group('unit');

/**
 * Helper functions for tests
 */
if (!function_exists('mock')) {
    function mock(string $className, array $methods = []): mixed
    {
        return \Mockery::mock($className, $methods);
    }
}

if (!function_exists('factory')) {
    function factory(string $className, array $attributes = []): object
    {
        return (new \Tests\Support\ModelFactory($className))->create($attributes);
    }
}

/**
 * Global setup
 */
beforeAll(function () {
    $_ENV['APP_ENV'] = 'testing';
    $_ENV['DB_NAME'] = 'sgen_test';
});

afterAll(function () {
    \Mockery::close();
});
