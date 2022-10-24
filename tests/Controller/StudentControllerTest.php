<?php

namespace App\Test\Controller;

use App\Entity\Student;
use App\Entity\Project;
use App\Entity\StudentGroup;
use App\Repository\StudentRepository;
use App\Repository\ProjectRepository;
use App\Repository\StudentGroupRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\InitProjectGroupsService;

class StudentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private StudentRepository $repository;
    private ProjectRepository $projectRepository;
    private StudentGroupRepository $studentGroupRepository;
    private Project $project;
    private StudentGroup $group;

    private string $path = '/student/';

    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'HTTP_HOST'       => 'localhost:8000'
        ]);
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Student::class);
        $this->projectRepository = static::getContainer()->get('doctrine')->getRepository(Project::class);
        $this->studentGroupRepository = static::getContainer()->get('doctrine')->getRepository(StudentGroup::class);

        $this->project = new Project();
        $this->project
            ->setName('My Title')
            ->setMaxStudentsPerGroup(3)
            ->setNumGroups(3);
        $this->projectRepository->save($this->project, true);

        $container = static::getContainer();
        $initProjectGroupsService = $container->get(InitProjectGroupsService::class);
        $initProjectGroupsService->initProjectGroups($this->project, 3);

        $this->group = $this->studentGroupRepository->findOneBy(['project' => $this->project]);
        $this->project->addStudentGroup($this->group);
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', sprintf('%s%s/new', $this->path, $this->project->getId()));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'student[fullname]' => 'Testing',
        ]);

        self::assertResponseRedirects(sprintf('/%s/status', $this->project->getId()));

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testRemove(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Student();
        $fixture->setFullname('My Title');
        $fixture->setStudentGroup($this->group);
        $fixture->setProject($this->project);
        $this->repository->save($fixture, true);
    
        $this->project->addStudent($fixture);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('/%s/status', $this->project->getId()));
        $response = $this->client->getResponse();
        file_put_contents('response.html', $response); 
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects(sprintf('/%s/status', $this->project->getId()));
    }

    public function testCleanUp()
    {
        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
        foreach ($this->studentGroupRepository->findAll() as $object) {
            $this->studentGroupRepository->remove($object, true);
        }
        foreach ($this->projectRepository->findAll() as $object) {
            $this->projectRepository->remove($object, true);
        }
        self::assertSame(1,1);

    }
}
