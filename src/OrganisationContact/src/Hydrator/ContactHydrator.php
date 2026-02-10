<?php

declare(strict_types=1);

namespace OrganisationContact\Hydrator;

use Laminas\Hydrator\HydratorInterface;
use OrganisationContact\Entity\Contact;
use OrganisationContact\Service\ContactService;
use ReflectionProperty;

use function array_key_exists;

class ContactHydrator implements HydratorInterface
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    /**
     * @param Contact|object $object
     * @return array|mixed[]
     */
    public function extract(object $object): array
    {
        return [
            'id'               => $object->getId(),
            'organisation'     => $object->getOrganisation() === null ? null : $object->getOrganisation()->getId(),
            'site'             => $object->getSite() === null ? null : $object->getSite()->getId(),
            'first_name'       => $this->getStringProperty($object, 'firstName'),
            'middle_name'      => $this->getStringProperty($object, 'middleName'),
            'last_name'        => $this->getStringProperty($object, 'lastName'),
            'work_telephone'   => $this->getStringProperty($object, 'workTelephone'),
            'work_extension'   => $this->getStringProperty($object, 'workExtension'),
            'mobile_telephone' => $this->getStringProperty($object, 'mobileTelephone'),
            'home_telephone'   => $this->getStringProperty($object, 'homeTelephone'),
            'work_email'       => $this->getStringProperty($object, 'workEmail'),
            'other_email'      => $this->getStringProperty($object, 'otherEmail'),
            'gender'           => $object->getGender(),
            'is_active'        => $object->isActive(),
        ];
    }

    /**
     * @param array $data
     * @param Contact|object $object
     * @return Contact|object
     */
    public function hydrate(array $data, object $object): object
    {
        if ($this->propertyExists('id', $data)) {
            $id = (int) $data['id'];
            if ($id > 0) {
                $object->setId($id);
            }
        }

        if ($this->propertyExists('organisation', $data)) {
            $organisationId = (int) $data['organisation'];
            // todo
        }

        if ($this->propertyExists('site', $data)) {
            $siteId = (int) $data['site'];
            // todo
        }

        if ($this->propertyExists('first_name', $data)) {
            $object->setFirstName($data['first_name']);
        }

        if ($this->propertyExists('middle_name', $data)) {
            $object->setMiddleName($data['middle_name']);
        }

        if ($this->propertyExists('last_name', $data)) {
            $object->setLastName($data['last_name']);
        }

        if ($this->propertyExists('work_telephone', $data)) {
            $object->setWorkTelephone($data['work_telephone']);
        }

        if ($this->propertyExists('work_extension', $data)) {
            $object->setWorkExtension($data['work_extension']);
        }

        if ($this->propertyExists('mobile_telephone', $data)) {
            $object->setMobileTelephone($data['mobile_telephone']);
        }

        if ($this->propertyExists('home_telephone', $data)) {
            $object->setHomeTelephone($data['home_telephone']);
        }

        if ($this->propertyExists('work_email', $data)) {
            $object->setWorkEmail($data['work_email']);
        }

        if ($this->propertyExists('other_email', $data)) {
            $object->setOtherEmail($data['other_email']);
        }

        if ($this->propertyExists('gender', $data)) {
            $object->setGender($data['gender']);
        }

        if (array_key_exists('is_active', $data)) {
            $object->setIsActive((bool) $data['is_active']);
        }

        return $object;
    }

    protected function propertyExists(string $property, array $data): bool
    {
        if (array_key_exists($property, $data) && ! empty($data[$property])) {
            return true;
        }

        return false;
    }

    private function getStringProperty(Contact $contact, string $property): string
    {
        $reflection = new ReflectionProperty($contact, $property);
        if (! $reflection->isInitialized($contact)) {
            return '';
        }

        return (string) $reflection->getValue($contact);
    }
}
