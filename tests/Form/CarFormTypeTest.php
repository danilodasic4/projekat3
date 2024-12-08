<?php

namespace App\Tests\Form;

use App\Entity\Car;
use App\Entity\User;
use App\Form\CarFormType;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;

class CarFormTypeTest extends TypeTestCase
{
    /** @var EntityManagerInterface|MockObject */
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock EntityManager
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public function testSubmitValidData(): void
    {
        // Simulate valid form data
        $user = new User();
        $user->setEmail('user5@example.com');
        
        $formData = [
            'brand' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
            'engineCapacity' => 1800,  // Integer value
            'horsePower' => 150,
            'color' => 'Blue',
            'registrationDate' => '2023-01-01',
           // 'user' => $user, // Associate with the user entity
        ];

        $form = $this->factory->create(CarFormType::class);

        // Submit the form data
        $form->submit($formData);

        // Validate that the form is valid
        $this->assertTrue($form->isValid());

        // Check if the form data was correctly processed
        $car = $form->getData();
        $this->assertInstanceOf(Car::class, $car); // Ensure it's a Car object
        $this->assertEquals('Toyota', $car->getBrand());
        $this->assertEquals(2020, $car->getYear());
        $this->assertEquals(1800, $car->getEngineCapacity());
    }

    
}

