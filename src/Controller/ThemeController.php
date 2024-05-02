<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;

use App\Repository\ThemeRepository;
use App\Repository\QuestionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/theme')]
class ThemeController extends AbstractController
{
    #[Route('/', name: 'app_theme_index', methods: ['GET'])]
    public function index(ThemeRepository $themeRepository, QuestionRepository $questionRepository): Response
    {
        return $this->render('theme/index.html.twig', [
            'themes' => $themeRepository->findAll(),
            'questions' => $questionRepository->findAll()
        ]);
    }

    #[Route('/quiz', name: 'app_theme_index_quiz', methods: ['GET'])]
    public function quizMain(ThemeRepository $themeRepository, QuestionRepository $questionRepository): Response
    {
        return $this->render('theme/main.html.twig', [
            'themes' => $themeRepository->findAll(),
            'questions' => $questionRepository->findAll()
        ]);
    }


    #[Route('/filter/{id}', name: 'app_theme_filter', methods: ['GET'])]
    public function filter(int $id, ThemeRepository $themeRepository, QuestionRepository $questionRepository): Response
    {
        return $this->render('theme/index.html.twig', [

            'themes' => $themeRepository->findBy(['question' => $id]),

            'questions' => $questionRepository->findAll()
        ]);
    }

    #[Route('/new', name: 'app_theme_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, QuestionRepository $questionRepository): Response
    {

       



        $theme = new Theme();
       


        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {


            $image = $form->get('img')->getData();
            if ($image != null) {
                $imageName = uniqid() . '.' . $image->guessExtension();
                $theme->setiMG($imageName);;

                $image->move($this->getParameter('theme_img_directory'), $imageName);
            }


            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('app_theme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('theme/new.html.twig', [
            'theme' => $theme,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_theme_show', methods: ['GET'])]
    public function show(Theme $theme): Response
    {
        return $this->render('theme/show.html.twig', [
            'theme' => $theme,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_theme_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Theme $theme, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ThemeType::class, $theme);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $image = $form->get('img')->getData();
            // je verifie qu'une nouvelle image a ete envoyé avec le formulaire 
            if ($image != null) {
                // je verifie l'existance d'une encienne image au produit 
                // si c'est le cas je supprime l'ancienne image 
                if (file_exists($this->getParameter('theme_img_directory') . $theme->getImG())) {
                    unlink($this->getParameter('theme_img_directory') . $theme->getImG());
                }

                // puis je telechager la nouvelle image et change le nom de l'image en base de donnees

                $imgName = uniqid() . '.' . $image->guessExtension();
                $theme->setImG($imgName);
                $image->move($this->getParameter('theme_img_directory'), $imgName);
            }





            $entityManager->persist($theme);
            $entityManager->flush();

            return $this->redirectToRoute('app_theme_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('theme/edit.html.twig', [
            'theme' => $theme,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_theme_delete', methods: ['POST'])]
    public function delete(Request $request, Theme $theme, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $theme->getId(), $request->getPayload()->get('_token'))) {

            if (file_exists('uploads/image/' . $theme->getImG())) {
                unlink('uploads/image/' . $theme->getImG());
            }
            $entityManager->remove($theme);
            $entityManager->flush();
        }
        

        return $this->redirectToRoute('app_theme_index', [], Response::HTTP_SEE_OTHER);
    }
}