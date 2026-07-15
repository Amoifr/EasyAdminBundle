<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Dto;

use EasyCorp\Bundle\EasyAdminBundle\Dto\AssetDto;
use PHPUnit\Framework\TestCase;

class AssetDtoTest extends TestCase
{
    public function testReprisePackageNameDefaultsToNull(): void
    {
        $assetDto = new AssetDto('admin');

        $this->assertNull($assetDto->getReprisePackageName());
    }

    public function testSetReprisePackageName(): void
    {
        $assetDto = new AssetDto('admin');
        $assetDto->setReprisePackageName('my_package');

        $this->assertSame('my_package', $assetDto->getReprisePackageName());
    }
}
