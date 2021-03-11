<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Service\FileUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


/**
 * @Route("/participant")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("/", name="participant_index", methods={"GET"})
     */
    public function index(ParticipantRepository $participantRepository): Response
    {
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="participant_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $participant = new Participant();
        $form = $this->createForm(RegistrationFormType::class, $participant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            if ($formData->getAdmin()){
                $participant->setRoles(['ROLE_ADMIN']);
            }
            if ($formData->getActif()){
                $participant->setActif(true);
            }else{
                $participant->setActif(false);
            }

            // encode the plain password
            $participant->setPassword(
                $passwordEncoder->encodePassword(
                    $participant,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($participant);
            $entityManager->flush();

            return $this->redirectToRoute('participant_index');
        }

        return $this->render('participant/new.html.twig', [
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="participant_show", methods={"GET"})
     */
    public function show(Participant $participant): Response
    {
        return $this->render('participant/show.html.twig', [
            'participant' => $participant,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="participant_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Participant $participant, UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(ParticipantType::class, $participant, ['role' => $this->getUser()->getRoles()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('password')->getData();
            if ($plainPassword !== null) {
                $participant->setPassword(
                    $passwordEncoder->encodePassword(
                        $participant,
                        $plainPassword
                    )
                );
            }
            //upload file by using the FileUploader service
            if ($form->getData()->getUrlPhoto() !== null){
                $file = $form->get('urlPhoto')->getData();
                if ($file) {
                    $fileName = $fileUploader->upload($file);
                    $participant->setUrlPhoto($fileName);
                }
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('participant_index');
        }

        return $this->render('participant/edit.html.twig', [
            'participant' => $participant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="participant_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Participant $participant): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($participant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('participant_index');
    }

    /**
     * @Route("/status", name="participant_status", methods={"POST"})
     */
    public function userDisable(Request $request){
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent());
        foreach ($data as $d){
            $user = $em->getRepository(Participant::class)->find($d->id);
            if ($d->status == true){
                $user->setActif(false);
            }else{
                $user->setActif(true);
            }
            $this->getDoctrine()->getManager()->persist($user);
        }

        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('participant_index');
    }

}
