<?php

namespace App\Controller\Admin;

use App\Repository\CarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class CarController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    #[Route('/admin/cars', name: 'admin_cars')]
    public function cars(CarRepository $carRepository): Response
    {
        return $this->render('admin/cars.html.twig', [
            'cars' => $carRepository->findAll(),
        ]);
    }
}
