<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Contracts\Field;

use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
interface FieldInterface
{
    /**
     * These options store the content rendered before/after the field's form input.
     * At config time they hold the raw values passed to prepend()/append();
     * after the field configurators run, they hold the normalized array
     * ['icon' => ?string, 'html' => ?TranslatableInterface] read by form templates.
     */
    public const OPTION_PREPEND = 'prepend';
    public const OPTION_APPEND = 'append';

    public static function new(string $propertyName, TranslatableInterface|string|bool|null $label = null): self;

    public function getAsDto(): FieldDto;

    public function __clone(): void;
}
