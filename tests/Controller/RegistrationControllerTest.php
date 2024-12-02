<?php
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class RegistrationControllerTest extends WebTestCase
{
    public function testSuccessfulRegistration()
    {
        $client = static::createClient();
        
        // Visit the registration page
        $crawler = $client->request('GET', '/register');

        //$this->assertSelectorExists('form[name="registrationForm"]');
        // Check that the form is displayed
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register');

        // Fill in the registration form
        $form = $crawler->selectButton('Register')->form();

        // Set the form data (you might need to adjust these field names based on the HTML)
        $form['registrationForm[email]'] = 'test@example.com';
        $form['registrationForm[plainPassword][first]'] = 'password123';
        $form['registrationForm[plainPassword][second]'] = 'password123';
        $form['registrationForm[birthday]'] = '1990-01-01';
        $form['registrationForm[gender]'] = 'male';
        $form['registrationForm[newsletter]'] = true;
        // (If you have file upload, make sure to add the file data here)
        $form['registrationForm[profile_picture]'] = null; // Or provide a file path for testing file upload

        // Submit the form
        $client->submit($form);

        // Check that the user is redirected after successful registration
        $this->assertResponseRedirects('/login');
        $crawler = $client->followRedirect();

        // Check for success message
        $this->assertSelectorTextContains('.alert-success', 'Registration successful, now you can login!');
    }

    public function testFailedRegistrationDueToInvalidData()
    {
        $client = static::createClient();
        
        // Visit the registration page
        $crawler = $client->request('GET', '/register');

        // Check that the form is displayed
        $this->assertResponseIsSuccessful();

        // Fill in the registration form with invalid data
        $form = $crawler->selectButton('Register')->form();

        // Set invalid email
        $form['registrationForm[email]'] = 'invalid-email';
        $form['registrationForm[plainPassword][first]'] = 'short';
        $form['registrationForm[plainPassword][second]'] = 'short';

        // Submit the form
        $client->submit($form);

        // Check that the form validation errors are displayed
        $this->assertSelectorTextContains('.alert-danger', 'Please enter a valid email address');
        $this->assertSelectorTextContains('.alert-danger', 'Your password should be at least 6 characters');
    }
}




