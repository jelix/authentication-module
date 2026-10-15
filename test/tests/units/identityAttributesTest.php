<?php

use PHPUnit\Framework\TestCase;
use Jelix\Authentication\Core\IdentityAttributes;
use Jelix\Authentication\Account\AccountAdapter;

class identityAttributesTest extends TestCase
{
    public function testGetAttributeLabelsWithKnownAttributes()
    {
        $labels = IdentityAttributes::getAttributeLabels([
            IdentityAttributes::UserName,
            IdentityAttributes::Email,
            IdentityAttributes::FirstName,
        ]);

        $this->assertEquals('Username', $labels[IdentityAttributes::UserName]);
        $this->assertEquals('Email address', $labels[IdentityAttributes::Email]);
        $this->assertEquals('First name', $labels[IdentityAttributes::FirstName]);
    }

    public function testGetAttributeLabelsWithUnknownAttribute()
    {
        $labels = IdentityAttributes::getAttributeLabels([
            'custom/attr/foo',
        ]);

        $this->assertEquals('custom/attr/foo', $labels['custom/attr/foo']);
    }

    public function testGetAttributeLabelsWithMixedAttributes()
    {
        $labels = IdentityAttributes::getAttributeLabels([
            IdentityAttributes::DisplayName,
            'custom/something',
            IdentityAttributes::Country,
        ]);

        $this->assertCount(3, $labels);
        $this->assertEquals('Display name', $labels[IdentityAttributes::DisplayName]);
        $this->assertEquals('custom/something', $labels['custom/something']);
        $this->assertEquals('Country', $labels[IdentityAttributes::Country]);
    }

    public function testGetAttributeLabelsWithEmptyArray()
    {
        $labels = IdentityAttributes::getAttributeLabels([]);
        $this->assertEmpty($labels);
    }

    public function testGetSupportedAccountClaimsReturnsDefaultClaims()
    {
        $adapter = new AccountAdapter();
        $claims = $adapter->getSupportedAccountClaims();

        $this->assertContains(IdentityAttributes::UserName, $claims);
        $this->assertContains(IdentityAttributes::FirstName, $claims);
        $this->assertContains(IdentityAttributes::LastName, $claims);
        $this->assertContains(IdentityAttributes::Email, $claims);
        $this->assertContains(IdentityAttributes::DisplayName, $claims);
    }

    public function testGetSupportedAccountClaimsReturnsIndexedArray()
    {
        $adapter = new AccountAdapter();
        $claims = $adapter->getSupportedAccountClaims();

        $this->assertIsArray($claims);
        $this->assertEquals(array_values($claims), $claims);
    }
}
