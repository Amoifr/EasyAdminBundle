<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Filter\Configurator;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\DashboardContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\I18nContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Translation\EntityTranslationIdGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Configurator\CommonConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatableMessage;

class CommonConfiguratorTest extends TestCase
{
    private EntityDto $entityDto;

    protected function setUp(): void
    {
        $metadata = new ClassMetadata('App\Entity\Product');
        $metadata->setIdentifier(['id']);
        $this->entityDto = new EntityDto('App\Entity\Product', $metadata);
    }

    public function testSupportsAllFilters(): void
    {
        $configurator = $this->createConfigurator();
        $filter = TextFilter::new('name');
        $filterDto = $filter->getAsDto();
        $adminContext = $this->createAdminContext();

        $this->assertTrue($configurator->supports($filterDto, null, $this->entityDto, $adminContext));
    }

    public function testConfigureUsesFieldLabelWhenAvailable(): void
    {
        $configurator = $this->createConfigurator();
        $filter = TextFilter::new('name');
        $filterDto = $filter->getAsDto();

        $field = TextField::new('name', 'Product Name');
        $fieldDto = $field->getAsDto();

        $adminContext = $this->createAdminContext();

        $configurator->configure($filterDto, $fieldDto, $this->entityDto, $adminContext);

        $this->assertSame('Product Name', $filterDto->getLabel());
    }

    public function testConfigureUsesFieldTranslatableMessageLabel(): void
    {
        $configurator = $this->createConfigurator();
        $filter = TextFilter::new('name');
        $filterDto = $filter->getAsDto();

        $field = TextField::new('name');
        $fieldDto = $field->getAsDto();
        $fieldDto->setLabel(new TranslatableMessage('product.name'));

        $adminContext = $this->createAdminContext();

        $configurator->configure($filterDto, $fieldDto, $this->entityDto, $adminContext);

        $this->assertSame('product.name', $filterDto->getLabel());
    }

    public function testConfigureHumanizesLabelWhenFieldIsNullAndEntityTranslationsDisabled(): void
    {
        $configurator = $this->createConfigurator();
        $filter = TextFilter::new('productName');
        $filterDto = $filter->getAsDto();

        $adminContext = $this->createAdminContext(useEntityTranslations: false);

        $configurator->configure($filterDto, null, $this->entityDto, $adminContext);

        $this->assertSame('Product Name', $filterDto->getLabel());
    }

    public function testConfigureUsesEntityTranslationWhenFieldIsNullAndEntityTranslationsEnabled(): void
    {
        $expectedTranslationId = 'entities.App\\Entity\\Product.properties.productName';

        $generator = $this->createMock(EntityTranslationIdGeneratorInterface::class);
        $generator->expects($this->once())
            ->method('generateForProperty')
            ->with('App\Entity\Product', 'productName')
            ->willReturn($expectedTranslationId);

        $configurator = new CommonConfigurator($generator);
        $filter = TextFilter::new('productName');
        $filterDto = $filter->getAsDto();

        $adminContext = $this->createAdminContext(useEntityTranslations: true);

        $configurator->configure($filterDto, null, $this->entityDto, $adminContext);

        $this->assertSame($expectedTranslationId, $filterDto->getLabel());
    }

    public function testConfigureDoesNotOverrideExplicitFilterLabel(): void
    {
        $configurator = $this->createConfigurator();
        $filter = TextFilter::new('name')->setLabel('Custom Label');
        $filterDto = $filter->getAsDto();

        $adminContext = $this->createAdminContext();

        $configurator->configure($filterDto, null, $this->entityDto, $adminContext);

        $this->assertSame('Custom Label', $filterDto->getLabel());
    }

    private function createConfigurator(): CommonConfigurator
    {
        $generator = $this->createMock(EntityTranslationIdGeneratorInterface::class);

        return new CommonConfigurator($generator);
    }

    private function createAdminContext(bool $useEntityTranslations = false): AdminContext
    {
        $dashboardDto = Dashboard::new()->getAsDto();
        if ($useEntityTranslations) {
            $dashboardDto->setUseEntityTranslations(true);
        }

        return AdminContext::forTesting(
            RequestContext::forTesting(new Request()),
            null,
            DashboardContext::forTesting($dashboardDto),
            I18nContext::forTesting('en', 'ltr')
        );
    }
}
