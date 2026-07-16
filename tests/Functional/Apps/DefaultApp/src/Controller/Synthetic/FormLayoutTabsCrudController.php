<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Synthetic;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\Synthetic\FormTestEntity;

/**
 * CrudController for testing form tabs layout.
 *
 * @extends AbstractCrudController<FormTestEntity>
 */
class FormLayoutTabsCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FormTestEntity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        // tab 1: Basic Information
        yield FormField::addTab('Basic Info', 'fa fa-info-circle');
        yield IdField::new('id')->hideOnForm();
        yield TextField::new('name');
        yield TextareaField::new('description');

        // tab 2: Contact Information (static badge)
        yield FormField::addTab('Contact', 'fa fa-phone')->setBadge(42, 'info');
        yield EmailField::new('email');
        yield TelephoneField::new('phone');

        // tab 3: Address (dynamic badge computed from the entity instance)
        yield FormField::addTab('Address', 'fa fa-map-marker')
            ->setBadge(static fn (FormTestEntity $entity): int => \strlen((string) $entity->getName()), 'warning');
        yield TextField::new('street');
        yield TextField::new('city');
        yield TextField::new('postalCode');
        yield CountryField::new('country');

        // tab 4: Settings
        yield FormField::addTab('Settings', 'fa fa-cog');
        yield BooleanField::new('isActive');
        yield DateTimeField::new('createdAt');
        yield ArrayField::new('tags');
        yield IntegerField::new('priority');
    }
}
