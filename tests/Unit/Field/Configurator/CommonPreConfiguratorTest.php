<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Field\Configurator;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Orm\NestedAssociationResolverInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Translation\EntityTranslationIdGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ResolvedPropertyDto;
use EasyCorp\Bundle\EasyAdminBundle\Factory\EntityFactory;
use EasyCorp\Bundle\EasyAdminBundle\Field\Configurator\CommonPreConfigurator;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Field\AbstractFieldTest;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CommonPreConfiguratorTest extends AbstractFieldTest
{
    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();
        /** @var PropertyAccessorInterface $propertyAccessor */
        $container = self::$kernel->getContainer()->get('test.service_container');
        $propertyAccessor = $container->get(PropertyAccessorInterface::class);
        $entityFactory = $container->get(EntityFactory::class);
        $entityTranslationIdGenerator = $container->get(EntityTranslationIdGeneratorInterface::class);
        $associationResolver = $container->get(NestedAssociationResolverInterface::class);
        $this->configurator = new CommonPreConfigurator($propertyAccessor, $entityFactory, $entityTranslationIdGenerator, $associationResolver);
    }

    public function testShouldKeepExistingValue(): void
    {
        $field = Field::new('foo')->setValue('bar');

        $this->assertSame('bar', $this->configure($field)->getValue());
    }

    public function testShouldKeepExistingFormattedValue(): void
    {
        $field = Field::new('foo')->setFormattedValue('bar');

        $this->assertSame('bar', $this->configure($field)->getFormattedValue());
    }

    public function testNestedPropertyEndingInSingleValuedAssociationIsSortableByDefault(): void
    {
        // e.g. AssociationField::new('customer.country') where 'country' is a to-one
        // association: sortable by default, like single-level association fields
        $this->configurator = $this->createConfiguratorForAssociationLeaf(isSingleValuedLeaf: true);

        $field = Field::new('customer.country');

        $this->assertTrue($this->configure($field)->isSortable());
    }

    public function testNestedPropertyEndingInToManyAssociationIsNotSortableByDefault(): void
    {
        $this->configurator = $this->createConfiguratorForAssociationLeaf(isSingleValuedLeaf: false);

        $field = Field::new('customer.orders');

        $this->assertFalse($this->configure($field)->isSortable());
    }

    /**
     * Creates the configurator with a resolver stub that behaves like the real one does
     * for a nested path ending at an association: the default resolve call (which requires
     * the leaf to be a field) throws, and the resolve call allowing an association leaf
     * returns the leaf's parent entity, whose metadata tells whether the leaf is single-valued.
     */
    private function createConfiguratorForAssociationLeaf(bool $isSingleValuedLeaf): CommonPreConfigurator
    {
        $associationResolver = $this->createMock(NestedAssociationResolverInterface::class);
        $associationResolver->method('resolveNestedAssociations')->willReturnCallback(
            function ($queryBuilder, EntityDto $entityDto, string $propertyName, bool $mustEndWithAssociation = false) use ($isSingleValuedLeaf): ResolvedPropertyDto {
                if (!$mustEndWithAssociation) {
                    throw new \InvalidArgumentException(sprintf('The "%s" property ends with an association.', $propertyName));
                }

                $leafName = substr($propertyName, strrpos($propertyName, '.') + 1);
                $classMetadata = $this->createMock(ClassMetadata::class);
                $classMetadata->method('isSingleValuedAssociation')->with($leafName)->willReturn($isSingleValuedLeaf);

                return new ResolvedPropertyDto(new EntityDto('App\Entity\Customer', $classMetadata), null, $leafName);
            }
        );

        $container = self::$kernel->getContainer()->get('test.service_container');

        return new CommonPreConfigurator(
            $container->get(PropertyAccessorInterface::class),
            $container->get(EntityFactory::class),
            $container->get(EntityTranslationIdGeneratorInterface::class),
            $associationResolver,
        );
    }
}
