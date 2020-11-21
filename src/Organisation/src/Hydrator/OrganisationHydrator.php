<?php

declare(strict_types=1);

namespace Organisation\Hydrator;

use Laminas\Hydrator\AbstractHydrator;
use Organisation\Entity\Domain;
use Organisation\Entity\Organisation;
use Ramsey\Uuid\Uuid;

class OrganisationHydrator extends AbstractHydrator
{
    /**
     * @param array $data
     * @param object|Organisation $object
     * @return object|void
     */
    public function hydrate(array $data, object $object)
    {
        if ($this->propertyExists('id', $data)) {
            $id = (int) $data['id'];
        }

        if ($this->propertyExists('uuid', $data)) {
            if ($data['uuid' instanceof Uuid]) {
                $object->setUuid($data['uuid']);
            } elseif (is_string($data['uuid'])) {
                if (Uuid::isValid($data['uuid'])) {
                    $object->setUuid(Uuid::fromString($data['uuid']));
                }
            }
        }

        if ($this->propertyExists('created', $data)) {
            if ($data['created'] instanceof \DateTime) {
                $object->setCreated($data['created']);
            } elseif (is_string($data['created'])) {
                if ($dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $data['created'])) {
                    $object->setCreated($dateTime);
                }
            }
        }

        if ($this->propertyExists('is_active', $data)) {
            $object->setIsActive((int)$data['is_active']);
        }

        if ($this->propertyExists('modified', $data)) {
            if ($data['modified'] instanceof \DateTime) {
                $object->setCreated($data['modified']);
            } elseif (is_string($data['modified'])) {
                if ($dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $data['created'])) {
                    $object->setCreated($dateTime);
                }
            }
        }

        if ($this->propertyExists('name', $data)) {
            $object->setName($data['name']);
        }

        if ($this->propertyExists('type_id', $data)) {
            $object->setTypeId((int)$data['type_id']);
        }

        if ($this->propertyExists('domain', $data)) {
            foreach ($data['domain'] as $value) {
                $domain = new Domain();
                $domain->setName($value);
                $domain->setOrganisation($object);
                $object->addDomain($domain);
            }
        }

        return $object;
    }

    /**
     * @param object|Organisation $object
     * @return array
     */
    public function extract(object $object): array
    {
        $result = [
            'id' => $object->getId(),
            'uuid' => $object->getUuid(),
            'created' => $object->getCreated(),
            'is_active' => $object->getIsActive(),
            'modified' => $object->getModified(),
            'name' => $object->getName(),
            'type_id' => $object->getTypeId(),
        ];

        $domains = [];
        /** @var Domain$domain */
        foreach ($object->getDomains() as $domain) {
            $domains[$domain->getId()] = $domain->getName();
        }

        $result['domain'] = implode(';', $domains);
        return $result;
    }

    protected function propertyExists(string $property, array $data)
    {
        if (array_key_exists($property, $data) && !empty($data[$property])) {
            return true;
        }

        return false;
    }
}