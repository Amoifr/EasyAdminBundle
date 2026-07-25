<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Filter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ResolvedPropertyDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\ComparisonType;
use PHPUnit\Framework\TestCase;

class EntityFilterTest extends TestCase
{
    public function testApplyWithNestedToManyAssociation(): void
    {
        $queryBuilder = new QueryBuilder($this->createMock(EntityManagerInterface::class));
        $queryBuilder->select('entity')->from(\stdClass::class, 'entity');

        // when filtering on 'author.books', the to-one/to-many check must use the metadata
        // of the resolved entity (Author, which defines the to-many 'books' association)
        // instead of the metadata of the root entity, which doesn't define 'books' at all
        $authorClassMetadata = new ClassMetadata(\stdClass::class);
        $authorClassMetadata->mapOneToMany(['fieldName' => 'books', 'targetEntity' => \stdClass::class, 'mappedBy' => 'author']);
        $authorEntityDto = new EntityDto(\stdClass::class, $authorClassMetadata);

        $rootEntityDto = new EntityDto(\stdClass::class, new ClassMetadata(\stdClass::class));

        $filter = EntityFilter::new('author.books');
        $filterDataDto = FilterDataDto::new(0, $filter->getAsDto(), 'entity', [
            'comparison' => ComparisonType::EQ,
            'value' => [42],
        ], new ResolvedPropertyDto($authorEntityDto, 'author', 'books'));

        $filter->apply($queryBuilder, $filterDataDto, null, $rootEntityDto);

        $joins = $queryBuilder->getDQLPart('join');
        $this->assertArrayHasKey('entity', $joins);
        $this->assertSame('author.books', $joins['entity'][0]->getJoin());
        $this->assertSame('ea_author_books_0', $joins['entity'][0]->getAlias());
        $this->assertStringContainsString('ea_author_books_0 = (:author_books_0)', (string) $queryBuilder->getDQLPart('where'));
        $this->assertSame([42], $queryBuilder->getParameter('author_books_0')->getValue());
    }
}
