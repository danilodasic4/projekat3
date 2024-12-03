<?php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CarControllerTest extends WebTestCase
{
    public function testCreateCar(): void
    {
        $client = static::createClient();

        // Pozivanje GET rute da bismo videli formu
        $client->request('GET', '/cars/create');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Create a New Car'); // Provera da li je stranica za kreiranje automobila

        // Simulacija POST zahteva za kreiranje automobila
        $data = [
            'car[brand]' => 'Test Brand',
            'car[model]' => 'Test Model',
            'car[year]' => 2020,
            'car[engineCapacity]' => 2000,
            'car[horsePower]' => 150,
            'car[color]' => 'Red',
            'car[registrationDate]' => '2024-12-01',
        ];

        $client->request('POST', '/cars/create', $data);
        $this->assertResponseRedirects('/cars'); // Provera da li je došlo do redirekcije nakon kreiranja automobila

        // Opcionalno, proverite da li je automobil sačuvan u bazi
        $car = $this->getDoctrine()->getRepository(Car::class)->findOneBy(['model' => 'Test Model']);
        $this->assertNotNull($car);
    }
    public function testShowCar(): void
{
    $client = static::createClient();

    // Pretpostavimo da imamo automobil sa ID = 1
    $carId = 1; 
    $client->request('GET', '/cars/' . $carId);

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Car Details'); // Provera naslova stranice
    $this->assertSelectorTextContains('p', 'Test Model'); // Provera da li se model automobila pojavljuje na stranici
}
public function testEditCar(): void
{
    $client = static::createClient();

    // Pretpostavimo da imamo automobil sa ID = 1
    $carId = 1;

    // Prvi poziv da bismo videli formu za editovanje
    $client->request('GET', '/cars/edit/' . $carId);
    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Edit Car');

    // Slanje POST zahteva sa novim podacima za automobil
    $data = [
        'car[brand]' => 'Updated Brand',
        'car[model]' => 'Updated Model',
        'car[year]' => 2021,
        'car[engineCapacity]' => 2200,
        'car[horsePower]' => 180,
        'car[color]' => 'Blue',
        'car[registrationDate]' => '2024-12-01',
    ];

    $client->request('POST', '/cars/edit/' . $carId, $data);
    $this->assertResponseRedirects('/cars'); // Provera redirekcije nakon uspešnog editovanja

    // Opcionalno, proverite da li su podaci ažurirani u bazi
    $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
    $this->assertEquals('Updated Model', $car->getModel());
}
public function testDeleteCar(): void
{
    $client = static::createClient();

    // Pretpostavimo da imamo automobil sa ID = 1
    $carId = 1;

    // Pozivanje DELETE rute za brisanje automobila
    $client->request('GET', '/cars/delete/' . $carId);
    $this->assertResponseRedirects('/cars'); // Provera redirekcije nakon brisanja

    // Opcionalno, proverite da li je automobil zaista obrisan iz baze
    $car = $this->getDoctrine()->getRepository(Car::class)->find($carId);
    $this->assertNull($car); // Ako je automobil obrisan, trebalo bi da bude null
}





}



