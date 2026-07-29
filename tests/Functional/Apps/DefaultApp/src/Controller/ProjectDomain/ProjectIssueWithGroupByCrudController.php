<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\ProjectDomain;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\Project;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\ProjectDomain\ProjectIssue;

/**
 * @extends AbstractCrudController<ProjectIssue>
 */
class ProjectIssueWithGroupByCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectIssue::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            // test group_by with a callable (returning null leaves the entry ungrouped)
            AssociationField::new('project')
                ->autocomplete()
                ->setFormTypeOption('group_by', static fn (Project $p): ?string => $p->isInternal() ? 'Internal' : null),
            // test group_by with a property path
            AssociationField::new('assignedDeveloper')
                ->autocomplete()
                ->setFormTypeOption('group_by', 'name'),
        ];
    }
}
