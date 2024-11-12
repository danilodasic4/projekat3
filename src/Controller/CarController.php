<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CarController extends AbstractController
{
    #[Route('/cars', name: 'app_car_index', methods: ['GET'])]
    public function index(): Response
    {
        // Logic to return a list of cars from the database or mock data
        // For example:
        $cars = [
            ['id' => 1, 'model' => 'Car 1', 'make' => 'Brand A'],
            ['id' => 2, 'model' => 'Car 2', 'make' => 'Brand B'],
        ];

        return $this->render('car/index.html.twig', [
            'cars' => $cars
        ]);
    }

    #[Route('/cars/{id}', name: 'app_car_show', methods: ['GET'])]
    public function show($id): Response
    {
        // Logic to return details of a specific car by its ID from the database
        $car = ['id' => $id, 'model' => 'Car ' . $id, 'make' => 'Brand A'];

        return $this->render('car/show.html.twig', [
            'car' => $car
        ]);
    }

    
}
