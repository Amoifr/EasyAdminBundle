<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Dto;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class FilterDataDto
{
    private int $index;
    private string $entityAlias;
    private ?ResolvedPropertyDto $resolvedProperty;
    private FilterDto $filterDto;
    private string $comparison;
    private mixed $value;
    private mixed $value2;

    private function __construct()
    {
    }

    /**
     * @param array{comparison: string, value: mixed, value2?: mixed} $formData
     */
    public static function new(int $index, FilterDto $filterDto, string $entityAlias, array $formData, ?ResolvedPropertyDto $resolvedProperty = null): self
    {
        $filterData = new self();
        $filterData->index = $index;
        $filterData->filterDto = $filterDto;
        $filterData->entityAlias = $entityAlias;
        $filterData->resolvedProperty = $resolvedProperty;
        $filterData->comparison = $formData['comparison'];
        $filterData->value = $formData['value'];
        $filterData->value2 = $formData['value2'] ?? null;

        return $filterData;
    }

    public function getEntityAlias(): string
    {
        return $this->resolvedProperty?->getEntityAlias() ?? $this->entityAlias;
    }

    /**
     * Returns the entity that holds the property targeted by the filter. For nested
     * properties (e.g. 'author.name') this is the associated entity (e.g. Author),
     * not the entity of the current CRUD controller. It returns null when the
     * property couldn't be resolved (e.g. custom filters with unmapped properties).
     */
    public function getEntityDto(): ?EntityDto
    {
        return $this->resolvedProperty?->getEntityDto();
    }

    public function getProperty(): string
    {
        return $this->resolvedProperty?->getPropertyName() ?? $this->filterDto->getProperty();
    }

    public function getFormTypeOption(string $optionName): mixed
    {
        return $this->filterDto->getFormTypeOption($optionName);
    }

    public function getComparison(): string
    {
        return $this->comparison;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getValue2(): mixed
    {
        return $this->value2;
    }

    public function getParameterName(): string
    {
        return sprintf('%s_%d', str_replace('.', '_', $this->filterDto->getProperty()), $this->index);
    }

    public function getParameter2Name(): string
    {
        return sprintf('%s_%d', str_replace('.', '_', $this->filterDto->getProperty()), $this->index + 1);
    }
}
