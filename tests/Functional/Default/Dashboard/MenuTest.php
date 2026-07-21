<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Default\Dashboard;

use EasyCorp\Bundle\EasyAdminBundle\Test\AbstractCrudTestCase;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\CategoryCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Dashboard\MenuDashboardController;

/**
 * Tests for main menu functionality in the dashboard:
 * - Menu items rendering
 * - Menu sections
 * - Submenus with hierarchy
 * - Active menu item highlighting
 * - Menu item badges.
 */
class MenuTest extends AbstractCrudTestCase
{
    protected function getControllerFqcn(): string
    {
        return CategoryCrudController::class;
    }

    protected function getDashboardFqcn(): string
    {
        return MenuDashboardController::class;
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->followRedirects();
    }

    public function testMainMenuExists(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // main menu should exist
        $mainMenu = $crawler->filter('#main-menu');
        static::assertCount(1, $mainMenu);
    }

    public function testMenuContainsGroupsWithUnorderedLists(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // menu items are chunked into groups, each with its own list
        $menuGroups = $crawler->filter('#main-menu .ea-sidebar-group');
        static::assertGreaterThan(0, $menuGroups->count());

        $menuLists = $crawler->filter('#main-menu ul.ea-sidebar-group-items');
        static::assertSame($menuGroups->count(), $menuLists->count());
    }

    public function testDashboardLinkIsRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // dashboard link should be present
        $dashboardLink = $crawler->filter('.ea-sidebar-item a.ea-sidebar-item-link');
        static::assertGreaterThan(0, $dashboardLink->count());

        $html = $crawler->html();
        static::assertStringContainsString('Dashboard', $html);
    }

    public function testMenuSectionsAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // menu sections should be present as group labels
        $sectionHeaders = $crawler->filter('.ea-sidebar-group-label');
        static::assertGreaterThan(0, $sectionHeaders->count());

        // check for specific section labels
        $html = $crawler->html();
        static::assertStringContainsString('Content Management', $html);
        static::assertStringContainsString('Advanced', $html);
        static::assertStringContainsString('External Links', $html);
    }

    public function testCrudMenuItemsAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        $html = $crawler->html();
        static::assertStringContainsString('Categories', $html);
        static::assertStringContainsString('Blog Posts', $html);
    }

    public function testLinkToMenuItemsAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // the linkTo(BlogPostCrudController::class, 'Blog Posts', ...) item should render
        $html = $crawler->html();
        static::assertStringContainsString('Blog Posts', $html);

        // the linkTo(CategoryCrudController::class) item (auto-derived label) should also render
        // and its link should be clickable (have an href)
        $menuLinks = $crawler->filter('.ea-sidebar-item a.ea-sidebar-item-link');
        $hasLinkToItem = false;
        foreach ($menuLinks as $link) {
            $href = $link->getAttribute('href');
            if ('' !== $href && '#' !== $href) {
                $hasLinkToItem = true;
                break;
            }
        }
        static::assertTrue($hasLinkToItem, 'linkTo() menu items should generate valid URLs');
    }

    public function testLinkToMenuItemKeepsCustomQueryParameters(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // the "Categories" linkTo() menu item has a custom query parameter set via setQueryParameter()
        $categoriesLink = $crawler->filter('.ea-sidebar-item a.ea-sidebar-item-link')->reduce(
            static fn ($node) => str_contains($node->text(), 'Categories')
        );
        static::assertGreaterThan(0, $categoriesLink->count());
        static::assertStringContainsString('custom_param=custom_value', $categoriesLink->first()->attr('href'));
    }

    public function testLinkToMenuItemBadgeIsRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // the linkTo() Blog Posts item has a badge with 'New'
        $badges = $crawler->filter('.ea-sidebar-badge');
        static::assertGreaterThan(0, $badges->count());

        $html = $crawler->html();
        static::assertStringContainsString('New', $html);
    }

    public function testMenuItemBadgesAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // badge should be present for Blog Posts
        $badges = $crawler->filter('.ea-sidebar-badge');
        static::assertGreaterThan(0, $badges->count());

        $html = $crawler->html();
        static::assertStringContainsString('New', $html);
    }

    public function testSubmenusAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // submenu items should be present
        $submenus = $crawler->filter('.ea-sidebar-item.has-submenu');
        static::assertGreaterThan(0, $submenus->count());

        // check for submenu labels
        $html = $crawler->html();
        static::assertStringContainsString('Reports', $html);
        static::assertStringContainsString('Settings', $html);
    }

    public function testSubmenuItemsAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // submenu list should exist
        $submenuLists = $crawler->filter('.ea-sidebar-submenu');
        static::assertGreaterThan(0, $submenuLists->count());

        // check for specific submenu items
        $html = $crawler->html();
        static::assertStringContainsString('Sales Report', $html);
        static::assertStringContainsString('Traffic Report', $html);
        static::assertStringContainsString('General', $html);
        static::assertStringContainsString('Security', $html);
    }

    public function testExternalLinksAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // external links should be present
        $html = $crawler->html();
        static::assertStringContainsString('Symfony', $html);
        static::assertStringContainsString('https://symfony.com', $html);
        static::assertStringContainsString('EasyAdmin Docs', $html);
    }

    public function testExternalLinksHaveTargetBlank(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // external Symfony link should have target="_blank"
        $symfonyLink = $crawler->filter('a[href="https://symfony.com"]');
        static::assertGreaterThan(0, $symfonyLink->count());
        static::assertSame('_blank', $symfonyLink->attr('target'));
    }

    public function testMenuIconsAreRendered(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // menu icons should be present
        $menuIcons = $crawler->filter('.ea-sidebar-item-icon');
        static::assertGreaterThan(0, $menuIcons->count());
    }

    public function testActiveMenuItemClassCanBeApplied(): void
    {
        // navigate to the Category CRUD index page using the helper method
        // this uses the MenuDashboardController context
        $crawler = $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful();

        // test that menu items can potentially have the active class
        // the active class is applied server-side based on URL matching
        // here we verify the menu structure supports the active state
        $menuItems = $crawler->filter('li.ea-sidebar-item');
        static::assertGreaterThan(0, $menuItems->count());

        // check that the menu contains items that can receive the 'is-active' class
        $html = $crawler->html();
        static::assertStringContainsString('ea-sidebar-item', $html);
    }

    public function testMenuItemsAreClickable(): void
    {
        // menu is displayed on CRUD pages, not on dashboard welcome page
        $crawler = $this->client->request('GET', $this->generateIndexUrl());
        static::assertResponseIsSuccessful();

        // find menu item links (items with submenus render a <button> instead of a link)
        $menuLinks = $crawler->filter('.ea-sidebar-item a.ea-sidebar-item-link');
        static::assertGreaterThan(0, $menuLinks->count());

        // verify each has an href attribute
        foreach ($menuLinks as $link) {
            $href = $link->getAttribute('href');
            static::assertNotEmpty($href);
        }
    }

    public function testMenuItemLabelsHaveCorrectClass(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // menu item labels should have the correct class
        $menuLabels = $crawler->filter('.ea-sidebar-item-label');
        static::assertGreaterThan(0, $menuLabels->count());
    }

    public function testSubmenuToggleIconExists(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // submenu chevron icons should exist
        $toggleIcons = $crawler->filter('.ea-sidebar-item-chevron');
        static::assertGreaterThan(0, $toggleIcons->count());
    }

    public function testMenuStructureHasCorrectHierarchy(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // check that submenu items are nested inside their parent
        $submenuParents = $crawler->filter('.ea-sidebar-item.has-submenu .ea-sidebar-submenu ul.ea-sidebar-submenu-items');
        static::assertGreaterThan(0, $submenuParents->count());
    }

    public function testSectionHeadersHaveCorrectClass(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // section headers should be rendered as group labels
        $sectionContents = $crawler->filter('.ea-sidebar-group .ea-sidebar-group-label');
        static::assertGreaterThan(0, $sectionContents->count());
    }

    public function testMenuItemLinksHaveCorrectClass(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // regular menu item links should have the ea-sidebar-item-link class
        $menuLinks = $crawler->filter('a.ea-sidebar-item-link');
        static::assertGreaterThan(0, $menuLinks->count());
    }

    public function testActiveMenuItemHasAriaCurrent(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // the link of the active menu item announces itself to assistive technologies
        $activeLinks = $crawler->filter('.ea-sidebar-item.is-active > a[aria-current="page"]');
        static::assertGreaterThan(0, $activeLinks->count());
    }

    public function testSubmenuToggleIsNotClickable(): void
    {
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        // items with submenus render a <button> toggle (not a link) with the aria-expanded attribute
        $submenuToggles = $crawler->filter('.ea-sidebar-item.has-submenu > button.ea-sidebar-item-link[aria-expanded]');
        static::assertGreaterThan(0, $submenuToggles->count());
    }

    public function testKeepOpenSubmenuIsAlwaysExpanded(): void
    {
        // the current URL (Category index) doesn't match any sub-item of the
        // 'Settings' submenu, which uses keepOpen()
        $crawler = $this->client->request('GET', $this->generateIndexUrl());

        static::assertResponseIsSuccessful();

        $keepOpenSubmenus = $crawler->filter('.ea-sidebar-item.has-submenu.is-kept-open.is-expanded');
        static::assertCount(1, $keepOpenSubmenus);
        static::assertStringContainsString('Settings', $keepOpenSubmenus->text());

        // keep-open parents are not a disclosure widget, so they don't announce aria-expanded
        $keepOpenToggles = $crawler->filter('.ea-sidebar-item.is-kept-open > button[aria-expanded]');
        static::assertCount(0, $keepOpenToggles);

        // regular submenus ('Reports') keep working as collapsible toggles
        $regularToggles = $crawler->filter('.ea-sidebar-item.has-submenu:not(.is-kept-open) > button[aria-expanded="false"]');
        static::assertGreaterThan(0, $regularToggles->count());
    }
}
