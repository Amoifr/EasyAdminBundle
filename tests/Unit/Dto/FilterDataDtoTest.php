<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Dto;

use Doctrine\ORM\Mapping\ClassMetadata;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\ResolvedPropertyDto;
use PHPUnit\Framework\TestCase;

class FilterDataDtoTest extends TestCase
{
    public function testNewWithoutResolvedProperty(): void
    {
        $filterDto = new FilterDto();
        $filterDto->setProperty('author.name');

        $filterDataDto = FilterDataDto::new(3, $filterDto, 'entity', ['comparison' => '=', 'value' => 'foo']);

        $this->assertSame('entity', $filterDataDto->getEntityAlias());
        $this->assertSame('author.name', $filterDataDto->getProperty());
        $this->assertSame('author_name_3', $filterDataDto->getParameterName());
        $this->assertSame('author_name_4', $filterDataDto->getParameter2Name());
        $this->assertSame('=', $filterDataDto->getComparison());
        $this->assertSame('foo', $filterDataDto->getValue());
        $this->assertNull($filterDataDto->getValue2());
    }

    public function testNewWithResolvedProperty(): void
    {
        $filterDto = new FilterDto();
        $filterDto->setProperty('author.name');

        $authorEntityDto = new EntityDto('App\Entity\User', $this->createMock(ClassMetadata::class));
        $resolvedProperty = new ResolvedPropertyDto($authorEntityDto, 'author', 'name');

        $filterDataDto = FilterDataDto::new(0, $filterDto, 'entity', ['comparison' => '=', 'value' => 'foo'], $resolvedProperty);

        $this->assertSame('author', $filterDataDto->getEntityAlias());
        $this->assertSame('name', $filterDataDto->getProperty());
        // parameter names always derive from the configured property, so they don't
        // change depending on whether the property was resolved or not
        $this->assertSame('author_name_0', $filterDataDto->getParameterName());
        $this->assertSame('author_name_1', $filterDataDto->getParameter2Name());
    }
}
