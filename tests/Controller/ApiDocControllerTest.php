<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiDocControllerTest extends WebTestCase
{
    public function testApiDocPage()
    {
        $client = static::createClient();

        $client->request('GET', '/api/doc');

        $this->assertResponseIsSuccessful();

        $crawler = $client->getCrawler();
        $this->assertGreaterThan(0, $crawler->filter('#swagger-ui')->count());
    }
}
