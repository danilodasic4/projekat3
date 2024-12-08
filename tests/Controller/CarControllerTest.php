<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Car;
use App\Service\RegistrationCostService;
use Symfony\Component\HttpFoundation\Request;

class CarControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        parent::setUp();
        // Initialize the test client
        $this->client = static::createClient();
        // Automatically authenticate the user before each test
        $this->authenticate('user1@example.com');
    }
    private function authenticate(string $email): void
    {
        // Fetch the user from the database based on the email
        $user = $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception('User not found in the database for email: ' . $email);
        }

        // Log the user in
        $this->client->loginUser($user);

        // Ensure the user has the ROLE_USER role
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');
    }



    public function testGetUserCars(): void
{
    // Send a GET request to fetch the cars associated with the authenticated user
    $this->client->request('GET', '/api/users/' . $this->getAuthenticatedUser()->getId() . '/cars');

    // Assert that the response status is 200 (OK)
    $this->assertResponseStatusCodeSame(Response::HTTP_OK);

    // Optional: Assert that the JSON response contains expected data (uncomment if needed)
    // $this->assertJsonContains([
    //     'id' => 1,  // Example ID of a car associated with the user
    // ]);
}

private function getAuthenticatedUser(): User
{
    // Fetch the authenticated user from the database
    return $this->client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
}
public function testSubmitValidData(): void
    {
        $formData = [
            'brand' => 'Tesla',
            'model' => 'Model S',
            'year' => 2022,
            'engineCapacity' => 3000,
            'horsePower' => 670,
            'color' => 'Red',
            'registrationDate' => '2022-01-01',
        ];

        $expectedCar = new Car();
        $expectedCar->setBrand('Tesla');
        $expectedCar->setModel('Model S');
        $expectedCar->setYear(2022);
        $expectedCar->setEngineCapacity(3000);
        $expectedCar->setHorsePower(670);
        $expectedCar->setColor('Red');
        $expectedCar->setRegistrationDate(new \DateTime('2022-01-01'));

        $form = $this->factory->create(CarFormType::class, new Car());
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expectedCar, $form->getData());
    }

    public function testCreateCar(): void
    {
        // Prepare data for creating a new car
        $carData = [
            'car[brand]' => 'Toyota', // Field names must match the ones in the CarFormType
            'car[model]' => 'Corolla',
            'car[year]' => 2020,
            'car[engineCapacity]' => 1800,
            'car[horsePower]' => 150,
            'car[color]' => 'Blue',
            'car[registrationDate]' => '2024-01-01',
            'car[save]' => 'Save Car', // Submit button value (important)
        ];

        // Send a POST request to create the new car
        $this->client->request(
            'POST',
            '/cars/create', // Your route for creating cars
            $carData, // Send the data as form parameters (application/x-www-form-urlencoded)
            [],
            [] // Use correct content type for form submission
        );
        // Assert that the response status is 201 (Created)
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        // Fetch the car from the database to verify it was created
        $car = $this->client->getContainer()->get('doctrine')->getRepository(Car::class)->findAll();
        dd($car);
        $this->assertNotNull($car, 'Car was not created in the database.');

        // Verify that the car's details match the input data
        $this->assertEquals('Toyota', $car->getBrand());
        $this->assertEquals(2020, $car->getYear());
        $this->assertEquals(1800, $car->getEngineCapacity());
        $this->assertEquals(150, $car->getHorsePower());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals('2024-01-01', $car->getRegistrationDate()->format('Y-m-d'));
    }
    
    public function testShowCar()
    {
        $client = static::createClient();
    
        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);
    
        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');
    
        // Find a car by its ID (assuming ID 1 exists in the database)
        $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find(1);
        
        // Ensure the car exists in the database
        $this->assertNotNull($car, 'Car with ID 1 should exist in the database.');
    
        // Send a GET request to retrieve the car details
        $client->request('GET', '/cars/' . $car->getId());
    
        // Verify that the response status is 200 OK
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Expected response status 200 OK');
    
        // Verify that the response is in JSON format and contains the correct car details
        $this->assertJsonContains([
            'brand' => $car->getBrand(),
            'model' => $car->getModel(),
            'year' => $car->getYear(),
            'engineCapacity' => $car->getEngineCapacity(),
            'horsePower' => $car->getHorsePower(),
            'color' => $car->getColor(),
            'registrationDate' => $car->getRegistrationDate()->format('Y-m-d'),
            'user' => [
                'email' => $car->getUser()->getEmail(),
            ],
        ]);
    }
public function testEditCar()
{
    $client = static::createClient();

    // Log in a user with the role ROLE_USER
    $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
    $client->loginUser($user);

    // Verify that the user has the role ROLE_USER
    $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

    // Find the car we want to update (assuming it has ID 1)
    $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find(1);
    
    // Ensure the car exists in the database
    $this->assertNotNull($car, 'Car with ID 1 should exist in the database.');

    // Data we will send for updating the car
    $updatedCarData = [
        'brand' => 'Updated Brand',
        'model' => 'Updated Model',
        'year' => 2023,
        'engineCapacity' => 2000,
        'horsePower' => 150,
        'color' => 'Blue',
        'registrationDate' => '2023-12-01',
    ];

    // Send a PUT request to update the car
    $client->request('PUT', '/cars/update/' . $car->getId(), [], [], ['CONTENT_TYPE' => 'application/json'], json_encode($updatedCarData));

    // Verify that the response status is 200 OK
    $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Expected response status 200 OK');

    // Verify that the data was updated in the database (retrieve the car from the database after the update)
    $updatedCar = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find($car->getId());

    // Verify that the car data has been updated
    $this->assertEquals($updatedCarData['brand'], $updatedCar->getBrand());
    $this->assertEquals($updatedCarData['model'], $updatedCar->getModel());
    $this->assertEquals($updatedCarData['year'], $updatedCar->getYear());
    $this->assertEquals($updatedCarData['engineCapacity'], $updatedCar->getEngineCapacity());
    $this->assertEquals($updatedCarData['horsePower'], $updatedCar->getHorsePower());
    $this->assertEquals($updatedCarData['color'], $updatedCar->getColor());
    $this->assertEquals(new \DateTime($updatedCarData['registrationDate']), $updatedCar->getRegistrationDate());
}

public function testDeleteCar()
{
    $client = static::createClient();

    // Log in a user with the role ROLE_USER
    $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
    $client->loginUser($user);

    // Verify that the user has the role ROLE_USER
    $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

    // Find the car we want to delete (assuming it has ID 1)
    $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find(1);
    
    // Ensure the car exists in the database
    $this->assertNotNull($car, 'Car with ID 1 should exist in the database.');

    // Send a DELETE request to delete the car
    $client->request('DELETE', '/cars/delete/' . $car->getId());

    // Verify that the response status is 200 OK
    $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Expected response status 200 OK');

    // Verify that the car no longer exists in the database
    $deletedCar = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find($car->getId());

    // Assert that the car is no longer in the database (it should be null)
    $this->assertNull($deletedCar, 'Car should be deleted from the database.');
}

public function testCalculateRegistrationCostWithValidCarAndDiscountCode()
    {
        // Create a client to send requests
        $client = static::createClient();

        // Mock the car repository and the car entity
        $carRepository = $this->createMock(CarRepository::class);
        $carRepository->method('find')->willReturn($this->createMock(Car::class));
        
        // Mock the registration cost service
        $registrationCostService = $this->createMock(RegistrationCostService::class);
        $registrationCostService->method('calculateRegistrationCost')->willReturn(500);
        $registrationCostService->method('applyDiscount')->willReturn(400);

        // Send a GET request with carId and discountCode query parameters
        $client->request(Request::METHOD_GET, '/cars/calculate-registration-cost', [
            'carId' => 1,
            'discountCode' => 'VALID_CODE'
        ]);

        // Assert that the status code is 200 (OK)
        $this->assertResponseIsSuccessful();

        // Assert that the response contains the correct values
        $this->assertJsonContains([
            'carId' => 1,
            'registrationCost' => 500,
            'finalCost' => 400,
        ]);
    }
    public function testCalculateRegistrationCostWithInvalidDiscountCode()
    {
        // Create a client to send requests
        $client = static::createClient();

        // Mock the car repository
        $carRepository = $this->createMock(CarRepository::class);
        $carRepository->method('find')->willReturn($this->createMock(Car::class));

        // Mock the registration cost service to return a base cost without applying a discount
        $registrationCostService = $this->createMock(RegistrationCostService::class);
        $registrationCostService->method('calculateRegistrationCost')->willReturn(500);
        $registrationCostService->method('applyDiscount')->willReturn(500); // No discount

        // Send a GET request with carId and an invalid discountCode
        $client->request(Request::METHOD_GET, '/cars/calculate-registration-cost', [
            'carId' => 1,
            'discountCode' => 'INVALID_CODE' // Invalid code
        ]);

        // Assert that the status code is 200 (OK)
        $this->assertResponseIsSuccessful();

        // Assert that the response contains the correct values (no discount applied)
        $this->assertJsonContains([
            'carId' => 1,
            'registrationCost' => 500,
            'finalCost' => 500, // No discount applied
        ]);
    }
    // Test for GET method to fetch registration details for an existing car
    public function testGetRegistrationDetails()
    {
        // Create a client to send requests
        $client = static::createClient();

        // Mock a car entity (this would be a real entity in your tests)
        $car = new Car();
        $car->setId(1);
        $car->setYear(2020);
        $car->setEngineCapacity(1500);
        $car->setBrand('Toyota');
        $car->setModel('Corolla');
        $car->setColor('Blue');
        
        // Mock the RegistrationCostService to return base and final cost
        $registrationCostService = $this->createMock(RegistrationCostService::class);
        $registrationCostService->method('getRegistrationDetails')
            ->willReturn([
                'car' => $car,
                'baseCost' => 1000.0,
                'finalCost' => 800.0
            ]);

        // Send GET request to fetch registration details for the car with ID 1
        $client->request(Request::METHOD_GET, '/cars/registration-details/1');

        // Assert that the status code is 200 (OK)
        $this->assertResponseIsSuccessful();

        // Assert that the page contains the car brand and model
        $this->assertSelectorTextContains('.card-title', 'Toyota Corolla');
        
        // Assert that the registration cost (base cost) is displayed correctly
        $this->assertSelectorTextContains('.alert-info', '1000.0 RSD');
        
        // Assert that the final cost with discount is displayed correctly
        $this->assertSelectorTextContains('.alert-success', '800.0 RSD');
    }
      // Test for POST method to apply a discount code and fetch updated registration details
      public function testPostRegistrationDetailsWithDiscount()
      {
          // Create a client to send requests
          $client = static::createClient();
  
          // Mock a car entity (this would be a real entity in your tests)
          $car = new Car();
          $car->setId(1);
          $car->setYear(2020);
          $car->setEngineCapacity(1500);
          $car->setBrand('Toyota');
          $car->setModel('Corolla');
          $car->setColor('Blue');
  
          // Mock the RegistrationCostService to return base and final cost with discount
          $registrationCostService = $this->createMock(RegistrationCostService::class);
          $registrationCostService->method('getRegistrationDetails')
              ->willReturn([
                  'car' => $car,
                  'baseCost' => 1000.0,
                  'finalCost' => 800.0 // Discount applied
              ]);
  
          // Send POST request to apply a discount code
          $client->request(Request::METHOD_POST, '/cars/registration-details/1', [
              'discountCode' => 'DISCOUNT2020'
          ]);
  
          // Assert that the status code is 200 (OK)
          $this->assertResponseIsSuccessful();
  
          // Assert that the page contains the updated final cost with the discount applied
          $this->assertSelectorTextContains('.alert-success', '800.0 RSD');
      }



}
