<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    // public function test_that_true_is_true(): void
    // {
    //     $this->assertTrue(true);
    // }

    /**
     * Test that it converts UGX to USD correctly.
     */
    public function test_it_converts_UGX_to_USD_correctly(): void
    {
        config(['currencies.supported.USD.rate' => 0.00027]);
        config(['currencies.supported.UGX.rate' => 1]);

        $result = convertCurrency(1000000, 'UGX', 'USD');
        $this->assertEquals(270, $result);
    }

}
