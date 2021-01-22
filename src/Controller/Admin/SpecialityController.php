<?php

namespace App\Controller\Admin;

use App\Entity\Speciality;
use App\Form\SpecialityType;
use App\Repository\SpecialityRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(host="admin.%domain%")
 */
class SpecialityController extends AbstractController
{
    /**
     * @Route("/", name="speciality_index", methods={"GET"})
     */
    public function index(SpecialityRepository $specialityRepository): Response
    {
        return $this->render('speciality/index.html.twig', [
            'specialities' => $specialityRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="speciality_new", methods={"GET","POST"})
     */
    public function new(Request $request, FileUploader $fileUploader): Response
    {
        $speciality = new Speciality();
        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                $newFilename = $fileUploader->upload($mediaFile);
                $speciality->setMedia($newFilename);
            }

            $entityManager->persist($speciality);
            $entityManager->flush();

            return $this->redirectToRoute('speciality_index');
        }

        return $this->render('speciality/new.html.twig', [
            'speciality' => $speciality,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="speciality_show", methods={"GET"})
     */
    public function show(Speciality $speciality): Response
    {
        return $this->render('speciality/show.html.twig', [
            'speciality' => $speciality,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="speciality_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Speciality $speciality, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(SpecialityType::class, $speciality);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaFile = $form->get('media')->getData();
            if ($mediaFile) {
                $newFilename = $fileUploader->upload($mediaFile);
                $speciality->setMedia($newFilename);
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('speciality_index');
        }

        return $this->render('speciality/edit.html.twig', [
            'speciality' => $speciality,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="speciality_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Speciality $speciality): Response
    {
        if ($this->isCsrfTokenValid('delete'.$speciality->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($speciality);
            $entityManager->flush();
        }

        //gérer la suppression du media

        return $this->redirectToRoute('speciality_index');
    }
}
