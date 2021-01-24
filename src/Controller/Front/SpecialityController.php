<?php

namespace App\Controller\Front;

use App\Repository\SpecialityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SpecialityController extends AbstractController
{
    /**
     * @Route("/", name="specialities", methods={"GET"})
     */
    public function index(SpecialityRepository $specialityRepository): Response
    {
        return $this->render('speciality/front.html.twig', [
            'specialities' => $specialityRepository->findAll(),
        ]);
    }
}
