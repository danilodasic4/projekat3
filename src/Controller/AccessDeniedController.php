<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccessDeniedController extends AbstractController
{
    #[Route('/access-denied', name: 'access_denied')]
    public function index(): Response
    {
        return $this->render('access_denied/index.html.twig');
    }
}
