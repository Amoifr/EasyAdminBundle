<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Twig\Component;

use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\BadgeVariant;
use EasyCorp\Bundle\EasyAdminBundle\Twig\Component\Option\Radius;

/**
 * Renders a badge: a small, colored label used for statuses, counts and tags.
 * Inspired by https://ui.shadcn.com/docs/components/badge.
 */
class Badge
{
    public ?BadgeVariant $variant = BadgeVariant::Secondary;
    public ?string $icon = null;
    public ?string $endIcon = null;
    public string $size = 'md';
    public ?Radius $radius = null;
    private ?string $customVariant = null;

    public function mount(string|BadgeVariant|null $variant = BadgeVariant::Secondary, string|Radius|null $radius = null): void
    {
        if ($variant instanceof BadgeVariant) {
            $this->variant = $variant;
        } elseif (null === $variant || '' === $variant) {
            // an explicit empty/null variant renders a badge with no variant CSS class
            // (e.g. menu badges that only define an arbitrary inline style)
            $this->variant = null;
        } else {
            $resolved = BadgeVariant::tryFrom($variant);
            $this->variant = $resolved;
            if (null === $resolved) {
                // unknown variants (e.g. 'boolean-true', 'currency', 'language') are
                // passed through as a custom 'badge-{variant}' CSS class
                $this->customVariant = $variant;
            }
        }

        if ($radius instanceof Radius || null === $radius) {
            $this->radius = $radius;
        } else {
            $this->radius = Radius::tryFrom($radius);
        }
    }

    public function getDefaultCssClass(): string
    {
        $cssClasses = ['badge'];

        if (null !== $this->variant) {
            $cssClasses[] = $this->variant->asCssClass();
        } elseif (null !== $this->customVariant) {
            $cssClasses[] = 'badge-'.$this->customVariant;
        }

        if ('sm' === $this->size) {
            $cssClasses[] = 'badge-sm';
        }

        // when no radius is set, the badge inherits the project's default border-radius
        if (null !== $this->radius) {
            $cssClasses[] = $this->radius->cssClass();
        }

        return implode(' ', $cssClasses);
    }
}
