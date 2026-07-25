<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Dto;

/**
 * The result of resolving a property path that may traverse Doctrine
 * associations and/or embeddables (e.g. 'author.address.country'): the entity
 * that ultimately holds the property, the DQL alias of that entity in the
 * query and the name of the property inside that entity.
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final readonly class ResolvedPropertyDto
{
    public function __construct(
        private EntityDto $entityDto,
        private ?string $entityAlias,
        private string $propertyName,
    ) {
    }

    public function getEntityDto(): EntityDto
    {
        return $this->entityDto;
    }

    /**
     * It returns null when the property was resolved without a query builder,
     * because then no JOIN clauses are created and there's no DQL alias.
     */
    public function getEntityAlias(): ?string
    {
        return $this->entityAlias;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
