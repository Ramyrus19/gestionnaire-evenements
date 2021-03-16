<?php


namespace App\Controller;

use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="sortie_index", methods={"GET","POST"})
     */
    public function index(SortieRepository $sortieRepository, ParticipantRepository $repoPart, SiteRepository $repoSite, Request $request, UserInterface $user): Response
    {
        /*         $sorties = $sortieRepository->findAll();
                    foreach ($sorties as $sortie){
                     dump($sortie->getParticipants()->getValues());
                     }die(); */
        $result = [];
        $filter=$request->getContent(); //1, 2, 3, 4
        if(
            !$request->request->get('filter_site') && 
            !$request->request->get('filter_nom') && 
            !$request->request->get('filtre_datedebut') && 
            !$request->request->get('filtre_datefin') &&
            !$request->request->get('filter_orga') && 
            !$request->request->get('filter_inscrit') && 
            !$request->request->get('filter_pas_inscrit') && 
            !$request->request->get('filter_passees') 

        ){

            $result = $sortieRepository->findAll();

        }else{

            if($request->request->get('filter_site')){
                $site = $repoSite->findOneBy(
                    ['id' => $request->request->get('filter_site')]
                );
                $selonsite = $sortieRepository->findBy(
                    ['site' => $site->getId()]
                );
                for ($j=0; $j < count($selonsite) ; $j++) { 
                    $result[$selonsite[$j]->getId()] = $selonsite[$j];
                }
            }
            
            if($request->request->get('filter_nom')){
                $selonsite = $sortieRepository->findByNameLike(
                    $request->request->get('filter_nom')
                );
                for ($j=0; $j < count($selonsite) ; $j++) { 
                    $result[$selonsite[$j]->getId()] = $selonsite[$j];
                }
            }
            
            if($request->request->get('filtre_datedebut') && $request->request->get('filtre_datefin')){

                $selonsite = $sortieRepository->findBetweenDates(
                    $request->request->get('filtre_datedebut'), $request->request->get('filtre_datefin')
                );
                for ($j=0; $j < count($selonsite) ; $j++) { 
                    $result[$selonsite[$j]->getId()] = $selonsite[$j];
                }
            }

            if($request->request->get('filter_orga')){
                $moi = $repoPart->findOneBy(
                    ['pseudo' => $user->getUsername()]
                );
                $byorga = $sortieRepository->findBy(
                    ['organisateur' => $moi->getId()]
                );
                for ($j=0; $j < count($byorga) ; $j++) { 
                    $result[$byorga[$j]->getId()] = $byorga[$j];
                }
            }

            if($request->request->get('filter_inscrit')){
                $moi = $repoPart->findOneBy(
                    ['pseudo' => $user->getUsername()]
                );
                $mes_sorties = $moi->getSorties();

                for ($j=0; $j < count($mes_sorties) ; $j++) { 
                    if($mes_sorties[$j]->getParticipants()->contains($moi)){
                        $result[$mes_sorties[$j]->getId()] = $mes_sorties[$j];
                    }
                }

            }

            if($request->request->get('filter_pas_inscrit')){
                $moi = $repoPart->findOneBy(
                    ['pseudo' => $user->getUsername()]
                );
                $les_sorties = $sortieRepository->findAll();

                for ($j=0; $j < count($les_sorties) ; $j++) { 
                    if(!$les_sorties[$j]->getParticipants()->contains($moi)){
                        $result[$les_sorties[$j]->getId()] = $les_sorties[$j];
                    }
                }

            }

            if($request->request->get('filter_passees')){
                $sorties_passees = $sortieRepository->findBy(
                    ['etat' => '5']
                );
                for ($j=0; $j < count($sorties_passees) ; $j++) { 
                    $result[$sorties_passees[$j]->getId()] = $sorties_passees[$j];
                }
            }

        }
        
        $sites=$repoSite->findAll();
        return $this->render('sortie/index.html.twig', [
            'sorties' => $result,
            'sites' => $sites

        ]);
    }

    //TODO: function changement etat (doc DiagEtatSortie.pdf)

}
