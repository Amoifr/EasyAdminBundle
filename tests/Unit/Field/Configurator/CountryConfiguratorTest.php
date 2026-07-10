<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Field\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\CountryConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Field\AbstractFieldTest;

class CountryConfiguratorTest extends AbstractFieldTest
{
    protected function setUp(): void
    {
        self::bootKernel();

        /** @var CountryConfigurator $countryConfigurator */
        $countryConfigurator = static::getContainer()->get(CountryConfigurator::class);
        $this->configurator = $countryConfigurator;
    }

    public function testFormChoicesWithFlagAndName(): void
    {
        $fieldDto = $this->configure(CountryField::new('country'), pageName: Crud::PAGE_NEW);
        $choices = $fieldDto->getFormTypeOption('choices');

        $franceLabel = array_search('FR', $choices, true);
        $this->assertIsString($franceLabel);
        $this->assertStringContainsString('France', $franceLabel);
        $this->assertStringContainsString('country-flag-wrapper', $franceLabel);

        // each country must get its own label (the same template is rendered with different variables)
        $germanyLabel = array_search('DE', $choices, true);
        $this->assertIsString($germanyLabel);
        $this->assertStringContainsString('Germany', $germanyLabel);
        $this->assertNotSame($franceLabel, $germanyLabel);
    }

    public function testFormChoicesWithFlagOnly(): void
    {
        $fieldDto = $this->configure(CountryField::new('country')->showName(false), pageName: Crud::PAGE_NEW);
        $choices = $fieldDto->getFormTypeOption('choices');

        $franceLabel = array_search('FR', $choices, true);
        $this->assertIsString($franceLabel);
        // the country name only appears inside the SVG flag (for accessibility), not as a visible label
        $this->assertStringNotContainsString('<span>France</span>', $franceLabel);
        $this->assertStringContainsString('country-flag-wrapper', $franceLabel);
    }

    public function testFormChoicesWithNameOnly(): void
    {
        $fieldDto = $this->configure(CountryField::new('country')->showFlag(false), pageName: Crud::PAGE_NEW);
        $choices = $fieldDto->getFormTypeOption('choices');

        $this->assertSame('FR', $choices['France'] ?? null);
    }
}
