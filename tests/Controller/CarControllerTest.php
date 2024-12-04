<?php
namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CarControllerTest extends WebTestCase
{
    //GET USER'S CARS
    public function testGetUserCarsWithCars(): void
    {
        $client = static::createClient();

        $user = $this->createMock(User::class);
        $userId = 1;  // Assuming user with ID 1 has cars in the database

        $client->request('GET', '/api/users/' . $userId . '/cars');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
        $this->assertGreaterThan(0, count($data), 'Expected a list of cars.');
    }

    public function testGetUserCarsNoCars(): void
    {
        // Create a client to make requests
        $client = static::createClient();

        // Make a GET request to fetch cars for a user with no cars (for example, user ID 9999)
        $userId = 9999; // Assuming this user does not exist or has no cars

        $client->request('GET', '/api/users/' . $userId . '/cars');

        // Assert that the response returns a 404 error when no cars are found
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        // Check if the response contains the expected error message
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(['error' => 'No cars found for this user'], $data);
    }
    //SHOW CAR 
     // Test for showing car details
     public function testShowCar()
     {
         $client = static::createClient();
         
         // Create a mock car object (you can also use a fixture or database object)
         $car = new Car();
         $car->setId(1);
         $car->setBrand('Toyota');
         $car->setModel('Corolla');
         $car->setYear(2020);
         $car->setEngineCapacity(1800);
         $car->setHorsePower(140);
         $car->setColor('Red');
         $car->setRegistrationDate(new \DateTime('2020-01-01'));
 
         // Persist the car to the database or mock the repository method
         $client->getContainer()->get('doctrine')->getManager()->persist($car);
         $client->getContainer()->get('doctrine')->getManager()->flush();
 
         // Send a GET request to show the car details
         $client->request('GET', '/cars/1');
 
         // Assert that the response is 200 (OK)
         $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
 
         // Assert that the page contains car details (check the car's brand and model)
         $this->assertSelectorTextContains('h1', 'Car Details');
         $this->assertSelectorTextContains('.card-title', 'Toyota Corolla');
         $this->assertSelectorTextContains('.list-group-item', 'Engine Capacity: 1800 cc');
         $this->assertSelectorTextContains('.list-group-item', 'Horse Power: 140 HP');
     }
 
     // Test for car not found (404 response)
     public function testShowCarNotFound()
     {
         $client = static::createClient();
 
         // Send a GET request to show a non-existing car
         $client->request('GET', '/cars/999'); // Assuming this car doesn't exist
 
         // Assert that the response is 404 (Not Found)
         $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
     }
     //CREATE NEW CAR

    // Test for GET request to render the car creation form
     public function testNewCarForm()
     {
         $client = static::createClient();
         
         // Send a GET request to render the car creation form
         $client->request('GET', '/cars/create');
 
         // Assert that the response is 200 (OK)
         $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
 
         // Assert that the page contains the title "Create a New Car"
         $this->assertSelectorTextContains('h1', 'Create a New Car');
         $this->assertSelectorExists('form');  // Check if form is rendered
     }
 
     // Test for POST request to create a new car with valid data
     public function testCreateNewCar()
     {
         $client = static::createClient();
         $client->enableProfiler();  // Enable profiler to inspect response content
 
         // Send a POST request with form data
         $client->request('POST', '/cars/create', [
             'car_form[brand]' => 'Honda',
             'car_form[model]' => 'Civic',
             'car_form[year]' => 2022,
             'car_form[engineCapacity]' => 2000,
             'car_form[horsePower]' => 160,
             'car_form[color]' => 'Blue',
             'car_form[registrationDate]' => '2022-05-01',
         ]);
 
         $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
 
         $this->assertRedirectToRoute('app_car_index');
 
         $carRepository = $client->getContainer()->get('doctrine')->getRepository(Car::class);
         $car = $carRepository->findOneBy(['model' => 'Civic']);
         $this->assertNotNull($car);
         $this->assertEquals('Honda', $car->getBrand());
     }
 
     public function testCreateNewCarInvalidData()
     {
         $client = static::createClient();
 
         $client->request('POST', '/cars/create', [
             'car_form[model]' => 'Civic',
             'car_form[year]' => 2022,
             'car_form[engineCapacity]' => 2000,
             'car_form[horsePower]' => 160,
             'car_form[color]' => 'Blue',
             'car_form[registrationDate]' => '2022-05-01',
         ]);
 
         $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
 
         $this->assertSelectorTextContains('.form-error', 'This value should not be null');
     }
     //EDIT


     // Test for GET request to render the car edit form
    public function testEditCarForm()
    {
        $client = static::createClient();
        
        // Create a car to edit
        $car = new Car();
        $car->setBrand('Toyota');
        $car->setModel('Camry');
        $car->setYear(2020);
        $car->setEngineCapacity(2500);
        $car->setHorsePower(200);
        $car->setColor('Black');
        $car->setRegistrationDate(new \DateTime('2020-01-01'));

        // Persist the car to the database
        $client->getContainer()->get('doctrine')->getManager()->persist($car);
        $client->getContainer()->get('doctrine')->getManager()->flush();

        // Send a GET request to the edit route
        $client->request('GET', '/cars/update/'.$car->getId());

        // Assert that the response is 200 (OK)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Assert that the form contains pre-filled values (car's brand and model)
        $this->assertSelectorTextContains('h1', 'Edit Car');
        $this->assertSelectorTextContains('input[name="car_form[brand]"]', 'Toyota');
        $this->assertSelectorTextContains('input[name="car_form[model]"]', 'Camry');
    }

    // Test for PUT request to update the car's details
    public function testUpdateCar()
    {
        $client = static::createClient();

        // Create a car to update
        $car = new Car();
        $car->setBrand('Honda');
        $car->setModel('Civic');
        $car->setYear(2021);
        $car->setEngineCapacity(1800);
        $car->setHorsePower(150);
        $car->setColor('Red');
        $car->setRegistrationDate(new \DateTime('2021-01-01'));

        // Persist the car to the database
        $client->getContainer()->get('doctrine')->getManager()->persist($car);
        $client->getContainer()->get('doctrine')->getManager()->flush();

        // Send a PUT request to update the car's data
        $client->request('PUT', '/cars/update/'.$car->getId(), [
            'car_form[brand]' => 'Nissan',
            'car_form[model]' => 'Altima',
            'car_form[year]' => 2022,
            'car_form[engineCapacity]' => 2000,
            'car_form[horsePower]' => 170,
            'car_form[color]' => 'Blue',
            'car_form[registrationDate]' => '2022-01-01',
        ]);

        // Assert that the response is a redirect (car updated successfully)
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        // Optionally, check the database for the updated car
        $carRepository = $client->getContainer()->get('doctrine')->getRepository(Car::class);
        $updatedCar = $carRepository->find($car->getId());
        $this->assertEquals('Nissan', $updatedCar->getBrand());
        $this->assertEquals('Altima', $updatedCar->getModel());
    }
    //DELETE
    // tests/Controller/CarControllerTest.php
    // Test for DELETE request to delete an existing car
    public function testDeleteCar()
    {
        $client = static::createClient();

        // Create a car to delete
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('3 Series');
        $car->setYear(2021);
        $car->setEngineCapacity(3000);
        $car->setHorsePower(250);
        $car->setColor('Grey');
        $car->setRegistrationDate(new \DateTime('2021-06-01'));

        // Persist the car to the database
        $client->getContainer()->get('doctrine')->getManager()->persist($car);
        $client->getContainer()->get('doctrine')->getManager()->flush();

        // Send a DELETE request to delete the car
        $client->request('DELETE', '/cars/delete/'.$car->getId());

        // Assert that the response is a redirect (car deleted successfully)
        $this->assertEquals(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        // Check that the car no longer exists in the database
        $carRepository = $client->getContainer()->get('doctrine')->getRepository(Car::class);
        $deletedCar = $carRepository->find($car->getId());
        $this->assertNull($deletedCar);
    }

    // Test for DELETE request when the car doesn't exist
    public function testDeleteCarNotFound()
    {
        $client = static::createClient();

        // Send a DELETE request to delete a non-existing car
        $client->request('DELETE', '/cars/delete/999'); // Assuming this car ID doesn't exist

        // Assert that the response is a 404 (car not found)
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }
    //EXPIRING REGISTRATION
    public function testExpiringRegistrationPageWithCars()
    {
        $client = static::createClient();

        // Create a car with an expiring registration (this depends on the current date logic)
        $car = new Car();
        $car->setBrand('BMW');
        $car->setModel('X5');
        $car->setYear(2021);
        $car->setEngineCapacity(3000);
        $car->setHorsePower(250);
        $car->setColor('Black');
        $car->setRegistrationDate(new \DateTimeImmutable('2023-12-25')); // set to expiring soon

        // Persist the car to the database
        $client->getContainer()->get('doctrine')->getManager()->persist($car);
        $client->getContainer()->get('doctrine')->getManager()->flush();

        // Send GET request to the expiring registration page
        $client->request('GET', '/cars/expiring-registration');

        // Assert that the response is OK (200)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Assert that the car appears in the table
        $this->assertSelectorTextContains('td', 'BMW');
        $this->assertSelectorTextContains('td', 'X5');
    }

    // Test when no cars are found with expiring registration
    public function testExpiringRegistrationPageNoCars()
    {
        $client = static::createClient();

        // Send GET request to the expiring registration page
        $client->request('GET', '/cars/expiring-registration');

        // Assert that the response is OK (200)
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Assert that the "No cars found" message appears
        $this->assertSelectorTextContains('.alert-warning', 'No cars found with expiring registration this month.');
    }
    //REGISTRATION DETAILS
// Test for valid car registration details (GET)
    public function testRegistrationDetailsPageWithCar()
    {
    $client = static::createClient();

    // Create a car and set its properties (ensure the car exists in the database)
    $car = new Car();
    $car->setBrand('Toyota');
    $car->setModel('Corolla');
    $car->setYear(2020);
    $car->setEngineCapacity(1800);
    $car->setColor('Red');
    $car->setRegistrationDate(new \DateTimeImmutable('2024-01-01')); // registration date
    // Persist the car to the database
    $client->getContainer()->get('doctrine')->getManager()->persist($car);
    $client->getContainer()->get('doctrine')->getManager()->flush();

    // Send GET request to the registration details page
    $client->request('GET', '/cars/registration-details/' . $car->getId());

    // Assert that the response is OK (200)
    $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

    // Assert that the car details appear in the response
    $this->assertSelectorTextContains('.card-title', 'Toyota Corolla');
    $this->assertSelectorTextContains('.list-group-item', 'Year: 2020');
    $this->assertSelectorTextContains('.list-group-item', 'Engine Capacity: 1800cc');
    $this->assertSelectorTextContains('.list-group-item', 'Color: Red');
    }

// Test with applying discount code (POST request)
    public function testRegistrationDetailsPageWithDiscount()
    {
    $client = static::createClient();

    // Create a car and set its properties
    $car = new Car();
    $car->setBrand('Honda');
    $car->setModel('Civic');
    $car->setYear(2022);
    $car->setEngineCapacity(2000);
    $car->setColor('Blue');
    $car->setRegistrationDate(new \DateTimeImmutable('2024-01-01'));
    // Persist the car to the database
    $client->getContainer()->get('doctrine')->getManager()->persist($car);
    $client->getContainer()->get('doctrine')->getManager()->flush();

    // Send POST request with a discount code
    $client->request('POST', '/cars/registration-details/' . $car->getId(), [
        'discountCode' => 'DISCOUNT10',
    ]);

    // Assert that the response is OK (200)
    $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

    // Check that the final cost with discount is displayed
    $this->assertSelectorTextContains('.alert-success', 'Final Cost with Discount');
    }

// Test when car not found (404)
    public function testRegistrationDetailsPageCarNotFound()
    {
        $client = static::createClient();

        // Send GET request with a non-existing car ID
        $client->request('GET', '/cars/registration-details/9999'); // ID 9999 does not exist

        // Assert that the response status is 404
        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());

        // Assert that the "Car not found" message is shown
        $this->assertSelectorTextContains('h1', '404 Not Found');
    }
}






