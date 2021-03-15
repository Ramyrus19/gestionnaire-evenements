<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\ParticipantType;
use App\Form\RegistrationFormType;
use App\Repository\ParticipantRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
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
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('participant/index.html.twig', [
            'participants' => $participantRepository->findAll(),
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

        $users = $em->getRepository(Participant::class)->findAll();
        $em->flush();

        return new JsonResponse(json_encode($users));
    }

    /**
     * @Route("/import", name="participant_import", methods={"POST"})
     */
    public function importCsv(Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder){

//        $file = $form->get('urlPhoto')->getData();
//        if ($file) {
//            $fileName = $fileUploader->upload($file);
//            $participant->setUrlPhoto($fileName);
//        }
//        $reader = Reader::createFromPath('%kernel.root_dir%/../public/imports/MOCK_DATA.csv');
//
//        $results = $reader->getRecords(['pseudo', 'nom', 'prenom', 'telephone', 'mail','admin','actif','site','plainPassword']);
//
//        foreach ($results as $row) {
//            $site = new Site();
//            $site->setNom($row['site']);
//            $em->persist($site);
//            $user = new Participant();
//            $user->setPassword(
//                $passwordEncoder->encodePassword(
//                    $user,
//                    $row['plainPassword']
//                )
//            );
//
//            $user->setPseudo($row['pseudo'])
//                ->setNom($row['nom'])
//                ->setPrenom($row['prenom'])
//                ->setTelephone($row['telephone'])
//                ->setMail($row['mail'])
//                ->setAdmin(filter_var($row['admin'], FILTER_VALIDATE_BOOLEAN))
//                ->setActif(filter_var($row['actif'], FILTER_VALIDATE_BOOLEAN))
//                ->setSite($site)
//            ;
//
//            $em->persist($user);
//
//        }
//
//        $em->flush();

        return $this->render(''

        );
    }
}
