<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class CarController extends AbstractController
{
    #[Route('/cars', name: 'app_car_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Logic to return a list of cars from the database would go here
        return new JsonResponse(['message' => 'List of cars']);
    }

    #[Route('/cars/{id}', name: 'app_car_show', methods: ['GET'])]
    public function show($id): JsonResponse
    {
        // Logic to return details of a specific car by its ID from the database would go here
        return new JsonResponse(['message' => "Details of car with ID: $id"]);
    }

    #[Route('/cars', name: 'app_car_create', methods: ['POST'])]
    public function create(): JsonResponse
    {
        // Logic to create a new car in the database would go here
        return new JsonResponse(['message' => 'Car created']);
    }

    #[Route('/cars/{id}', name: 'app_car_update', methods: ['PUT'])]
    public function update($id): JsonResponse
    {
        // Logic to update a car in the database by its ID would go here
        return new JsonResponse(['message' => "Car with ID $id updated"]);
    }

    #[Route('/cars/{id}', name: 'app_car_delete', methods: ['DELETE'])]
    public function delete($id): JsonResponse
    {
        // Logic to delete a car from the database by its ID would go here
        return new JsonResponse(['message' => "Car with ID $id deleted"]);
    }
}
