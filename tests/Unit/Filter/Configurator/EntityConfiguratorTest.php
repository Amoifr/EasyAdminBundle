<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Filter\Configurator;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\CrudContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\DashboardContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\I18nContext;
use EasyCorp\Bundle\EasyAdminBundle\Context\RequestContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\NestedAssociationResolverInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ResolvedPropertyDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\Configurator\EntityConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class EntityConfiguratorTest extends TestCase
{
    private NestedAssociationResolverInterface $associationResolver;

    protected function setUp(): void
    {
        $this->associationResolver = $this->createMock(NestedAssociationResolverInterface::class);
    }

    public function testConfigureResolvesNestedAssociation(): void
    {
        $adminUrlGenerator = $this->createMock(AdminUrlGeneratorInterface::class);

        $configurator = new EntityConfigurator($adminUrlGenerator, $this->associationResolver);

        $filterDto = new FilterDto();
        $filterDto->setProperty('parent.category');

        $rootEntityDto = new EntityDto('App\Entity\Parent', $this->createMock(ClassMetadata::class));

        $parentCategoryClassMetadata = $this->createMock(ClassMetadata::class);
        $parentCategoryClassMetadata->method('hasAssociation')
            ->with('category')
            ->willReturn(true);
        $parentCategoryClassMetadata->expects(self::once())
            ->method('getAssociationTargetClass')
            ->with('category')
            ->willReturn('App\Entity\Category');

        $categoryEntityDto = new EntityDto('App\Entity\Category', $parentCategoryClassMetadata);

        $this->associationResolver->expects(self::once())
            ->method('resolveNestedAssociations')
            ->with(null, $rootEntityDto, 'parent.category', true)
            ->willReturn(new ResolvedPropertyDto($categoryEntityDto, null, 'category'));

        $configurator->configure($filterDto, null, $rootEntityDto, new AdminContext(
            RequestContext::forTesting(),
            CrudContext::forTesting(),
            DashboardContext::forTesting(),
            I18nContext::forTesting(),
        ));

        self::assertSame('App\Entity\Category', $filterDto->getFormTypeOption('value_type_options.class'));
    }

    public function testConfigureIgnoresNonAssociationProperties(): void
    {
        $adminUrlGenerator = $this->createMock(AdminUrlGeneratorInterface::class);

        $configurator = new EntityConfigurator($adminUrlGenerator, $this->associationResolver);

        $filterDto = new FilterDto();
        $filterDto->setProperty('status');

        $classMetadata = $this->createMock(ClassMetadata::class);
        $classMetadata->method('hasAssociation')
            ->with('status')
            ->willReturn(false);
        $classMetadata->expects(self::never())->method('getAssociationTargetClass');

        $rootEntityDto = new EntityDto('App\Entity\Post', $classMetadata);

        $this->associationResolver->method('resolveNestedAssociations')
            ->willReturn(new ResolvedPropertyDto($rootEntityDto, null, 'status'));

        $configurator->configure($filterDto, null, $rootEntityDto, new AdminContext(
            RequestContext::forTesting(),
            CrudContext::forTesting(),
            DashboardContext::forTesting(),
            I18nContext::forTesting(),
        ));

        self::assertNull($filterDto->getFormTypeOption('value_type_options.class'));
    }
}
