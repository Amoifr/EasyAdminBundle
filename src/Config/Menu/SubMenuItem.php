<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Config\Menu;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\MenuItemDto;
use Symfony\Contracts\Translation\TranslatableInterface;

/**
 * @see EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem::submenu()
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
final class SubMenuItem implements MenuItemInterface
{
    use MenuItemTrait {
        setLinkRel as private;
        setLinkTarget as private;
    }

    /** @var MenuItemInterface[] */
    private array $subMenuItems = [];

    public function __construct(TranslatableInterface|string $label, ?string $icon = null)
    {
        $this->dto = new MenuItemDto();

        $this->dto->setType(MenuItemDto::TYPE_SUBMENU);
        $this->dto->setLabel($label);
        $this->dto->setIcon($icon);
    }

    /**
     * @param MenuItemInterface[] $subItems
     */
    public function setSubItems(array $subItems): self
    {
        $this->subMenuItems = $subItems;

        return $this;
    }

    /**
     * Renders the submenu always expanded, no matter the current URL, and makes
     * it not collapsible. Useful to group related items while keeping all of
     * them permanently visible.
     */
    public function keepOpen(bool $keepOpen = true): self
    {
        $this->dto->setKeepOpen($keepOpen);

        return $this;
    }

    public function getAsDto(): MenuItemDto
    {
        $subItemDtos = [];
        foreach ($this->subMenuItems as $subItem) {
            $subItemDtos[] = $subItem->getAsDto();
        }

        $this->dto->setSubItems($subItemDtos);

        if ($this->dto->keepsOpen()) {
            $this->dto->setExpanded(true);
        }

        return $this->dto;
    }
}
