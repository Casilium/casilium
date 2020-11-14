<?php

declare(strict_types=1);

namespace OrganisationSite\Entity;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class OrganisationCollection - class representing a paginated group of Organisations
 * @see https://docs.zendframework.com/mezzio-hal/intro/
 * @package Organisation\Entity
 */
class SiteCollection extends Paginator
{

}
