<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Entity\Etat;
use App\Form\SortieType;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use App\Repository\LieuRepository;
use App\Repository\EtatRepository;
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
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
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
            // TODO dsl vous n'êtes pas l'organisateur.
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
     * @Route("/{id}", name="sortie_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Sortie $sortie, EtatRepository $repo1): Response
    {
        $sortie->setEtat($repo1->find(6));
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('sortie_index');
    }
}
