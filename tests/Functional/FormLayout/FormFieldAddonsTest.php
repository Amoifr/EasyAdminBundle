<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\FormLayout;

use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\DashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Synthetic\FormFieldAddonsSyntheticCrudController;

/**
 * Tests for the ->prepend() and ->append() methods of fields and how
 * those addons are rendered in form inputs.
 */
class FormFieldAddonsTest extends AbstractCrudTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    protected function getControllerFqcn(): string
    {
        return FormFieldAddonsSyntheticCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return DashboardController::class;
    }

    public function testFieldWithNoAddons(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        static::assertCount(0, $crawler->filter('#FormTestEntity_street')->closest('.form-widget')->filter('.ea-input-group'), 'The street field defines no addons, so its input is not wrapped in an addon group.');
    }

    public function testAddonWithTextContent(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $group = $crawler->filter('#FormTestEntity_name')->closest('.ea-input-group');
        static::assertCount(1, $group, 'The name field input is rendered inside the addon group.');
        static::assertSame('https://', trim($group->filter('.ea-input-addon-prepend')->text()));
        static::assertCount(0, $group->filter('.ea-input-addon-append'));
    }

    public function testAddonWithHtmlContent(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $addon = $crawler->filter('#FormTestEntity_email')->closest('.ea-input-group')->filter('.ea-input-addon-append');
        static::assertSame('<b>@example.com</b>', trim($addon->html()), 'Addon HTML contents are rendered instead of escaped.');
    }

    public function testAddonWithTranslatableContent(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $addon = $crawler->filter('#FormTestEntity_priority')->closest('.ea-input-group')->filter('.ea-input-addon-prepend');
        static::assertSame('Priority Addon Lorem Ipsum', trim($addon->text()));
    }

    public function testAddonWithIcon(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $addon = $crawler->filter('#FormTestEntity_phone')->closest('.ea-input-group')->filter('.ea-input-addon-append');
        static::assertCount(1, $addon->filter('svg'), 'Icon addons render the icon as an inline SVG (internal icon set).');
        static::assertSame('', trim($addon->text()), 'Icon-only addons render no text.');
    }

    public function testAddonWithIconAndTextContent(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $addon = $crawler->filter('#FormTestEntity_city')->closest('.ea-input-group')->filter('.ea-input-addon-prepend');
        static::assertCount(1, $addon->filter('svg'));
        static::assertSame('Search', trim($addon->text()));
    }

    public function testFieldWithBothPrependAndAppend(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $group = $crawler->filter('#FormTestEntity_country')->closest('.ea-input-group');
        static::assertCount(1, $group, 'The country field input is rendered inside a single addon group.');
        static::assertSame('https://', trim($group->filter('.ea-input-addon-prepend')->text()));
        static::assertSame('.com', trim($group->filter('.ea-input-addon-append')->text()));
    }

    public function testMoneyFieldRendersItsCurrencySymbolAsAddon(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $group = $crawler->filter('#FormTestEntity_priceInCents')->closest('.ea-input-group');
        static::assertCount(1, $group, 'The money input is rendered inside a single addon group.');
        static::assertCount(0, $group->filter('.input-group'), 'The money widget does not render its legacy Bootstrap input-group.');

        $prependAddons = $group->filter('.ea-input-addon-prepend');
        static::assertCount(2, $prependAddons, 'The user-defined addon and the currency symbol render as sibling addons.');
        static::assertSame('Fee', trim($prependAddons->eq(0)->text()), 'The user-defined addon is rendered first.');
        static::assertSame('$', trim($prependAddons->eq(1)->text()), 'The currency symbol is rendered next to the input.');
    }

    public function testPercentFieldRendersItsSymbolAsAddon(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        $group = $crawler->filter('#FormTestEntity_score')->closest('.ea-input-group');
        static::assertCount(1, $group);
        static::assertCount(0, $group->filter('.input-group'));
        static::assertSame('%', trim($group->filter('.ea-input-addon-append')->text()));
    }

    public function testAddonsAreIgnoredInUnsupportedFields(): void
    {
        $crawler = $this->client->request('GET', $this->generateNewFormUrl());

        static::assertCount(0, $crawler->filter('#FormTestEntity_description')->closest('.form-widget')->filter('.ea-input-group'), 'Textarea fields ignore the addons.');
        static::assertCount(0, $crawler->filter('.field-array .ea-input-group'), 'Array fields ignore the addons.');
    }
}
