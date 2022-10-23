<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: StudentGroup::class)]
    private Collection $studentGroups;

    #[ORM\Column]
    private ?int $max_students_per_group = null;

    private ?int $num_groups = null;

    public function __construct()
    {
        $this->studentGroups = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, StudentGroup>
     */
    public function getStudentGroups(): Collection
    {
        return $this->studentGroups;
    }

    public function addStudentGroup(StudentGroup $studentGroup): self
    {
        if (!$this->studentGroups->contains($studentGroup)) {
            $this->studentGroups->add($studentGroup);
            $studentGroup->setProject($this);
        }

        return $this;
    }

    public function removeStudentGroup(StudentGroup $studentGroup): self
    {
        if ($this->studentGroups->removeElement($studentGroup)) {
            // set the owning side to null (unless already changed)
            if ($studentGroup->getProject() === $this) {
                $studentGroup->setProject(null);
            }
        }

        return $this;
    }

    public function getMaxStudentsPerGroup(): ?int
    {
        return $this->max_students_per_group;
    }

    public function setMaxStudentsPerGroup(int $max_students_per_group): self
    {
        $this->max_students_per_group = $max_students_per_group;

        return $this;
    }

    /**
     * Get the value of num_groups
     */ 
    public function getNumGroups()
    {
        return $this->num_groups;
    }

    /**
     * Set the value of num_groups
     *
     * @return  self
     */ 
    public function setNumGroups($num_groups)
    {
        $this->num_groups = $num_groups;

        return $this;
    }
}
