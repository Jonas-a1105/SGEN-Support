<?php
/**
 * Pest Configuration - Testing Framework
 */

use Pest\Plugin\Watch\WatchPlugin;

uses()->in('Unit')->group('unit');
uses()->in('Feature')->group('feature');
uses()->in('Integration')->group('integration');

/**
 * Helper functions for tests
 */
function mock(string $className, array $methods = []): \PHPUnit\Framework\MockObject\MockObject
{
    return \Mockery::mock($className, $methods);
}

function factory(string $className, array $attributes = []): object
{
    return (new \Tests\Support\ModelFactory($className))->create($attributes);
}

/**
 * Global setup
 */
beforeAll(function () {
    // Setup test database, config, etc.
    $_ENV['APP_ENV'] = 'testing';
    $_ENV['DB_NAME'] = 'sgen_test';
});

afterAll(function () {
    \Mockery::close();
});