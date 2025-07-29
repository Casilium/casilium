<?php

declare(strict_types=1);

namespace OrganisationSiteTest\Entity;

use Laminas\InputFilter\InputFilterInterface;
use Organisation\Entity\Organisation;
use OrganisationSite\Entity\CountryEntity;
use OrganisationSite\Entity\SiteEntity;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class SiteEntityTest extends TestCase
{
    private SiteEntity $site;

    protected function setUp(): void
    {
        $this->site = new SiteEntity();
    }

    public function testConstructorInitializesDefaults(): void
    {
        $site = new SiteEntity();

        $this->assertEquals(0, $site->getId()); // Constructor sets ID to 0
        $this->assertInstanceOf(UuidInterface::class, $site->getUuid());

        // All other properties should be null by default
        $this->assertNull($site->getName());
        $this->assertNull($site->getStreetAddress());
        $this->assertNull($site->getStreetAddress2());
        $this->assertNull($site->getTown());
        $this->assertNull($site->getCity());
        $this->assertNull($site->getCounty());
        $this->assertNull($site->getPostalCode());
        $this->assertNull($site->getTelephone());
        $this->assertNull($site->getCountry());
        $this->assertNull($site->getOrganisation());
    }

    public function testSetAndGetId(): void
    {
        $result = $this->site->setId(123);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals(123, $this->site->getId());
    }

    public function testSetAndGetUuid(): void
    {
        $uuid   = Uuid::uuid4();
        $result = $this->site->setUuid($uuid);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertSame($uuid, $this->site->getUuid());
    }

    public function testSetAndGetName(): void
    {
        $name   = 'London Office';
        $result = $this->site->setName($name);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($name, $this->site->getName());
    }

    public function testSetNameWithNull(): void
    {
        $result = $this->site->setName(null);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertNull($this->site->getName());
    }

    public function testSetAndGetCountry(): void
    {
        $country = $this->createMock(CountryEntity::class);
        $result  = $this->site->setCountry($country);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertSame($country, $this->site->getCountry());
    }

    public function testSetAndGetCounty(): void
    {
        $county = 'Greater London';
        $result = $this->site->setCounty($county);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($county, $this->site->getCounty());
    }

    public function testSetCountyWithNull(): void
    {
        $result = $this->site->setCounty(null);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertNull($this->site->getCounty());
    }

    public function testSetAndGetStreetAddress(): void
    {
        $address = '123 Main Street';
        $result  = $this->site->setStreetAddress($address);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($address, $this->site->getStreetAddress());
    }

    public function testSetAndGetStreetAddress2(): void
    {
        $address2 = 'Suite 456';
        $result   = $this->site->setStreetAddress2($address2);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($address2, $this->site->getStreetAddress2());
    }

    public function testSetStreetAddress2WithNull(): void
    {
        $result = $this->site->setStreetAddress2(null);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertNull($this->site->getStreetAddress2());
    }

    public function testSetAndGetTown(): void
    {
        $town   = 'Westminster';
        $result = $this->site->setTown($town);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($town, $this->site->getTown());
    }

    public function testSetTownWithNull(): void
    {
        $result = $this->site->setTown(null);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertNull($this->site->getTown());
    }

    public function testSetTownReturnsNullableEntity(): void
    {
        // Note: setTown has inconsistent return type annotation (?SiteEntity instead of SiteEntity)
        $result = $this->site->setTown('Test Town');

        $this->assertInstanceOf(SiteEntity::class, $result);
    }

    public function testSetAndGetCity(): void
    {
        $city   = 'London';
        $result = $this->site->setCity($city);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($city, $this->site->getCity());
    }

    public function testSetAndGetPostalCode(): void
    {
        $postalCode = 'SW1A 1AA';
        $result     = $this->site->setPostalCode($postalCode);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($postalCode, $this->site->getPostalCode());
    }

    public function testSetAndGetTelephone(): void
    {
        $telephone = '+44 20 7946 0958';
        $result    = $this->site->setTelephone($telephone);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals($telephone, $this->site->getTelephone());
    }

    public function testSetAndGetOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $result       = $this->site->setOrganisation($organisation);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertSame($organisation, $this->site->getOrganisation());
    }

    public function testGetAddressAsStringWithAllFields(): void
    {
        $country = $this->createMock(CountryEntity::class);
        $country->method('getName')->willReturn('United Kingdom');

        $this->site->setStreetAddress('123 Main Street')
                  ->setStreetAddress2('Suite 456')
                  ->setTown('Westminster')
                  ->setCity('London')
                  ->setCounty('Greater London')
                  ->setCountry($country)
                  ->setPostalCode('SW1A 1AA');

        $expected = '123 Main Street, Suite 456, Westminster, London, Greater London, United Kingdom, SW1A 1AA';
        $result   = $this->site->getAddressAsString();

        $this->assertEquals($expected, $result);
    }

    public function testGetAddressAsStringWithPartialFields(): void
    {
        $country = $this->createMock(CountryEntity::class);
        $country->method('getName')->willReturn('United Kingdom');

        $this->site->setStreetAddress('123 Main Street')
                  ->setCity('London')
                  ->setCountry($country)
                  ->setPostalCode('SW1A 1AA');

        // Should filter out null values
        $expected = '123 Main Street, London, United Kingdom, SW1A 1AA';
        $result   = $this->site->getAddressAsString();

        $this->assertEquals($expected, $result);
    }

    public function testGetArrayCopyReturnsCorrectStructure(): void
    {
        $uuid    = Uuid::uuid4();
        $country = $this->createMock(CountryEntity::class);
        $country->method('getId')->willReturn(826); // UK country code
        $organisation = $this->createMock(Organisation::class);

        $this->site->setId(123)
                  ->setUuid($uuid)
                  ->setName('Test Site')
                  ->setStreetAddress('123 Test Street')
                  ->setStreetAddress2('Floor 2')
                  ->setTown('Test Town')
                  ->setCity('Test City')
                  ->setCounty('Test County')
                  ->setPostalCode('TE1 2ST')
                  ->setTelephone('+44 20 1234 5678')
                  ->setCountry($country)
                  ->setOrganisation($organisation);

        $arrayCopy = $this->site->getArrayCopy();

        $this->assertIsArray($arrayCopy);
        $this->assertEquals(123, $arrayCopy['id']);
        $this->assertSame($uuid, $arrayCopy['uuid']);
        $this->assertEquals('Test Site', $arrayCopy['name']);
        $this->assertEquals('123 Test Street', $arrayCopy['street_address']);
        $this->assertEquals('Floor 2', $arrayCopy['street_address2']);
        $this->assertEquals('Test Town', $arrayCopy['town']);
        $this->assertEquals('Test City', $arrayCopy['city']);
        $this->assertEquals('Test County', $arrayCopy['county']);
        $this->assertEquals('TE1 2ST', $arrayCopy['postal_code']);
        $this->assertEquals('+44 20 1234 5678', $arrayCopy['telephone']);
        $this->assertEquals(826, $arrayCopy['country_id']);
        $this->assertSame($organisation, $arrayCopy['organisation']);
    }

    public function testSetValuesWithAllData(): void
    {
        $uuid    = Uuid::uuid4();
        $country = $this->createMock(CountryEntity::class);

        $data = [
            'id'              => 456,
            'uuid'            => $uuid->toString(),
            'name'            => 'Updated Site',
            'street_address'  => '456 Updated Street',
            'street_address2' => 'Suite 789',
            'town'            => 'Updated Town',
            'city'            => 'Updated City',
            'county'          => 'Updated County',
            'country'         => $country,
            'postal_code'     => 'UP1 2ST',
            'telephone'       => '+44 20 9876 5432',
        ];

        $result = $this->site->setValues($data);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals('Updated Site', $this->site->getName());
        $this->assertEquals('456 Updated Street', $this->site->getStreetAddress());
        $this->assertEquals('Suite 789', $this->site->getStreetAddress2());
        $this->assertEquals('Updated Town', $this->site->getTown());
        $this->assertEquals('Updated City', $this->site->getCity());
        $this->assertEquals('Updated County', $this->site->getCounty());
        $this->assertSame($country, $this->site->getCountry());
        $this->assertEquals('UP1 2ST', $this->site->getPostalCode());
        $this->assertEquals('+44 20 9876 5432', $this->site->getTelephone());
    }

    public function testSetValuesWithPartialData(): void
    {
        $data = [
            'name' => 'Partial Site',
            'city' => 'Partial City',
        ];

        $this->site->setValues($data);

        $this->assertEquals('Partial Site', $this->site->getName());
        $this->assertEquals('Partial City', $this->site->getCity());

        // Other properties should remain null
        $this->assertNull($this->site->getStreetAddress());
        $this->assertNull($this->site->getCounty());
    }

    public function testSetValuesHandlesUuidGeneration(): void
    {
        $data = ['name' => 'Test Site'];

        // Should generate UUID if not provided
        $this->site->setValues($data);

        $this->assertInstanceOf(UuidInterface::class, $this->site->getUuid());
    }

    public function testSetValuesWithInvalidUuid(): void
    {
        $data = [
            'name' => 'Test Site',
            'uuid' => 'invalid-uuid-string',
        ];

        // Should keep existing UUID when invalid UUID provided
        $originalUuid = $this->site->getUuid();
        $this->site->setValues($data);

        $this->assertSame($originalUuid, $this->site->getUuid());
    }

    public function testExchangeArrayCallsSetValues(): void
    {
        $data = [
            'name' => 'Exchange Test',
            'city' => 'Exchange City',
        ];

        $this->site->exchangeArray($data);

        $this->assertEquals('Exchange Test', $this->site->getName());
        $this->assertEquals('Exchange City', $this->site->getCity());
    }

    public function testGetInputFilterSpecificationReturnsInterface(): void
    {
        $inputFilter = $this->site->getInputFilterSpecification();

        $this->assertInstanceOf(InputFilterInterface::class, $inputFilter);
    }

    public function testFluentInterfaceChaining(): void
    {
        $uuid         = Uuid::uuid4();
        $country      = $this->createMock(CountryEntity::class);
        $organisation = $this->createMock(Organisation::class);

        $result = $this->site
            ->setId(999)
            ->setUuid($uuid)
            ->setName('Chained Site')
            ->setStreetAddress('999 Chain Street')
            ->setStreetAddress2('Unit 999')
            ->setTown('Chain Town')
            ->setCity('Chain City')
            ->setCounty('Chain County')
            ->setCountry($country)
            ->setPostalCode('CH9 9IN')
            ->setTelephone('+44 20 9999 9999')
            ->setOrganisation($organisation);

        $this->assertInstanceOf(SiteEntity::class, $result);
        $this->assertEquals(999, $this->site->getId());
        $this->assertSame($uuid, $this->site->getUuid());
        $this->assertEquals('Chained Site', $this->site->getName());
        $this->assertEquals('999 Chain Street', $this->site->getStreetAddress());
        $this->assertEquals('Unit 999', $this->site->getStreetAddress2());
        $this->assertEquals('Chain Town', $this->site->getTown());
        $this->assertEquals('Chain City', $this->site->getCity());
        $this->assertEquals('Chain County', $this->site->getCounty());
        $this->assertSame($country, $this->site->getCountry());
        $this->assertEquals('CH9 9IN', $this->site->getPostalCode());
        $this->assertEquals('+44 20 9999 9999', $this->site->getTelephone());
        $this->assertSame($organisation, $this->site->getOrganisation());
    }

    public function testSetValuesHandlesNameDuplication(): void
    {
        // There's a bug in setValues where name is set twice
        $data = ['name' => 'Duplicate Name Test'];

        $this->site->setValues($data);

        // Should still work despite the duplication
        $this->assertEquals('Duplicate Name Test', $this->site->getName());
    }

    public function testGetAddressAsStringHandlesNullCountry(): void
    {
        // Test that null country is handled properly with null-safe operator
        $this->site->setStreetAddress('123 Test Street')
                  ->setCity('Test City')
                  ->setPostalCode('T1 2ST');

        // Country is null, so it should be filtered out
        $expected = '123 Test Street, Test City, T1 2ST';
        $result   = $this->site->getAddressAsString();

        $this->assertEquals($expected, $result);
    }
}
