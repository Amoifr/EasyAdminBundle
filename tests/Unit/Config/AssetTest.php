<?php

namespace EasyCorp\Bundle\EasyAdminBundle\Tests\Unit\Config;

use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use PHPUnit\Framework\TestCase;

class AssetTest extends TestCase
{
    public function testReprisePackageNameDefaultsToNull(): void
    {
        $asset = Asset::new('admin');

        $this->assertNull($asset->getAsDto()->getReprisePackageName());
    }

    public function testReprisePackageName(): void
    {
        $asset = Asset::new('admin')->reprisePackageName('my_package');

        $this->assertSame('my_package', $asset->getAsDto()->getReprisePackageName());
    }
}
