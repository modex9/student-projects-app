<?php

namespace App\Test\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProjectControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private ProjectRepository $repository;
    private string $path = '/';

    protected function setUp(): void
    {
        $this->client = static::createClient([], [
            'HTTP_HOST'       => 'localhost:8000'
        ]);
        $this->repository = static::getContainer()->get('doctrine')->getRepository(Project::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project index');
        $this->assertSelectorTextContains('a', 'Create new');
        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'project[name]' => 'Testing',
            'project[max_students_per_group]' => 5,
        ]);

        self::assertResponseRedirects('/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setMaxStudentsPerGroup(2);

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($fixture);
        $entityManager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Project');
        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setMaxStudentsPerGroup(2);

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($fixture);
        $entityManager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'project[name]' => 'Something New',
            'project[max_students_per_group]' => 3,
        ]);

        self::assertResponseRedirects('/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame(3, $fixture[0]->getMaxStudentsPerGroup());
    }

    public function testRemove(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Project();
        $fixture->setName('My Title');
        $fixture->setMaxStudentsPerGroup(2);
        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->persist($fixture);
        $entityManager->flush();

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/');
    }
}
