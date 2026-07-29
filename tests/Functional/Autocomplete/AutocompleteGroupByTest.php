<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Autocomplete;

use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\ProjectDomain\DeveloperCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\ProjectDomain\ProjectCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\ProjectDomain\ProjectIssueWithAutocompleteCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\ProjectDomain\ProjectIssueWithGroupByCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\Developer;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\Project;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\ProjectIssue;

class AutocompleteGroupByTest extends AbstractCrudTestCase
{
    protected function getControllerFqcn(): string
    {
        return ProjectIssueWithGroupByCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    private function createProject(string $name, bool $internal): Project
    {
        $project = new Project();
        $project->setName($name);
        $project->setDescription('Test Description');
        $project->setInternal($internal);
        $project->setStatesSimpleArray(['active']);
        $project->setStartDateMutable(new \DateTime());
        $project->setStartDateImmutable(new \DateTimeImmutable());
        $project->setStartDateTimeMutable(new \DateTime());
        $project->setStartDateTimeImmutable(new \DateTimeImmutable());
        $project->setStartDateTimeTzMutable(new \DateTime());
        $project->setStartDateTimeTzImmutable(new \DateTimeImmutable());
        $project->setCountInteger(0);
        $project->setCountSmallint(0);
        $project->setPriceDecimal('0.00');
        $project->setPriceFloat(0.0);
        $project->setStartTimeMutable(new \DateTime());
        $project->setStartTimeImmutable(new \DateTimeImmutable());

        return $project;
    }

    private function requestAutocompleteResults(string $targetControllerFqcn, string $originatingControllerFqcn, string $propertyName, string $query): array
    {
        $autocompleteUrl = $this->adminUrlGenerator
            ->setDashboard(DashboardController::class)
            ->setController($targetControllerFqcn)
            ->setAction('autocomplete')
            ->set('page', 1)
            ->set('query', $query)
            ->set('autocompleteContext', [
                'crudControllerFqcn' => $originatingControllerFqcn,
                'propertyName' => $propertyName,
                'originatingPage' => 'new',
            ])
            ->generateUrl();

        $this->client->request('GET', $autocompleteUrl);
        $this->assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('results', $data);

        return $data['results'];
    }

    public function testGroupByCallableAddsTheGroupToEachResult(): void
    {
        $internalProject = $this->createProject('Groupby Internal Tooling', true);
        $publicProject = $this->createProject('Groupby Public Website', false);

        $this->entityManager->persist($internalProject);
        $this->entityManager->persist($publicProject);
        $this->entityManager->flush();

        $results = $this->requestAutocompleteResults(
            ProjectCrudController::class,
            ProjectIssueWithGroupByCrudController::class,
            'project',
            'Groupby'
        );

        $this->assertCount(2, $results);

        $resultsByLabel = array_column($results, null, 'entityAsString');
        $this->assertSame('Internal', $resultsByLabel['Groupby Internal Tooling']['entityGroup']);
        // a null group leaves the result ungrouped: the key must not even be present
        $this->assertArrayNotHasKey('entityGroup', $resultsByLabel['Groupby Public Website']);
    }

    public function testGroupByPropertyPathAddsTheGroupToEachResult(): void
    {
        $developer = new Developer();
        $developer->setName('Groupby Grace Hopper');

        $this->entityManager->persist($developer);
        $this->entityManager->flush();

        $results = $this->requestAutocompleteResults(
            DeveloperCrudController::class,
            ProjectIssueWithGroupByCrudController::class,
            'assignedDeveloper',
            'Groupby Grace'
        );

        $this->assertCount(1, $results);
        $this->assertSame('Groupby Grace Hopper', $results[0]['entityGroup']);
    }

    public function testResultsHaveNoGroupWhenGroupByIsNotDefined(): void
    {
        $project = $this->createProject('Groupby Control Project', false);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $results = $this->requestAutocompleteResults(
            ProjectCrudController::class,
            ProjectIssueWithAutocompleteCrudController::class,
            'project',
            'Groupby Control'
        );

        $this->assertNotEmpty($results);
        foreach ($results as $result) {
            $this->assertArrayNotHasKey('entityGroup', $result);
        }
    }

    public function testSelectedChoicesAreRenderedInsideOptgroupsInTheForm(): void
    {
        $internalProject = $this->createProject('Groupby Form Project', true);

        $issue = new ProjectIssue();
        $issue->setName('Groupby Form Issue');
        $issue->setProject($internalProject);

        $this->entityManager->persist($internalProject);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $editUrl = $this->adminUrlGenerator
            ->setDashboard(DashboardController::class)
            ->setController(ProjectIssueWithGroupByCrudController::class)
            ->setAction('edit')
            ->setEntityId($issue->getId())
            ->generateUrl();

        $crawler = $this->client->request('GET', $editUrl);
        $this->assertResponseIsSuccessful();

        $selectedOption = $crawler->filter('optgroup[label="Internal"] option[selected]');
        $this->assertCount(1, $selectedOption);
        $this->assertSame('Groupby Form Project', $selectedOption->text());
    }
}
