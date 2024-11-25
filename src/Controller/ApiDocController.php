<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiDocController extends AbstractController
{
    #[Route('/api/doc', name: 'api_doc', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('@NelmioApiDoc/SwaggerUi/index.html.twig');
    }
}
