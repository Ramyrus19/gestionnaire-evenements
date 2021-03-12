<?php


namespace App\Controller;


use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="sortie_index", methods={"GET"})
     */
    public function index(SortieRepository $sortieRepository): Response
    {
/*         $sorties = $sortieRepository->findAll();
foreach ($sorties as $sortie){
    dump($sortie->getParticipants()->getValues());
    
}die(); */
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
            
        ]);
    }

    //TODO: function changement etat (doc DiagEtatSortie.pdf)
}