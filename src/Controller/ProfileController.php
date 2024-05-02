<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/profile')]
class ProfileController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_profile')]
    public function index(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('profile/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_profile_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user): Response
    {
        // Vérifier si l'utilisateur actuellement authentifié est celui dont le profil est modifié
        if ($this->getUser()->getId() !== $user->getId()) {
            throw new AccessDeniedException('Vous ne pouvez pas modifier ce profil.');
        }

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();

            if ($image !== null) {
                $this->handleImageUpload($image, $user);
            }

            $this->entityManager->flush();

            return $this->redirectToRoute('app_profile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('profile/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    private function handleImageUpload($image, $user)
    {
        if ($user->getImage() !== null) {
            if (file_exists($this->getParameter('user_image_directory') . $user->getImage())) {
                unlink($this->getParameter('user_image_directory') . $user->getImage());
            }
        }

        $imgName = uniqid() . '.' . $image->guessExtension();
        $user->setImage($imgName);

        try {
            $image->move($this->getParameter('user_image_directory'), $imgName);
        } catch (FileException $e) {
            // Handle file exception
        }
    }
}
