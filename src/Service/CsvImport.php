<?php


namespace App\Service;


use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CsvImport
{

    private $em;
    private $passwordEncoder;
    private $fileUploader;
    private $targetDirectory;

    public function __construct($targetDirectory, EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, FileUploader $fileUploader)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
        $this->fileUploader = $fileUploader;
        $this->targetDirectory = $targetDirectory;
    }

    public function import($file){
        $response = "";
        $fileName = $this->fileUploader->upload($file);
        $reader = Reader::createFromPath($this->getTargetDirectory().'/'.$fileName);

        $results = $reader->getRecords(['pseudo', 'nom', 'prenom', 'telephone', 'mail','admin','actif','site','plainPassword']);

        foreach ($results as $row) {
            $existentUser = $this->em->getRepository(Participant::class)->findOneBy(['pseudo' => $row['pseudo']]);

            if (null == $existentUser){
                $site = new Site();
                $site->setNom($row['site']);
                $this->em->persist($site);

                $user = new Participant();
                $user->setPassword(
                    $this->passwordEncoder->encodePassword(
                        $user,
                        $row['plainPassword']
                    )
                );

                $user->setPseudo($row['pseudo'])
                    ->setNom($row['nom'])
                    ->setPrenom($row['prenom'])
                    ->setTelephone($row['telephone'])
                    ->setMail($row['mail'])
                    ->setAdmin(filter_var($row['admin'], FILTER_VALIDATE_BOOLEAN))
                    ->setActif(filter_var($row['actif'], FILTER_VALIDATE_BOOLEAN))
                    ->setSite($site)
                ;
                $this->em->persist($user);

                $response = "success";
            }else{
                $response = "duplicate";
            }
        }

        $this->em->flush();

        return new Response($response);
    }

    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }

}