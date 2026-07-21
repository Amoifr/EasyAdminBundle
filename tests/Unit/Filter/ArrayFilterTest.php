<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Filter;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ArrayFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use PHPUnit\Framework\TestCase;

class ArrayFilterTest extends TestCase
{
    public function testApplyWithoutFieldDto(): void
    {
        // the filtered property is not necessarily configured as a displayed field of the
        // current page, so the FieldDto argument can be null (see EntityRepository::addFilterClause())
        $queryBuilder = $this->createQueryBuilder();
        $filterDataDto = $this->createFilterDataDto(['foo']);

        ArrayFilter::new('tags')->apply($queryBuilder, $filterDataDto, null, $this->createEntityDto());

        $parameters = $queryBuilder->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertSame('%foo%', $parameters->first()->getValue());
    }

    public function testApplyWithSimpleArrayFieldUsesQuotedPattern(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $filterDataDto = $this->createFilterDataDto(['foo']);

        $fieldDto = TextField::new('tags')->getAsDto();
        $fieldDto->setDoctrineMetadata(['type' => Types::SIMPLE_ARRAY]);

        ArrayFilter::new('tags')->apply($queryBuilder, $filterDataDto, $fieldDto, $this->createEntityDto());

        $parameters = $queryBuilder->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertSame('%"foo"%', $parameters->first()->getValue());
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return new QueryBuilder($this->createMock(EntityManagerInterface::class));
    }

    /**
     * @param list<string> $value
     */
    private function createFilterDataDto(array $value): FilterDataDto
    {
        $filterDto = ArrayFilter::new('tags')->getAsDto();

        return FilterDataDto::new(0, $filterDto, 'entity', [
            'comparison' => ComparisonType::CONTAINS,
            'value' => $value,
        ]);
    }

    private function createEntityDto(): EntityDto
    {
        return new EntityDto(\stdClass::class, new ClassMetadata(\stdClass::class));
    }
}
