<?php
namespace App\Tests\Controller;

use App\Repository\CarRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Entity\Car;
use App\Service\RegistrationCostService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class CarControllerTest extends WebTestCase
{
    private $registrationCostServiceMock;
    private $carMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mock for the Car entity
        $this->carMock = $this->createMock(Car::class);
        // Mock methods for car data (for example, getYear() and getEngineCapacity())
        $this->carMock->method('getYear')->willReturn(2020);
        $this->carMock->method('getEngineCapacity')->willReturn(1200);

        // Create mock for RegistrationCostService
        $this->registrationCostServiceMock = $this->createMock(RegistrationCostService::class);
    }
    public function testGetUserCars()
    {
        $client = static::createClient();
        
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);

        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        $client->request('GET', '/api/users/' . $user->getId() . '/cars');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        // $this->assertJsonContains([
        //     'id' => 1,  
        // ]);
    }

    public function testCreateCar()
    {
        $client = static::createClient();
    
        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);
    
        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        // Data for creating a new car
        $carData = [
            'car_form' => [
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2020,
                'engineCapacity' => 1800,
                'horsePower' => 150,
                'color' => 'Blue',
                'registrationDate' => '2024-01-01',
                'save' => '',
            ]
        ];


        $client->request('POST', '/cars/create', $carData);
    
        // Verify that the response status is 201 Created
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND, 'Expected response status 201 (Created)');

        // Fetch the car from the database to verify it was created
        $car = $client->getContainer()->get(CarRepository::class)->findOneBy(['model' => 'Corolla']);

        $this->assertNotNull($car, 'Car was not created in the database.');
    
        // Verify that the car details match the input data
        $this->assertEquals('Toyota', $car->getBrand());
        $this->assertEquals(2020, $car->getYear());
        $this->assertEquals(1800, $car->getEngineCapacity());
        $this->assertEquals(150, $car->getHorsePower());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals('2024-01-01', $car->getRegistrationDate()->format('Y-m-d'));
    }
    
        public function testShowCar(): void
    {
        $client = static::createClient();

        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);

        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        // Fetch an existing car from the database for testing
        $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->findOneBy([]);

        // Ensure there is a car in the database
        $this->assertNotNull($car, 'No cars found in the database for testing.');

        // Make a GET request to the /cars/{id} route
        $client->request('GET', '/cars/' . $car->getId());

        // Verify the response status code
        $this->assertResponseStatusCodeSame(Response::HTTP_OK, 'Expected response status 200 (OK).');

        // Check if the car details are rendered correctly in the response content
        $this->assertStringContainsString($car->getBrand(), $client->getResponse()->getContent());
        $this->assertStringContainsString($car->getModel(), $client->getResponse()->getContent());
        $this->assertStringContainsString((string)$car->getYear(), $client->getResponse()->getContent());
        $this->assertStringContainsString((string)$car->getEngineCapacity(), $client->getResponse()->getContent());
        $this->assertStringContainsString((string)$car->getHorsePower(), $client->getResponse()->getContent());
        $this->assertStringContainsString($car->getColor(), $client->getResponse()->getContent());
        $this->assertStringContainsString($car->getRegistrationDate()->format('Y-m-d'), $client->getResponse()->getContent());
    }
    public function testEditCar(): void
    {
        $client = static::createClient();

        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);

        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        // Fetch an existing car from the database for testing
        $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->findOneBy([]);

        // Ensure there is a car in the database
        $this->assertNotNull($car, 'No cars found in the database for testing.');

        // Data for updating the car
        $updatedCarData = [
            'car_form' => [
                'brand' => 'Honda',
                'model' => 'Civic',
                'year' => 2021,
                'engineCapacity' => 2000,
                'horsePower' => 180,
                'color' => 'Red',
                'registrationDate' => '2025-01-01',
            ],
        ];

        // Perform the PUT request to update the car
        $client->request('PUT', '/cars/update/' . $car->getId(), $updatedCarData);

        // Assert redirection status
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND, 'Expected response status 302 (Found).');

        // Assert redirection to the correct route
        $this->assertTrue($client->getResponse()->isRedirect('/cars'), 'Expected redirect to /cars.');

        // Follow the redirection and verify the car was updated
        $client->followRedirect();
        $updatedCar = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find($car->getId());
        $this->assertEquals('Honda', $updatedCar->getBrand());
        $this->assertEquals('Civic', $updatedCar->getModel());
        $this->assertEquals(2021, $updatedCar->getYear());
        $this->assertEquals(2000, $updatedCar->getEngineCapacity());
        $this->assertEquals(180, $updatedCar->getHorsePower());
        $this->assertEquals('Red', $updatedCar->getColor());
        $this->assertEquals('2025-01-01', $updatedCar->getRegistrationDate()->format('Y-m-d'));
    }

    public function testDeleteCar()
    {
        $client = static::createClient();

        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);

        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        // Choose a car ID to delete (e.g., ID = 4)
        $carId = 4;

        // Fetch the car from the database to verify it exists
        $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find($carId);
        $this->assertNotNull($car, 'The car should exist before deleting.');

        // Perform the DELETE request
        $client->request('DELETE', '/cars/delete/' . $carId);

        // Ensure the response status is a redirect (302 Found)
        $this->assertResponseRedirects('/cars', Response::HTTP_FOUND);

        // Fetch the car again from the database to check if it was deleted
        $car = $client->getContainer()->get('doctrine')->getRepository(Car::class)->find($carId);

        // Assert that the car is not in the database anymore (i.e., it is null)
        $this->assertNull($car, 'The car should be deleted from the database.');
    }


    public function testExpiringRegistration()
    {
        $client = static::createClient();
    
        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);
    
        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

        // Data for a car with expiring registration
        $carData = [
            'car_form' => [
                'brand' => 'Toyota',
                'model' => 'Corolla',
                'year' => 2020,
                'engineCapacity' => 1800,
                'horsePower' => 150,
                'color' => 'Blue',
                'registrationDate' => '2024-01-01', // Make sure this registration date is within the expiring range
                'save' => '',
            ]
        ];

        // Send a POST request to create the car
        $client->request('POST', '/cars/create', $carData);

        // Verify that the response status is HTTP_FOUND (redirect)
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Fetch the car from the database to verify it was created
        $car = $client->getContainer()->get(CarRepository::class)->findOneBy(['model' => 'Corolla']);

        $this->assertNotNull($car, 'Car was not created in the database.');
    
        // Verify the car details
        $this->assertEquals('Toyota', $car->getBrand());
        $this->assertEquals('Corolla', $car->getModel());
        $this->assertEquals(2020, $car->getYear());
        $this->assertEquals(1800, $car->getEngineCapacity());
        $this->assertEquals(150, $car->getHorsePower());
        $this->assertEquals('Blue', $car->getColor());
        $this->assertEquals('2024-01-01', $car->getRegistrationDate()->format('Y-m-d'));

        // Now test the GET route for cars with expiring registration
        $client->request('GET', '/cars/expiring-registration');
    
        // Check if the response is OK (200 OK)
        $this->assertResponseIsSuccessful();
    
        // Verify that the car with expiring registration is shown
        $this->assertSelectorTextContains('.car-list', 'Toyota');
        $this->assertSelectorTextContains('.car-list', 'Corolla');
        $this->assertSelectorTextContains('.car-list', '2020');
        $this->assertSelectorTextContains('.car-list', 'Blue');
        $this->assertSelectorTextContains('.car-list', '2024-01-01');
    }
    public function testCalculateRegistrationCost()
    {
        $client = static::createClient();
    
        // Log in a user with the role ROLE_USER
        $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
        $client->loginUser($user);
    
        // Verify that the user has the role ROLE_USER
        $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');
    
        // Data for a car with expiring registration
        $carData = [
            'car_form' => [
                'brand' => 'Mercedes',
                'model' => 'G class',
                'year' => 2024,
                'engineCapacity' => 3500,
                'horsePower' => 350,
                'color' => 'Blue',
                'registrationDate' => '2025-01-01', // Ensure the date is valid and in range
                'save' => '',
            ]
        ];
    
        // Send a POST request to create the car
        $client->request('POST', '/cars/create', $carData);

        // Verify that the response status is HTTP_FOUND (redirect)
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Fetch the car from the database to verify it was created
        $car = $client->getContainer()->get(CarRepository::class)->findOneBy(['model' => 'G class']);
    
        // Get parameters from the container
        $registrationBaseCost = $client->getContainer()->getParameter('registration_base_cost');
        $discountCode = $client->getContainer()->getParameter('discount_code');
    
        // Send a GET request to calculate registration cost
        $client->request('GET', '/cars/calculate-registration-cost', [
            'carId' => $car->getId(),
            'discountCode' => $discountCode,
        ]);
    
        // Verify that the response status is 200 OK
        $this->assertResponseStatusCodeSame(200, 'Expected response status 200 (OK)');
    
        // Decode JSON response
        $response = json_decode($client->getResponse()->getContent(), true);
    
        // Verify response data structure
        $this->assertArrayHasKey('carId', $response);
        $this->assertArrayHasKey('registrationCost', $response);
        $this->assertArrayHasKey('finalCost', $response);
    
        // Verify response data values
        $this->assertEquals($car->getId(), $response['carId'], 'Car ID does not match.');
        
        // Calculate expected costs
        $expectedBaseCost = $registrationBaseCost 
            + (($car->getYear() - 1960) * 200) 
            + (($car->getEngineCapacity() - 900) * 200);
        $this->assertEquals($expectedBaseCost, $response['registrationCost'], 'Base cost does not match.');
    
        $expectedFinalCost = $discountCode === 'discount20' ? $expectedBaseCost * 0.8 : $expectedBaseCost;
        $this->assertEquals($expectedFinalCost, $response['finalCost'], 'Final cost does not match after applying discount.');
    }
    



    public function testRegistrationDetails()
{
    // Create a client to simulate a request
    $client = static::createClient();

    // Log in a user with the role ROLE_USER
    $user = $client->getContainer()->get('doctrine')->getRepository(User::class)->findOneBy(['email' => 'user1@example.com']);
    $client->loginUser($user);

    // Verify that the user has the role ROLE_USER
    $this->assertContains('ROLE_USER', $user->getRoles(), 'User must have ROLE_USER.');

    // Simulate a GET request to the registration details page for car with ID 6
    $crawler = $client->request('GET', 'cars/registration-details/6');  // Adjust the URL as per your route configuration

    // Check that the response status code is 200 (OK)
    $this->assertResponseIsSuccessful();

    // Verify that the page contains the expected title or header text
    $this->assertSelectorTextContains('h1', 'Registration Details');  // Ensure the title is correct

    // Verify that car details are rendered correctly
    $this->assertSelectorTextContains('.card-title', 'Stokes PLC sint');  // Ensure the car name is displayed

    // Assert the Year
    $this->assertSelectorTextContains('.list-group-item:nth-child(1)', 'Year: 2004');

    // Assert the Engine Capacity
    $this->assertSelectorTextContains('.list-group-item:nth-child(2)', 'Engine Capacity: 3551cc');

    // Assert the Color (if you need it)
    $this->assertSelectorTextContains('.list-group-item:nth-child(3)', 'Color: GreenYellow');
    // Check that the registration cost is rendered correctly
    $this->assertSelectorTextContains('.alert-info', '549000 RSD');  // Ensure the registration cost is shown

    // Check that the discount form is present
    $this->assertCount(1, $crawler->filter('form'));  // Ensure there is exactly one form on the page

    // Simulate submitting a discount code in the form (optional)
    $form = $crawler->selectButton('Apply Discount')->form();
    $form['discountCode'] = 'discount20';  // Enter a discount code for testing
    $crawler = $client->submit($form);

    // Verify that the page has been updated with the discounted price (if applicable)
    $this->assertSelectorTextContains('.alert-success', 'Final Cost with Discount:');  // Ensure discount is applied and final cost is displayed
}

    
}