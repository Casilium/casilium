<?php

declare(strict_types=1);

namespace OrganisationSiteTest\Entity;

use OrganisationSite\Entity\CountryEntity;
use PHPUnit\Framework\TestCase;

class CountryEntityTest extends TestCase
{
    private CountryEntity $country;

    protected function setUp(): void
    {
        $this->country = new CountryEntity();
    }

    public function testSetAndGetId(): void
    {
        $result = $this->country->setId(826);

        $this->assertInstanceOf(CountryEntity::class, $result);
        $this->assertEquals(826, $this->country->getId());
    }

    public function testSetAndGetName(): void
    {
        $name   = 'United Kingdom';
        $result = $this->country->setName($name);

        $this->assertInstanceOf(CountryEntity::class, $result);
        $this->assertEquals($name, $this->country->getName());
    }

    public function testFluentInterfaceChaining(): void
    {
        $result = $this->country
            ->setId(840)
            ->setName('United States');

        $this->assertInstanceOf(CountryEntity::class, $result);
        $this->assertEquals(840, $this->country->getId());
        $this->assertEquals('United States', $this->country->getName());
    }

    /**
     * @dataProvider countryDataProvider
     */
    public function testSetCountryData(int $id, string $name): void
    {
        $this->country->setId($id)->setName($name);

        $this->assertEquals($id, $this->country->getId());
        $this->assertEquals($name, $this->country->getName());
    }

    public static function countryDataProvider(): array
    {
        return [
            'UK'        => [826, 'United Kingdom'],
            'US'        => [840, 'United States'],
            'Germany'   => [276, 'Germany'],
            'France'    => [250, 'France'],
            'Canada'    => [124, 'Canada'],
            'Australia' => [36, 'Australia'],
            'Japan'     => [392, 'Japan'],
        ];
    }

    public function testCountryEntityPropertiesAreCorrectlyTyped(): void
    {
        $this->country->setId(826)->setName('United Kingdom');

        $this->assertIsInt($this->country->getId());
        $this->assertIsString($this->country->getName());
    }

    public function testCountryWithEmptyName(): void
    {
        $this->country->setName('');

        $this->assertEquals('', $this->country->getName());
    }

    public function testCountryWithLongName(): void
    {
        $longName = 'The United Kingdom of Great Britain and Northern Ireland';
        $this->country->setName($longName);

        $this->assertEquals($longName, $this->country->getName());
    }

    public function testCountryWithSpecialCharacters(): void
    {
        $nameWithSpecialChars = 'Côte d\'Ivoire';
        $this->country->setName($nameWithSpecialChars);

        $this->assertEquals($nameWithSpecialChars, $this->country->getName());
    }

    public function testCountryWithNumericName(): void
    {
        // Edge case: country name that's entirely numeric
        $numericName = '123456';
        $this->country->setName($numericName);

        $this->assertEquals($numericName, $this->country->getName());
        $this->assertIsString($this->country->getName()); // Should still be string type
    }

    public function testMultipleSettersOnSameInstance(): void
    {
        // Test that setters properly update the same instance
        $this->country->setId(100)->setName('Test Country 1');
        $this->assertEquals(100, $this->country->getId());
        $this->assertEquals('Test Country 1', $this->country->getName());

        // Update the same instance
        $this->country->setId(200)->setName('Test Country 2');
        $this->assertEquals(200, $this->country->getId());
        $this->assertEquals('Test Country 2', $this->country->getName());
    }
}
