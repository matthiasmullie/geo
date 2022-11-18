<?php

namespace MatthiasMullie\Geo\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

$reflect = new \ReflectionMethod('PHPUnit\\Framework\\TestCase', 'toString');
if (method_exists($reflect, 'hasReturnType') && $reflect->hasReturnType()) {
    // since return type hints, the functions we need compat for already exist
    class CompatTestCase extends PHPUnitTestCase
    {
    }
} else {
    class CompatTestCase extends PHPUnitTestCase
    {
        public function expectException($exception)
        {
            if (method_exists('PHPUnit\\Framework\\TestCase', 'expectException')) {
                parent::expectException($exception);
            } else {
                parent::setExpectedException($exception);
            }
        }

        public function assertEqualsWithDelta($expected, $actual, $delta, $message = '')
        {
            if (method_exists('PHPUnit\\Framework\\TestCase', 'assertEqualsWithDelta')) {
                parent::assertEqualsWithDelta($expected, $actual, $delta, $message);
            } else {
                parent::assertEquals($expected, $actual, $message, $delta);
            }
        }
    }
}
