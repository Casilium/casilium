<?php

declare(strict_types=1);

namespace OrganisationContactTest\Entity;

use Organisation\Entity\Organisation;
use OrganisationContact\Entity\Contact;
use OrganisationSite\Entity\SiteEntity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ContactTest extends TestCase
{
    private Contact $contact;

    protected function setUp(): void
    {
        $this->contact = new Contact();
    }

    public function testSetAndGetId(): void
    {
        $result = $this->contact->setId(123);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(123, $this->contact->getId());
    }

    public function testSetAndGetOrganisation(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $result       = $this->contact->setOrganisation($organisation);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertSame($organisation, $this->contact->getOrganisation());
    }

    public function testGetOrganisationReturnsNullByDefault(): void
    {
        // Organisation property is initialized to null
        $this->assertNull($this->contact->getOrganisation());
    }

    public function testSetAndGetSite(): void
    {
        $site   = $this->createMock(SiteEntity::class);
        $result = $this->contact->setSite($site);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertSame($site, $this->contact->getSite());
    }

    public function testSetSiteWithNull(): void
    {
        $result = $this->contact->setSite(null);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertNull($this->contact->getSite());
    }

    public function testGetSiteReturnsNullByDefault(): void
    {
        // Site property is initialized to null
        $this->assertNull($this->contact->getSite());
    }

    public function testSetAndGetFirstName(): void
    {
        $firstName = 'John';
        $result    = $this->contact->setFirstName($firstName);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($firstName, $this->contact->getFirstName());
    }

    public function testSetAndGetMiddleName(): void
    {
        $middleName = 'Michael';
        $result     = $this->contact->setMiddleName($middleName);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($middleName, $this->contact->getMiddleName());
    }

    public function testSetAndGetLastName(): void
    {
        $lastName = 'Smith';
        $result   = $this->contact->setLastName($lastName);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($lastName, $this->contact->getLastName());
    }

    public function testSetAndGetWorkTelephone(): void
    {
        $workTelephone = '+44 20 7946 0958';
        $result        = $this->contact->setWorkTelephone($workTelephone);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($workTelephone, $this->contact->getWorkTelephone());
    }

    public function testSetAndGetWorkExtension(): void
    {
        $workExtension = '1234';
        $result        = $this->contact->setWorkExtension($workExtension);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($workExtension, $this->contact->getWorkExtension());
    }

    public function testSetAndGetMobileTelephone(): void
    {
        $mobileTelephone = '+44 7700 900123';
        $result          = $this->contact->setMobileTelephone($mobileTelephone);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($mobileTelephone, $this->contact->getMobileTelephone());
    }

    public function testSetAndGetHomeTelephone(): void
    {
        $homeTelephone = '+44 20 7946 0123';
        $result        = $this->contact->setHomeTelephone($homeTelephone);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($homeTelephone, $this->contact->getHomeTelephone());
    }

    public function testSetAndGetWorkEmail(): void
    {
        $workEmail = 'john@company.com';
        $result    = $this->contact->setWorkEmail($workEmail);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($workEmail, $this->contact->getWorkEmail());
    }

    public function testSetAndGetOtherEmail(): void
    {
        $otherEmail = 'john.personal@example.com';
        $result     = $this->contact->setOtherEmail($otherEmail);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($otherEmail, $this->contact->getOtherEmail());
    }

    public function testSetAndGetGender(): void
    {
        $gender = 'M';
        $result = $this->contact->setGender($gender);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($gender, $this->contact->getGender());
    }

    public function testGetGenderReturnsNullByDefault(): void
    {
        // Gender property is initialized to null
        $this->assertNull($this->contact->getGender());
    }

    public function testGetArrayCopyReturnsCorrectStructure(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $site         = $this->createMock(SiteEntity::class);

        $this->contact->setId(456)
                     ->setOrganisation($organisation)
                     ->setSite($site)
                     ->setFirstName('Jane')
                     ->setMiddleName('Anne')
                     ->setLastName('Doe')
                     ->setWorkTelephone('+44 20 1234 5678')
                     ->setWorkExtension('5678')
                     ->setMobileTelephone('+44 7700 123456')
                     ->setHomeTelephone('+44 20 8765 4321')
                     ->setWorkEmail('jane@company.com')
                     ->setOtherEmail('jane@personal.com')
                     ->setGender('F');

        $arrayCopy = $this->contact->getArrayCopy();

        $this->assertIsArray($arrayCopy);
        $this->assertEquals(456, $arrayCopy['id']);
        $this->assertSame($organisation, $arrayCopy['organisation']);
        $this->assertSame($site, $arrayCopy['site']);
        $this->assertEquals('Jane', $arrayCopy['firstName']);
        $this->assertEquals('Anne', $arrayCopy['middleName']);
        $this->assertEquals('Doe', $arrayCopy['lastName']);
        $this->assertEquals('+44 20 1234 5678', $arrayCopy['workTelephone']);
        $this->assertEquals('5678', $arrayCopy['workExtension']);
        $this->assertEquals('+44 7700 123456', $arrayCopy['mobileTelephone']);
        $this->assertEquals('+44 20 8765 4321', $arrayCopy['homeTelephone']);
        $this->assertEquals('jane@company.com', $arrayCopy['workEmail']);
        $this->assertEquals('jane@personal.com', $arrayCopy['otherEmail']);
        $this->assertEquals('F', $arrayCopy['gender']);
    }

    public function testExchangeArraySetsAllProperties(): void
    {
        $data = [
            'id'               => 789,
            'first_name'       => 'Bob',
            'middle_name'      => 'William',
            'last_name'        => 'Johnson',
            'work_telephone'   => '+1 555 123 4567',
            'work_extension'   => '9999',
            'mobile_telephone' => '+1 555 987 6543',
            'home_telephone'   => '+1 555 246 8135',
            'work_email'       => 'bob@work.com',
            'other_email'      => 'bob@home.com',
            'gender'           => 'M',
        ];

        $result = $this->contact->exchangeArray($data);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(789, $this->contact->getId());
        $this->assertEquals('Bob', $this->contact->getFirstName());
        $this->assertEquals('William', $this->contact->getMiddleName());
        $this->assertEquals('Johnson', $this->contact->getLastName());
        $this->assertEquals('+1 555 123 4567', $this->contact->getWorkTelephone());
        $this->assertEquals('9999', $this->contact->getWorkExtension());
        $this->assertEquals('+1 555 987 6543', $this->contact->getMobileTelephone());
        $this->assertEquals('+1 555 246 8135', $this->contact->getHomeTelephone());
        $this->assertEquals('bob@work.com', $this->contact->getWorkEmail());
        $this->assertEquals('bob@home.com', $this->contact->getOtherEmail());
        $this->assertEquals('M', $this->contact->getGender());
    }

    public function testExchangeArrayHandlesMissingValues(): void
    {
        $data = [
            'first_name' => 'Alice',
            'last_name'  => 'Cooper',
        ];

        $result = $this->contact->exchangeArray($data);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals('Alice', $this->contact->getFirstName());
        $this->assertEquals('Cooper', $this->contact->getLastName());

        // Missing string properties should default to empty strings
        $this->assertEquals('', $this->contact->getMiddleName());
        $this->assertEquals('', $this->contact->getWorkTelephone());
        $this->assertEquals('', $this->contact->getWorkEmail());

        // Missing nullable properties should be null
        $this->assertNull($this->contact->getOrganisation());
        $this->assertNull($this->contact->getSite());
        $this->assertNull($this->contact->getGender());

        // Missing ID should default to 0
        $this->assertEquals(0, $this->contact->getId());
    }

    public function testFluentInterfaceChaining(): void
    {
        $organisation = $this->createMock(Organisation::class);
        $site         = $this->createMock(SiteEntity::class);

        $result = $this->contact
            ->setId(101)
            ->setOrganisation($organisation)
            ->setSite($site)
            ->setFirstName('Charlie')
            ->setMiddleName('David')
            ->setLastName('Brown')
            ->setWorkTelephone('+44 20 9999 8888')
            ->setWorkExtension('0001')
            ->setMobileTelephone('+44 7777 666666')
            ->setHomeTelephone('+44 20 5555 4444')
            ->setWorkEmail('charlie@work.co.uk')
            ->setOtherEmail('charlie@home.co.uk')
            ->setGender('M');

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals(101, $this->contact->getId());
        $this->assertSame($organisation, $this->contact->getOrganisation());
        $this->assertSame($site, $this->contact->getSite());
        $this->assertEquals('Charlie', $this->contact->getFirstName());
        $this->assertEquals('David', $this->contact->getMiddleName());
        $this->assertEquals('Brown', $this->contact->getLastName());
        $this->assertEquals('+44 20 9999 8888', $this->contact->getWorkTelephone());
        $this->assertEquals('0001', $this->contact->getWorkExtension());
        $this->assertEquals('+44 7777 666666', $this->contact->getMobileTelephone());
        $this->assertEquals('+44 20 5555 4444', $this->contact->getHomeTelephone());
        $this->assertEquals('charlie@work.co.uk', $this->contact->getWorkEmail());
        $this->assertEquals('charlie@home.co.uk', $this->contact->getOtherEmail());
        $this->assertEquals('M', $this->contact->getGender());
    }

    #[DataProvider('genderProvider')]
    public function testSetGenderWithValidValues(string $gender): void
    {
        $result = $this->contact->setGender($gender);

        $this->assertInstanceOf(Contact::class, $result);
        $this->assertEquals($gender, $this->contact->getGender());
    }

    public static function genderProvider(): array
    {
        return [
            'Male'    => ['M'],
            'Female'  => ['F'],
            'Other'   => ['O'],
            'Unknown' => ['U'],
        ];
    }

    public function testContactEntityPropertiesAreCorrectlyTyped(): void
    {
        // Test that string properties are properly handled
        $this->contact->setFirstName('Test')
                     ->setMiddleName('Middle')
                     ->setLastName('User')
                     ->setWorkTelephone('+44 123 456 7890')
                     ->setWorkExtension('123')
                     ->setMobileTelephone('+44 777 888 9999')
                     ->setHomeTelephone('+44 111 222 3333')
                     ->setWorkEmail('test@work.com')
                     ->setOtherEmail('test@personal.com');

        // All string getters should return strings
        $this->assertIsString($this->contact->getFirstName());
        $this->assertIsString($this->contact->getMiddleName());
        $this->assertIsString($this->contact->getLastName());
        $this->assertIsString($this->contact->getWorkTelephone());
        $this->assertIsString($this->contact->getWorkExtension());
        $this->assertIsString($this->contact->getMobileTelephone());
        $this->assertIsString($this->contact->getHomeTelephone());
        $this->assertIsString($this->contact->getWorkEmail());
        $this->assertIsString($this->contact->getOtherEmail());
    }

    public function testExchangeArrayWithNullIdHandling(): void
    {
        // Test behavior when ID is null in exchangeArray
        $data = [
            'id'         => null,
            'first_name' => 'Test',
            'last_name'  => 'User',
        ];

        $this->contact->exchangeArray($data);

        $this->assertEquals(0, $this->contact->getId());
        $this->assertEquals('Test', $this->contact->getFirstName());
        $this->assertEquals('User', $this->contact->getLastName());
    }
}
