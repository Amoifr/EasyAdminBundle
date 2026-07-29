<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Dto;

/**
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final readonly class ThemeDto
{
    private const GRAY_STEPS = ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'];

    public function __construct(
        private ?string $primaryColor = null,
        private ?string $primaryForeground = null,
        private ?string $darkPrimaryColor = null,
        private ?string $darkPrimaryForeground = null,
        private ?string $radius = null,
        private ?string $spacing = null,
        private ?string $grayRampPrefix = null,
        private ?string $darkGrayRampPrefix = null,
    ) {
    }

    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    public function getPrimaryForeground(): ?string
    {
        return $this->primaryForeground;
    }

    public function getDarkPrimaryColor(): ?string
    {
        return $this->darkPrimaryColor;
    }

    public function getDarkPrimaryForeground(): ?string
    {
        return $this->darkPrimaryForeground;
    }

    public function getRadius(): ?string
    {
        return $this->radius;
    }

    public function getSpacing(): ?string
    {
        return $this->spacing;
    }

    public function getGrayRampPrefix(): ?string
    {
        return $this->grayRampPrefix;
    }

    public function getDarkGrayRampPrefix(): ?string
    {
        return $this->darkGrayRampPrefix;
    }

    /**
     * The CSS variable overrides to emit in the backend <head>, grouped by
     * target selector: 'common' variables apply to both color schemes
     * (':root, .ea-dark-scheme') and 'dark' variables only to '.ea-dark-scheme'.
     *
     * @return array{common: array<string, string>, dark: array<string, string>}
     */
    public function getCssVariables(): array
    {
        $common = [];
        $dark = [];

        if (null !== $this->primaryColor) {
            $common['--ea-primary'] = $this->primaryColor;
            $common['--ea-primary-foreground'] = $this->primaryForeground;
        }

        if (null !== $this->darkPrimaryColor) {
            $dark['--ea-primary'] = $this->darkPrimaryColor;
            $dark['--ea-primary-foreground'] = $this->darkPrimaryForeground;
        }

        if (null !== $this->radius) {
            $common['--ea-radius'] = $this->radius;
        }

        if (null !== $this->spacing) {
            $common['--ea-spacing'] = $this->spacing;
        }

        if (null !== $this->grayRampPrefix) {
            foreach (self::GRAY_STEPS as $step) {
                $common['--gray-'.$step] = sprintf('var(%s%s)', $this->grayRampPrefix, $step);
            }
        }

        if (null !== $this->darkGrayRampPrefix) {
            foreach (self::GRAY_STEPS as $step) {
                $dark['--gray-'.$step] = sprintf('var(%s%s)', $this->darkGrayRampPrefix, $step);
            }
        }

        return ['common' => $common, 'dark' => $dark];
    }

    public function hasCssVariables(): bool
    {
        $cssVariables = $this->getCssVariables();

        return [] !== $cssVariables['common'] || [] !== $cssVariables['dark'];
    }
}
