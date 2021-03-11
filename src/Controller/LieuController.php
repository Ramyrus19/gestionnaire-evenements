<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Form\LieuType;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @Route("/lieu")
 */
class LieuController extends AbstractController
{
    /**
     * @Route("/", name="lieu_index", methods={"GET"})
     */
    public function index(LieuRepository $lieuRepository): Response
    {
        return $this->render('lieu/index.html.twig', [
            'lieus' => $lieuRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="lieu_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $lieu = new Lieu();
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($lieu);
            $entityManager->flush();

            return $this->redirectToRoute('lieu_index');
        }

        return $this->render('lieu/new.html.twig', [
            'lieu' => $lieu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/api_new", name="lieu_new_api", methods={"POST"})
     */
    public function api_new(EntityManagerInterface $em, Request $request, VilleRepository $repo2): Response
    {
        $obj = json_decode($request -> getContent());
        $infos["message"]="error_vide";

        if(isset($obj->nom) && isset($obj->rue) && isset($obj->latitude)
        && isset($obj->longitude) && isset($obj->ville_id)
        && $obj->nom!="" && $obj->rue!="" && $obj->latitude!=""
        && $obj->longitude!="" && $obj->ville_id!=""){

            $lieu = new Lieu();
            $lieu->setNom($obj->nom);
            $lieu->setRue($obj->rue);
            $lieu->setLatitude($obj->latitude);
            $lieu->setLongitude($obj->longitude);

            $ville = $repo2->find((int)$obj->ville_id);
            $lieu->setVille($ville);
        
            $em->persist($lieu);
            $em->flush();
            $infos["lieu_nom"] = $lieu->toJson();
            $infos["ville"] = $ville->toJson();
            $infos["message"] = "ok";

        }
        
        return $this->json($infos);
    }

    /**
     * @Route("/{id}", name="lieu_show", methods={"GET"})
     */
    public function show(Lieu $lieu): Response
    {
        return $this->render('lieu/show.html.twig', [
            'lieu' => $lieu,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="lieu_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Lieu $lieu): Response
    {
        $form = $this->createForm(LieuType::class, $lieu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('lieu_index');
        }

        return $this->render('lieu/edit.html.twig', [
            'lieu' => $lieu,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="lieu_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Lieu $lieu): Response
    {
        if ($this->isCsrfTokenValid('delete'.$lieu->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($lieu);
            $entityManager->flush();
        }

        return $this->redirectToRoute('lieu_index');
    }
}
