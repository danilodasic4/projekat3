<?php
namespace App\Tests\Form;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class RegistrationFormTypeTest extends WebTestCase
{
    public function testSubmitValidData(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register'); 

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Register'); 

        $form = $crawler->selectButton('Register')->form();

        $form['registration_form[email]'] = 'user7@example.com';
        $form['registration_form[plainPassword][first]'] = 'password123';
        $form['registration_form[plainPassword][second]'] = 'password123';
        $form['registration_form[birthday]'] = '1990-01-01';
        $form['registration_form[gender]'] = 'Male'; 
        $form['registration_form[newsletter]'] = '1'; 
        $form['registration_form[agreeTerms]'] = '1'; 

        $imagePath = __DIR__ . '/../../public/uploads/profile_pictures/test_image.png';

        if (!file_exists($imagePath)) {
            throw new \Exception("Test image file not found at: $imagePath");
        }

        $profilePicture = new UploadedFile(
            $imagePath,        
            'test_image.png',  
            'image/png',       
            filesize($imagePath), 
            0,                 
            true
        );

        $form['registration_form[profile_picture]'] = $profilePicture;

        $client->submit($form);

        $this->assertResponseRedirects('/login'); 

    }
}