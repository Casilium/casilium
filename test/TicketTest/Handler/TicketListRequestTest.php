<?php

declare(strict_types=1);

namespace TicketTest\Handler;

use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Uri;
use PHPUnit\Framework\TestCase;
use Ticket\Handler\TicketListRequest;

class TicketListRequestTest extends TestCase
{
    public function testExtractPageDefaultsToOneForInvalidValues(): void
    {
        $request = (new ServerRequest())->withQueryParams([
            'page' => 'invalid',
        ]);

        $this->assertSame(1, TicketListRequest::extractPage($request));
    }

    public function testExtractPageReturnsParsedPositiveInteger(): void
    {
        $request = (new ServerRequest())->withQueryParams([
            'page' => '3',
        ]);

        $this->assertSame(3, TicketListRequest::extractPage($request));
    }

    public function testExtractOptionsPrioritizesOrganisationOverQueueAndStatus(): void
    {
        $request = (new ServerRequest())
            ->withAttribute('org_id', 'org-123')
            ->withAttribute('queue_id', '99')
            ->withAttribute('status_id', '2')
            ->withQueryParams([
                'show' => 'all',
            ]);

        $this->assertSame([
            'organisation_uuid' => 'org-123',
            'hide_completed'    => false,
        ], TicketListRequest::extractOptions($request));
    }

    public function testExtractOptionsSupportsUnresolvedFilter(): void
    {
        $request = (new ServerRequest())->withQueryParams([
            'filter' => 'unresolved',
        ]);

        $this->assertSame([
            'unresolved' => true,
        ], TicketListRequest::extractOptions($request));
    }

    public function testExtractChangesPathForQueueRoute(): void
    {
        $request = (new ServerRequest())
            ->withUri(new Uri('/ticket/list/queue/44'));

        $this->assertSame('/ticket/list/changes/queue/44', TicketListRequest::extractChangesPath($request));
    }

    public function testExtractItemsPerPageDefaultsAndCaps(): void
    {
        $defaultRequest = (new ServerRequest())->withQueryParams([
            'rows' => 'invalid',
        ]);
        $cappedRequest  = (new ServerRequest())->withQueryParams([
            'rows' => '999',
        ]);
        $validRequest   = (new ServerRequest())->withQueryParams([
            'rows' => '10',
        ]);

        $this->assertSame(25, TicketListRequest::extractItemsPerPage($defaultRequest));
        $this->assertSame(100, TicketListRequest::extractItemsPerPage($cappedRequest));
        $this->assertSame(10, TicketListRequest::extractItemsPerPage($validRequest));
    }
}
