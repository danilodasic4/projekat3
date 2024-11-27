<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;

class HomeController extends AbstractController
{
    #[Route('/', name: 'homepage', methods:['GET'])]
    #[OA\Get(
        path: '/',
        summary: 'Home page',
        description: 'This route returns the home page.',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully loaded the home page'
            )
        ]
    )]
    public function index()
    {
        return $this->render('index.html.twig');
    }
}


?>