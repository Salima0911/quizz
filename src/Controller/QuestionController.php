<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Form\QuestionType;
use App\Repository\ThemeRepository;
use App\Repository\QuestionRepository;
use App\Repository\ReponseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/question')]
class QuestionController extends AbstractController
{
    #[Route('/', name: 'app_question_index', methods: ['GET'])]
    public function index(QuestionRepository $questionRepository, Request $request): Response
    {
        return $this->render('question/index.html.twig', [
            'questions' => $questionRepository->findAll(),
        ]);
    }

    #[Route('/filter/', name: 'app_question_detail', methods: ['GET'])]
    public function details(QuestionRepository $questionRepository, Request $request): Response
    {
        if($request->query->has('id')) {
            $themeId = $request->query->get('id');
            $questionbyTheme = $questionRepository->findBy(['idTheme' => $themeId]);
        }
        return $this->render('question/detail.html.twig', [
            'questionFiltered' =>$questionbyTheme,
        ]);
    }

    #[Route('/quiz/', name: 'app_questionUser_detail', methods: ['GET', 'POST'])]
    public function detailsUser(QuestionRepository $questionRepository, ReponseRepository $reponseRepository ,Request $request): Response
    {
        // Récupérer toutes les réponses
        $reponses = $reponseRepository->findAll();
        
        // Initialiser la variable questionFiltered
        $questionbyTheme = [];
    
        // Vérifier si un ID de thème est passé dans la requête
        if($request->query->has('id')) {
            $themeId = $request->query->get('id');
            // Récupérer les questions pour le thème spécifié
            $questionbyTheme = $questionRepository->findBy(['idTheme' => $themeId]);
        }
    
        // Passer les données à la vue
        return $this->render('question/questionUser.html.twig', [
            'questionFiltered' => $questionbyTheme,
            'reponses' => $reponses,
        ]);
    }
        
    // Ajouter la méthode submit pour gérer la soumission du quiz
    #[Route('/submit', name: 'app_questionUser_submit', methods: ['POST'])]
    public function submit(Request $request, ReponseRepository $reponseRepository): Response
    {
        $requestData = json_decode($request->getContent(), true);
        $responseId = $requestData['responseId'];
    
        // Récupérer la réponse soumise depuis la base de données
        $reponse = $reponseRepository->find($responseId);
    
        // Vérifier si la réponse est correcte
        $isCorrect = $reponse->isCorrect();
    
        // Renvoyer la réponse en JSON
        return $this->json(['isCorrect' => $isCorrect]);
    }
    
        
    #[Route('/filter/{id}', name: 'app_question_filter', methods: ['GET'])]
    public function filter(int $id, QuestionRepository $questionRepository, ThemeRepository $themeRepository): Response
    {
        return $this->render('question/index.html.twig', [

            'questions' => $questionRepository->findBy(['theme' => $id]),

            'themes' => $themeRepository->findAll()
        ]);
    }
    #[Route('/new', name: 'app_question_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($question);
            $entityManager->flush();

            return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_show', methods: ['GET'])]
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', [
            'question' => $question,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_question_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_question_delete', methods: ['POST'])]
    public function delete(Request $request, Question $question, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($question);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_question_index', [], Response::HTTP_SEE_OTHER);
    }
}
