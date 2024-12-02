<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiDocControllerTest extends WebTestCase
{
    public function testApiDocPage()
    {
        $client = static::createClient();

        // Pozivanje API dokumentacije stranice
        $client->request('GET', '/api/doc');

        // Proverava da li je status 200 (stranica je uspešno učitana)
        $this->assertResponseIsSuccessful();

        // Proverava da li se Swagger UI učitava (provera prisutnosti div-a sa id="swagger-ui")
        $crawler = $client->getCrawler();
        $this->assertGreaterThan(0, $crawler->filter('#swagger-ui')->count());
    }
}
