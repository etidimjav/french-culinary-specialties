<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * Tag.
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="App\Repository\TagRepository")
 * @ExclusionPolicy("all")
 */
class Tag
{
    use TimestampableTrait;

    /**
     * @var null|int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var null|string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=true)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Speciality", mappedBy="tags", cascade={"remove"})
     */
    private $specialities;

    public function __construct()
    {
        $this->specialities = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @VirtualProperty
     * @SerializedName("id")
     * @Groups({"fullTag"})
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @VirtualProperty
     * @SerializedName("name")
     * @Groups({"fullTag"})
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set name.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get specialities.
     *
     * @return Collection|Speciality[]
     */
    public function getSpecialities(): Collection
    {
        return $this->specialities;
    }

    /**
     * Add speciality.
     *
     * @param mixed $speciality
     */
    public function addSpeciality(Speciality $speciality): self
    {
        if (!$this->specialities->contains($speciality)) {
            $this->specialities[] = $speciality;
            $speciality->addTag($this);
        }

        return $this;
    }

    /**
     * Remove speciality.
     *
     * @param mixed $speciality
     */
    public function removeSpeciality(Speciality $speciality): self
    {
        if ($this->specialities->contains($speciality)) {
            $this->specialities->removeElement($speciality);
            $speciality->removeTag($this);
        }

        return $this;
    }
}
