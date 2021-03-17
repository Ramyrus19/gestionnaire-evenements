<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Service\CsvImport;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\File;


/**
 * @Route("/participant")
 */
class ParticipantController extends AbstractController
{
    /**
     * @Route("/", name="participant_index", methods={"GET", "POST"})
     */
    public function index(Request $request, ParticipantRepository $participantRepository, CsvImport $csvImport): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createFormBuilder()
            ->add('csvfile', FileType::class, [
                'label' => false,
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'accept'=> ".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'application/vnd.ms-excel',
                            'text/plain',
                            'text/csv',
                        ],
                        'mimeTypesMessage' => 'Format invalid ! Format accepté: csv',
                    ])
                ],
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('csvfile')->getData();
            $response = $csvImport->import($file);
            if ($response->getContent() === 'success'){
                $this->addFlash(
                    'success',
                    'Fichier importé avec success !'
                );
            }else{
                $this->addFlash(
                    'duplicate',
                    'Erreur import ! Doublons !'
                );
            }

            return $this->redirectToRoute('participant_index');
        }
        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
            'import_form' => $form->createView()
        ]);
    }

    /**
     * @Route("/new", name="participant_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
    public function edit(Request $request, Participant $participant, UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader, UserInterface $user, $id): Response
    {
        if ($this->isGranted('ROLE_ADMIN') || $id == $user->getId()){
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
                if ($form->get('urlPhoto')->getData() !== null){
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
        }else{
            return $this->redirect($this->generateUrl('participant_edit', array('id' => $user->getId())));
        }

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
     * @Route("/{status}", name="participant_status", methods={"POST"})
     */
    public function userChangeStatus(Request $request, EntityManagerInterface $entityManager, $status): JsonResponse
    {
        $data = json_decode($request->getContent());

        foreach ($data as $d){
            $user = $entityManager->getRepository(Participant::class)->findOneBy(['pseudo' => $d->pseudo]);
            if ($status == 'enable'){
                $user->setActif(true);
            }elseif ($status == 'disable'){
                $user->setActif(false);
            }
            $entityManager->persist($user);

            if ($status == 'delete'){
                $entityManager->remove($user);
                $entityManager->flush();
            }
        }
        $entityManager->flush();
        return new JsonResponse();
    }

}
