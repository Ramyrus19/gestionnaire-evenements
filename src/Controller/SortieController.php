<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Participant;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use App\Repository\LieuRepository;
use App\Repository\EtatRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/sortie")
 */
class SortieController extends AbstractController
{

    /**
     * @Route("/new", name="sortie_new", methods={"GET","POST"})
     */
    public function new(Request $request, ParticipantRepository $repo1, VilleRepository $repo2, LieuRepository $repo3, UserInterface $user): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $orga=$repo1->findOneByPseudo($user->getUsername());
        $sortie->setOrganisateur($orga);

        $sortie->setSite($orga->getSite());

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_index');
        }

        $villes = $repo2->findAll();
        $lieux = $repo3->findAll();
        $ville_orga = $sortie->getOrganisateur()->getSite()->getNom();

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
            'villes' => $villes,
            'lieux' => $lieux,
            'ville_orga' => $ville_orga,
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_show", methods={"GET"})
     */
    public function show(Sortie $sortie): Response
    {
        $ville_orga = $sortie->getOrganisateur()->getSite()->getNom();
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'ville_orga' => $ville_orga,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sortie_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Sortie $sortie, VilleRepository $repo2, LieuRepository $repo3, UserInterface $user): Response
    {
        
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if($sortie->getOrganisateur()->getPseudo() == $user->getUsername()){
            // editeur == organisateur
            if ($form->isSubmitted() && $form->isValid()) {
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('sortie_index');
            }
        }else{
            $this->addFlash('notice','Vous n\'avez pas les droits pour modifier.');
            return $this->redirectToRoute('sortie_index');
        };

        $villes = $repo2->findAll();
        $lieux = $repo3->findAll();
        $ville_orga = $sortie->getOrganisateur()->getSite()->getNom();

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
            'villes' => $villes,
            'lieux' => $lieux,
            'ville_orga' => $ville_orga,
        ]);
    }

    /**
     * @Route("/{id}/annuler", name="sortie_annuler", methods={"GET","POST"})
     */
    public function annuler(Request $request, Sortie $sortie, EtatRepository $repo1, UserInterface $user): Response
    {
        if($sortie->getOrganisateur()->getPseudo() == $user->getUsername() || $this->isGranted('ROLE_ADMIN') ){
            $form = $this->createForm(SortieType::class, $sortie);
            $form->handleRequest($request);

            // editeur == organisateur
            if ($form->isSubmitted() && $form->isValid()) {
                $sortie->setEtat($repo1->find(6));
                $sortie->getParticipants()->clear();
                $this->getDoctrine()->getManager()->flush();
    
                return $this->redirectToRoute('sortie_index');
            }

            $ville_orga = $sortie->getOrganisateur()->getSite()->getNom();

            return $this->render('sortie/annuler.html.twig', [
                'sortie' => $sortie,
                'form' => $form->createView(),
                'ville_orga' => $ville_orga,
            ]);
        }else{
            $this->addFlash('notice','Vous n\'avez pas les droits pour annuler.');
            return $this->redirectToRoute('sortie_index');
        }
    }
    /**
     * @Route("/{action}/{idSortie}/{idParticipant}", name="sortie_inscription", methods={"GET", "POST"})
     */
    public function inscription(Request $request, $action, $idSortie, $idParticipant, EntityManagerInterface $em): Response
    {
        $sortie = $em->getRepository(Sortie::class)->find($idSortie);
        $participant = $em->getRepository(Participant::class)->find($idParticipant);

        if ($action==='ins' && $sortie->getNbPlaces() > $sortie->getParticipants()->count() ){
            $sortie->addParticipant($participant);
        }elseif($action==='des'){
            $sortie->removeParticipant($participant);
        }
        
        $em->persist($sortie);
        $em->flush();

        return $this->redirectToRoute('sortie_index');
    }



}
