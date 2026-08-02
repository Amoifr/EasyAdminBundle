<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Synthetic;

use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\Project;

/**
 * CRUD controller for testing an AssociationField whose property is a nested
 * association path ending at an association ('latestRelease.category'). The field
 * defines setSortProperty('name'), so sorting orders by the related category's name.
 * It doesn't call setCrudController(): the cell must auto-link to the CRUD controller
 * of the entity at the end of the path (ProjectReleaseCategory).
 *
 * @extends AbstractCrudController<Project>
 */
class SortNestedAssociationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
        yield AssociationField::new('latestRelease.category')
            ->setSortProperty('name');
    }
}
