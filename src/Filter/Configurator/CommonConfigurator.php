<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Filter\Configurator;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterConfiguratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Translation\EntityTranslationIdGeneratorInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDto;
use EasyCorp\Bundle\EasyAdminBundle\Generator\LabelGenerator;
use Symfony\Component\Translation\TranslatableMessage;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class CommonConfigurator implements FilterConfiguratorInterface
{
    public function __construct(
        private EntityTranslationIdGeneratorInterface $entityTranslationIdGenerator,
    ) {
    }

    public function supports(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): bool
    {
        return true;
    }

    public function configure(FilterDto $filterDto, ?FieldDto $fieldDto, EntityDto $entityDto, AdminContext $context): void
    {
        if (null === $filterDto->getLabel()) {
            $fieldLabel = $fieldDto?->getLabel();
            if ($fieldLabel instanceof TranslatableMessage) {
                $fieldLabel = $fieldLabel->getMessage();
            }

            if (null !== $fieldLabel) {
                $label = $fieldLabel;
            } elseif ($context->isUseEntityTranslations()) {
                $label = $this->entityTranslationIdGenerator->generateForProperty($entityDto->getFqcn(), $filterDto->getProperty());
            } else {
                $label = LabelGenerator::humanize($filterDto->getProperty());
            }

            $filterDto->setLabel($label);
        }
    }
}
