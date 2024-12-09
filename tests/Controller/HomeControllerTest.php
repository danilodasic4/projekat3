<?php

namespace App\Tests\Controller;

use App\Entity\User; 
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomepageForGuest(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Welcome to Our Application');
    }

    public function testHomepageForLoggedInUser(): void
    {
        $client = static::createClient();
        $container = static::getContainer();

        $userRepository = $container->get('doctrine')->getRepository(User::class);
        $testUser = $userRepository->findOneByEmail('user1@example.com');

        if (!$testUser) {
            throw new \Exception('Test user not found. Please create a user with email "user1@example.com" for testing.');
        }

        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-success', 'Hello, user1@example.com! You are logged in.');
    }
}
