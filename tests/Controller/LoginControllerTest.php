<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;

class LoginControllerTest extends WebTestCase
{
    public function testLoginPageForGuest()
{
    $client = static::createClient();
    $crawler = $client->request('GET', '/login');

    // Assert that the response is successful
    $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    // Assert that the login form is present
    $this->assertSelectorExists('form[action="/login"]');

    // Assert that the button with text "Login" is present
    $this->assertSelectorTextContains('button[type="submit"]', 'Login');
}


    public function testLoginPageForLoggedInUser()
    {
        $client = static::createClient();
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);

        $client->request('GET', '/login');

        // Assert redirection to car index page
        $this->assertResponseRedirects('/cars');
    }

    public function testLogoutRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/logout');

        // Assert redirection (Symfony handles logout automatically)
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}
