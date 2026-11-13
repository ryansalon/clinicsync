<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * The landing page routes guests to the sign-in screen.
     */
    public function test_the_application_redirects_guests_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}
