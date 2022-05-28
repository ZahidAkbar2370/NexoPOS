<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Traits\WithAuthentication;
use Tests\Traits\WithTaxTest;

class CreateTaxTest extends TestCase
{
    use WithAuthentication, WithTaxTest;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testCreateTaxes()
    {
        $this->attemptAuthenticate();
        $this->attemptCreateTax();
    }
}
