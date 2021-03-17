<?php

namespace App\Entity;

use App\Repository\LieuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=LieuRepository::class)
 */
class Lieu
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(message="Veuillez reinseigner un nom")
     * @Assert\Length(max=30, maxMessage="Nom trop long. Nombre maximum de caractères: {{ limit }}")
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Assert\NotBlank(message="Veuillez reinseigner une rue")
     * @Assert\Length(max=30, maxMessage="Adresse trop longue. Nombre maximum de caractères: {{ limit }}")
     */
    private $rue;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @Assert\Regex(
     *     pattern="/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/",
     *     message="Veuillez reinseigner des coordonnées valides"
     * )
     */
    private $latitude;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Regex(
     *     pattern="/^(-?\d+(\.\d+)?),\s*(-?\d+(\.\d+)?)$/",
     *     message="Veuillez reinseigner des coordonnées valides"
     * )
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="lieux")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $ville;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRue(): ?string
    {
        return $this->rue;
    }

    public function setRue(?string $rue): self
    {
        $this->rue = $rue;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getVille(): ?Ville
    {
        return $this->ville;
    }

    public function setVille(?Ville $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function toJson(): ?string
    {
        return '{"id":"'.$this->id.'","nom": "'.$this->nom.'", "rue": "'.$this->rue.'", "latitude": "'.$this->latitude.'", "longitude": "'.$this->longitude.'", "ville_id": "'.$this->ville->getId().'", "ville_cp": "'.$this->ville->getCp().'"}';
    }
}
