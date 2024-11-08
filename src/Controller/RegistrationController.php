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
        // We are creating new instance of User class
        $user = new User();

        // We are creating form and connecting it with User entity 
        $form = $this->createForm(RegistrationFormType::class, $user);

        // Form data processing
        $form->handleRequest($request);

        // If is form sent and validated, we save the user
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // We encrypt the password before saving it to the database
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            // Saving the user
            $entityManager->persist($user);
            $entityManager->flush();

            // Flash message about success
            $this->addFlash('success', 'Registracija je uspešna!');

            // Redirecting homepage after successful registration
            return $this->redirectToRoute('homepage');
        }

        // Displaying the form in a Twig template
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
