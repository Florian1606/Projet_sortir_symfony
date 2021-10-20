<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\NotBlank(message="Veuillez saisir un nom")
     * @Assert\Length(
     *    min=3,
     *   max=250,
     *   minMessage="Le nom doit faire au moins {{ limit }} caractères",
     *    maxMessage="Le nom ne peut pas faire plus de {{ limit }} caractères"
     * )
     */
    private $nom;

    /**
     *
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank(message="Veuillez saisir une date de début")
     * @Assert\GreaterThanOrEqual(
     *      value = "today",
     *      message = "La date doit être supérieur ou égale à la date d'aujourd'hui"
     * )
     * @Assert\Type(
     *      type = "\DateTime",
     *      message = "Le type de la date n'est pas valide",
     * )
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Positive(message="La durée doit être strictement positif")
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull(message="Veuillez saisir une date de fin")
     * @Assert\Type(
     *      type = "\DateTime",
     *      message = "vacancy.date.valid",
     * )
     * @Assert\GreaterThanOrEqual(
     *      value = "today",
     *      message = "La date doit être supérieur ou égale à la date d'aujourd'hui"
     * )
     * @Assert\Expression(
     *     "this.getDateLimiteInscription() > this.getDateDebut()",
     *     message="La date de fin doit être strictement supérieur à la date de début de la sortie"
     * )
     */
    private $dateLimiteInscription;

    /**
     *
     * @ORM\Column(type="integer")
     * @Assert\Positive(message="Le nombre d'inscription max doit être strictement positif")
     */
    private $nbIncriptionMax;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $urlPhoto;

    /**
     * @ORM\ManyToMany(targetEntity=Participant::class, mappedBy="sorties")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=Participant::class, inversedBy="SortiesOrganisees")
     */
    private $organisateur;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="sorties")
     *
     *
     */
    private $site;

    /**
     * @ORM\ManyToOne(targetEntity=Etat::class, inversedBy="sorties")
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     */
    private $lieu;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

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

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbIncriptionMax(): ?int
    {
        return $this->nbIncriptionMax;
    }

    public function setNbIncriptionMax(int $nbIncriptionMax): self
    {
        $this->nbIncriptionMax = $nbIncriptionMax;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrlPhoto(): ?string
    {
        return $this->urlPhoto;
    }

    public function setUrlPhoto(?string $urlPhoto): self
    {
        $this->urlPhoto = $urlPhoto;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addSorty($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSorty($this);
        }

        return $this;
    }

    public function getOrganisateur(): ?Participant
    {
        return $this->organisateur;
    }

    public function setOrganisateur(?Participant $organisateur): self
    {
        $this->organisateur = $organisateur;

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getEtat(): ?Etat
    {
        return $this->etat;
    }

    public function setEtat(?Etat $etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    public function getLieu(): ?Lieu
    {
        return $this->lieu;
    }

    public function setLieu(?Lieu $lieu): self
    {
        $this->lieu = $lieu;

        return $this;
    }
}
