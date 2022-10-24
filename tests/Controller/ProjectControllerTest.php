<?php

namespace App\Test\Controller;

use App\Entity\Project;
use App\Entity\StudentGroup;
use App\Repository\ProjectRepository;
use App\Repository\StudentGroupRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Service\InitProjectGroupsService;

class ProjectControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ProjectRepository $repository;
    private StudentGroupRepository $studentGroupRepository;
    private string $path = '/';

    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'HTTP_HOST'       => 'localhost:8000'
        ]);
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Project::class);
        $this->studentGroupRepository = static::getContainer()->get('doctrine')->getRepository(StudentGroup::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project index');
        self::assertSelectorTextContains('a', 'Create new');
        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());
        $studentGroupsCount = count($this->studentGroupRepository->findAll());

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $numGroups = 4;
        $this->client->submitForm('Save', [
            'project[name]' => 'Testing',
            'project[max_students_per_group]' => 5,
            'project[num_groups]' => $numGroups,
        ]);

        self::assertResponseRedirects('/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
        self::assertSame($studentGroupsCount + $numGroups, count($this->studentGroupRepository->findAll()));

        // Delete Student groups first as they constraint the deletion of project
        foreach($this->studentGroupRepository->findAll() as $studentGroup)
        {
            $this->studentGroupRepository->remove($studentGroup, true);
        } 
    }

    public function testGroupCreationAndDeletion()
    {
        $fixture = new Project();
        $fixture->setName('My Title');

        $maxStudentsperGroup = 2;
        $fixture->setMaxStudentsPerGroup($maxStudentsperGroup);

        $numGroups = 3;
        $fixture->setNumGroups($numGroups);
        $this->repository->save($fixture, true);

        $container = static::getContainer();
        $initProjectGroupsService = $container->get(InitProjectGroupsService::class);

        $studentGroupsCount = count($this->studentGroupRepository->findAll());
        $initProjectGroupsService->initProjectGroups($fixture, $numGroups);
        self::assertSame($studentGroupsCount + $numGroups, count($this->studentGroupRepository->findAll()));

        $projectGroups = $this->studentGroupRepository->findBy(['project' => $fixture]);
        self::assertSame($numGroups, count($projectGroups));

        $group = $this->studentGroupRepository->findOneBy(['project' => $fixture]);
        self::assertNotNull($group);
        foreach($projectGroups as $studentGroup)
        {
            $this->studentGroupRepository->remove($studentGroup, true);
        }
        self::assertSame($studentGroupsCount, count($this->studentGroupRepository->findAll()));
        self::assertSame(0, count($fixture->getStudentGroups()));
    }

    public function testProjectStatusPage(): void
    {
        $fixture = new Project();
        $fixture->setName('Project X');
        $fixture->setMaxStudentsPerGroup(2);

        $this->repository->save($fixture, true);
        $this->client->request('GET', sprintf('%s%s/status', $this->path, $fixture->getId()));
        self::assertSelectorTextContains('h3', 'Project status page');
        self::assertSelectorExists('div#project-content');
    }

    public function testRemove(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setMaxStudentsPerGroup(2);

        $this->repository->save($fixture, true);
        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s/status', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/');
    }
}
