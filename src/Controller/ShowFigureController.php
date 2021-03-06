<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Figure;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShowFigureController extends AbstractController
{
    /**
     * @Route("/figure/{slug}", name="show_figure")
     */
    public function show(Request $request, Figure $figure, CommentRepository $commentRepository, EntityManagerInterface $entityManager): Response
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $commentRepository->getCommentPaginatorjoinedToUser($figure, $offset);
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setFigure($figure);
            $comment->setUser($this->getUser());

            $entityManager->persist($comment);
            $entityManager->flush();
        }

        return $this->render('show_figure.html.twig', [
            'controller_name' => 'ShowFigureController',
            'figure' => $figure,
            'comment_form' => $form->createView(),
            'comments' => $paginator,
            'previous' => $offset - CommentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + CommentRepository::PAGINATOR_PER_PAGE),
        ]);
    }
}
