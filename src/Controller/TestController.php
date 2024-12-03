<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class TestController extends AbstractController
{
    public function __construct(private readonly HttpClientInterface $httpClient) {}

    #[Route('/test-http-client', name: 'test_http_client')]
    public function test(): Response
    {
        $response = $this->httpClient->request('GET', 'https://httpbin.org/get');
        return new Response($response->getContent());
    }
}
