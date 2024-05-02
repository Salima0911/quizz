<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,  UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {


            $image = $form->get('image')->getData();
            if ($image != null) {
                $imageName = uniqid() . '.' . $image->guessExtension();
                $user->setImage($imageName);;

                $image->move($this->getParameter('user_image_directory'), $imageName);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('password')->getData();
                // on utilise le setter de l'user pour ajouter le mot de pass hashé a l'user avant de l'evoyer en bdd

                $hashedPassword = $hasher->hashPassword($user, $plainPassword);

                // on hash le password grace  a la classe UserPasswordHasherInterface

                $user->setPassword($hashedPassword);
                // 2éme methode de hasher  le password
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, $id): Response
    {
        // $form = $this->createForm(UserType::class, $user);
        $form = $this->createFormBuilder($user)
            ->add('name')
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'expanded' => true,
                'multiple' => true,
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Invité' => 'ROLE_GUEST',
                    'Modérateur' => 'ROLE_MODERATOR',
                ]
            ])->add('image', FileType::class, [
                'required' => false,
                'data_class' => null,
                // mapped false permet d'extraire un input du reste du formulaire, ça évite qu'un input
                // soit lié à l'objet envoyé dans le formulaire
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                    ]),
                ]
            ])

            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('image')->getData();
            // je verifie qu'une nouvelle image a ete envoyé avec le formulaire 
            if ($image != null) {
                // je verifie l'existance d'une encienne image au produit 
                // si c'est le cas je supprime l'ancienne image 
                if (file_exists($this->getParameter('user_image_directory') . $user->getImage())) {
                    unlink($this->getParameter('user_image_directory') . $user->getImage());
                }

                // puis je telechager la nouvelle image et change le nom de l'image en base de donnees

                $imgName = uniqid() . '.' . $image->guessExtension();
                $user->setImage($imgName);
                $image->move($this->getParameter('user_image_directory'), $imgName);
            }

            if ($form->isSubmitted() && $form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->get('_token'))) {

            if (file_exists('uploads/image/' . $user->getImage())) {
                unlink('uploads/image/' . $user->getImage());
            }
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
