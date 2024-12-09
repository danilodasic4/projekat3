<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    // Test that the registration form loads correctly (GET request)
    public function testRegistrationPageIsAccessible(): void
    {
        $client = static::createClient();

        // Make a GET request to the registration page
        $client->request('GET', '/register');

        // Assert that the response status code is 200 (OK)
        $this->assertResponseIsSuccessful();

        // Ensure the registration form is present on the page
        $this->assertSelectorExists('form[name="registration_form"]');
        $this->assertSelectorExists('input[name="registration_form[email]"]');
        $this->assertSelectorExists('input[name="registration_form[plainPassword][first]"]');
        $this->assertSelectorExists('input[name="registration_form[plainPassword][second]"]');
    }

    // Test successful form submission (POST request with valid data)
    public function testSuccessfulRegistration(): void
{
    $client = static::createClient();

    // Make a GET request to the registration page first
    $client->request('GET', '/register');

    // Submit the form with valid data
    $client->submitForm('Register', [
        'registration_form[email]' => 'valid@example.com',
        'registration_form[plainPassword][first]' => 'validpassword123',
        'registration_form[plainPassword][second]' => 'validpassword123',  // Confirmation matches
        'registration_form[birthday]' => '1990-01-01',
        'registration_form[gender]' => 'male',
        'registration_form[agreeTerms]' => true,
        'registration_form[newsletter]' => true,
    ]);

    // Assert that the response is a redirection (302)
    $this->assertResponseStatusCodeSame(302);

    // // Assert that the redirection goes to the login page
     $this->assertResponseHeaderSame('Location', '/login');

    // // Follow the redirect
    $client->followRedirect();

    // // Now, we should be on the login page after a successful registration
    $this->assertSelectorTextContains('h1', 'Login'); 
}
    public function testInvalidRegistration(): void
{
    $client = static::createClient();

    // Make a GET request to the registration page first
    $client->request('GET', '/register');

    // Submit the form with invalid data (e.g., missing password confirmation)
    $client->submitForm('Register', [
        'registration_form[email]' => 'invalid@example.com',
        'registration_form[plainPassword][first]' => 'validpassword123',
        'registration_form[plainPassword][second]' => '', // Invalid: confirmation is missing
        'registration_form[birthday]' => '1990-01-01',
        'registration_form[gender]' => 'male',
        'registration_form[agreeTerms]' => true,
        'registration_form[newsletter]' => true,
    ]);


    $this->assertResponseIsSuccessful(); // The form should not redirect

}
}

