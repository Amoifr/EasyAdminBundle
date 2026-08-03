<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Controller\Synthetic;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Tests\Functional\Apps\DefaultApp\Entity\Synthetic\FormTestEntity;
use function Symfony\Component\Translation\t;

/**
 * CrudController for testing the ->prepend() and ->append() methods of fields
 * and how those addons are rendered in the form inputs.
 *
 * @extends AbstractCrudController<FormTestEntity>
 */
class FormFieldAddonsSyntheticCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FormTestEntity::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            // a control field with no addons
            TextField::new('street'),

            // addon defined as a simple text string
            TextField::new('name')->prepend('https://'),

            // addon defined as a text string with HTML contents
            TextField::new('email')->append('<b>@example.com</b>'),

            // addon defined as a Translatable object
            IntegerField::new('priority')->prepend(t('Priority Addon Lorem Ipsum')),

            // addon defined as an icon only
            TextField::new('phone')->append(icon: 'internal:search'),

            // addon combining an icon and a text content
            TextField::new('city')->prepend('Search', icon: 'internal:search'),

            // field using both prepend and append at the same time
            TextField::new('country')->prepend('https://')->append('.com'),

            // the currency symbol is rendered with the same addon markup and
            // it's compatible with user-defined addons
            MoneyField::new('priceInCents')->setCurrency('USD')->prepend('Fee'),

            // the percent symbol is rendered with the same addon markup
            PercentField::new('score'),

            // fields not rendered as single-line inputs ignore the addons
            TextareaField::new('description')->prepend('ignored'),
            ArrayField::new('tags')->prepend('ignored'),
        ];
    }
}
