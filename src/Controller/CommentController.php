<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CommentRepository;
use App\Entity\Comment;

use App\Form\DeleteCommentFormType;
use App\Form\CommentType;
use App\Repository\PlaceRepository;

#[Route('/comment')]
class CommentController extends AbstractController
{
    #[Route('s/', name: 'comment_list', methods: ['GET'])]
    public function index(CommentRepository $commentRepository): Response
    {
        return $this->render('comment/index.html.twig', [
            'places' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/create', name: 'comment_create')]
    public function create(
        Request $request,
        CommentRepository $commentRepository,
        PlaceRepository $placeRepository): Response
    {

        $place = new Comment();
        $form = $this->createForm(CommentType::class, $place);
        $form->handleRequest($request);

        $lugar_id = $request->request->get('place_id');
        $lugar = $placeRepository->find($lugar_id);

        if($form->isSubmitted() && $form->isValid()) {

            $place->setPlace($lugar);
            $place->setDate(new \DateTime());
            $place->setUser($this->getUser());

            $commentRepository->add($place, true);
            $this->addFlash('success', 'Comentario guardado con éxito.');

        } else {
            $this->addFlash('error', 'No se pudo añadir el comentario.');
        }

        return $this->redirectToRoute('place_show', ['id' => $place->getPlace()->getId()]);
    }


    #[Route('/edit/{id}', name: 'comment_edit')]
    public function edit (
        Comment $place,
        Request $request,
        CommentRepository $commentRepository): Response
    {
        $form = $this->createForm(CommentType::class, $place);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $commentRepository->add($place, true);
            $this->addFlash('success', 'Comentario actualizado con éxito.');

            return $this->redirectToRoute('comment_show', ['id' => $place->getId()]);
        }

        return $this->render('comment/edit.html.twig', [
            'formulario' => $form->createView(),
            'place' => $place
        ]);
    }


    #[Route('/delete/{id}', name: 'comment_delete')]
    public function delete(
        Comment $place,
        Request $request,
        CommentRepository $commentRepository
        ): Response
    {
        $form = $this->createForm(DeleteCommentFormType::class, $place);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $commentRepository->remove($place, true);
            $this->addFlash('success', "S'ha eliminat el comentari correctament.");

            return $this->redirectToRoute('comment_list');
        }

        return $this->render('comment/delete.html.twig', [
            'formulario' => $form->createView(),
            'place' => $place
        ]);
    }


    #[Route('/show/{id}', name: 'comment_show')]
    public function show(
        Comment $place): Response
    {
        return $this->render('comment/show.html.twig', [
            'place' => $place
        ]);
    }

}
