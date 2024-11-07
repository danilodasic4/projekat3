<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        // Kreiramo novu User instancu
        $user = new User();

        // Kreiramo formu i povezujemo je sa User entitetom
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Obrada podataka sa forme
        $form->handleRequest($request);

        // Ako je forma poslana i validna, snimamo korisnika
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Šifrujemo lozinku pre nego što je snimimo u bazu
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Snimanje korisnika u bazu
            $entityManager->persist($user);
            $entityManager->flush();

            // Flash poruka o uspehu
            $this->addFlash('success', 'Registracija je uspešna!');

            // Preusmeravamo korisnika na homepage nakon uspešne registracije
            return $this->redirectToRoute('homepage');
        }

        // Prikazivanje forme u Twig šablonu
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
