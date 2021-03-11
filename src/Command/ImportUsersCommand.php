<?php


namespace App\Command;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ImportUsersCommand extends Command
{
    protected static $defaultName = 'csv:import:users';

    private $em;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        parent::__construct();

        $this->em = $em;
        $this->passwordEncoder = $encoder;

    }


    protected function configure()
    {
        $this
            ->setDescription('Import utilisateurs depuis un fichier CSV')
            ->setHelp('Cette commande vous permet d\'importer des utilisateurs dans la base de données à partir d\'un fichier .csv')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('L\'import a commencé...');

        $reader = Reader::createFromPath('%kernel.root_dir%/../public/imports/MOCK_DATA.csv');

//        $results = $reader->getRecords();
        $results = $reader->getRecords(['pseudo', 'nom', 'prenom', 'telephone', 'mail','admin','actif','site','plainPassword']);
        $io->progressStart(iterator_count($results));

        foreach ($results as $row) {
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
                ->setAdmin($row['admin'])
                ->setActif($row['actif'])
                ->setSite($site)
            ;

            $this->em->persist($user);

            $io->progressAdvance();
        }

        $this->em->flush();

        $io->progressFinish();
        $io->success('Commande exécutée avec success !');

        return Command::SUCCESS;
    }
}