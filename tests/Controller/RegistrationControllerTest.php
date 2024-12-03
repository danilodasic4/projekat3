<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
class RegistrationControllerTest extends WebTestCase
{
    
    public function testSuccessfulRegistration(): void
{
    $client = static::createClient();

    $data = [
        'email' => 'testuser@example.com',
        'plainPassword' => 'password123',
        'profile_picture' => null,  // or mock a file upload
        'birthday' => '1990-01-01',
        'gender' => 'male',
        'newsletter' => true,
    ];

    // Sending the POST request with JSON data
    $client->request('POST', '/register', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($data));

    // Check that the response status is 200 OK
    $this->assertResponseStatusCodeSame(200);

    // Check if the response content is valid JSON
    $this->assertJson($client->getResponse()->getContent());
}

// 1) App\Tests\Controller\RegistrationControllerTest::testSuccessfulRegistration
// Failed asserting that the Response status code is 200.
// HTTP/1.1 500 Internal Server Error
// Cache-Control:          max-age=0, must-revalidate, private
// Content-Type:           text/html; charset=UTF-8
// Date:                   Tue, 03 Dec 2024 07:06:46 GMT
// Expires:                Tue, 03 Dec 2024 07:06:46 GMT
// Vary:                   Accept
// X-Debug-Exception:      Class%20%22App%5CController%5CJsonResponse%22%20not%20found
// X-Debug-Exception-File: %2Fvar%2Fwww%2Fsrc%2FController%2FRegistrationController.php:104
// X-Robots-Tag:           noindex

// <!-- Class &quot;App\Controller\JsonResponse&quot; not found (500 Internal Server Error) -->

}


