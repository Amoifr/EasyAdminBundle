<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Default\Search;

use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Search\DefaultCrudSearchController;

/**
 * Tests that custom query parameters are re-added as hidden fields in the index
 * search form, so they survive GET form submissions in real browsers (issue #7640).
 */
class SearchPreservesCustomQueryParametersTest extends AbstractCrudTestCase
{
    protected function getControllerFqcn(): string
    {
        return DefaultCrudSearchController::class;
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

    public function testCustomQueryParametersArePreservedAsHiddenFields(): void
    {
        $indexUrl = $this->generateIndexUrl();
        $indexUrl .= (str_contains($indexUrl, '?') ? '&' : '?').'_status=to_complete&_discipline=squat';
        $crawler = $this->client->request('GET', $indexUrl);

        $statusInput = $crawler->filter('form.form-action-search input[type="hidden"][name="_status"]');
        $this->assertCount(1, $statusInput, 'Custom "_status" parameter should be re-added as a hidden field');
        $this->assertSame('to_complete', $statusInput->attr('value'));

        $disciplineInput = $crawler->filter('form.form-action-search input[type="hidden"][name="_discipline"]');
        $this->assertCount(1, $disciplineInput, 'Custom "_discipline" parameter should be re-added as a hidden field');
        $this->assertSame('squat', $disciplineInput->attr('value'));
    }

    public function testReservedQueryParametersAreNotDuplicatedAsHiddenFields(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl('foo').'&page=2');

        // "query" is already provided by the search input, so it must not be duplicated as a hidden field
        $this->assertCount(
            0,
            $crawler->filter('form.form-action-search input[type="hidden"][name="query"]'),
            'The "query" parameter must not be re-added as a hidden field'
        );

        // "page" is reset by the form action, so it must not be preserved
        $this->assertCount(
            0,
            $crawler->filter('form.form-action-search input[type="hidden"][name="page"]'),
            'The "page" parameter must not be re-added as a hidden field'
        );
    }
}
