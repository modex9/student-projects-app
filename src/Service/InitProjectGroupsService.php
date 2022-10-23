<?php

namespace App\Service;

use App\Entity\StudentGroup;
use App\Repository\StudentGroupRepository;
use Doctrine\ORM\EntityManagerInterface;

class InitProjectGroupsService
{
    private $sudentGroupRepository;

    public function __construct(StudentGroupRepository $sudentGroupRepository) 
    {
        $this->sudentGroupRepository = $sudentGroupRepository;
    }

    public function initProjectGroups($project, $numGroups)
    {
        for($i = 0; $i < $numGroups; $i++) 
        {
            $group = new StudentGroup();
            $group->setProject($project);
            $this->sudentGroupRepository->save($group, true);
        }
    }
}