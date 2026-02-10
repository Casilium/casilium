<?php

declare(strict_types=1);

namespace Ticket\Handler;

use Psr\Http\Message\ServerRequestInterface;

use function array_key_exists;
use function is_numeric;
use function preg_replace;

final class TicketListRequest
{
    public const ITEMS_PER_PAGE      = 25;
    private const MAX_ITEMS_PER_PAGE = 100;
    public const POLL_INTERVAL_MS    = 60000;
    private const DEFAULT_PAGE_NUM   = 1;

    public static function extractPage(ServerRequestInterface $request): int
    {
        $queryParams = $request->getQueryParams();
        $page        = $queryParams['page'] ?? self::DEFAULT_PAGE_NUM;

        if (! is_numeric($page) || (int) $page < self::DEFAULT_PAGE_NUM) {
            return self::DEFAULT_PAGE_NUM;
        }

        return (int) $page;
    }

    public static function extractOptions(ServerRequestInterface $request): array
    {
        $queryParams   = $request->getQueryParams();
        $hideCompleted = ! array_key_exists('show', $queryParams);
        $filter        = $queryParams['filter'] ?? null;

        if ($organisationUuid = $request->getAttribute('org_id')) {
            return [
                'organisation_uuid' => $organisationUuid,
                'hide_completed'    => $hideCompleted,
            ];
        }

        if ($queueId = $request->getAttribute('queue_id')) {
            return [
                'queue_id'       => (int) $queueId,
                'hide_completed' => $hideCompleted,
            ];
        }

        if ($statusId = $request->getAttribute('status_id')) {
            return [
                'status_id' => (int) $statusId,
            ];
        }

        if ($filter === 'overdue') {
            return [
                'overdue' => true,
            ];
        }

        if ($filter === 'duetoday') {
            return [
                'due_today' => true,
            ];
        }

        if ($filter === 'unresolved') {
            return [
                'unresolved' => true,
            ];
        }

        return [
            'hide_completed' => $hideCompleted,
        ];
    }

    public static function extractItemsPerPage(ServerRequestInterface $request): int
    {
        $queryParams = $request->getQueryParams();
        $rowsPerPage = $queryParams['rows'] ?? $queryParams['per_page'] ?? self::ITEMS_PER_PAGE;

        if (! is_numeric($rowsPerPage)) {
            return self::ITEMS_PER_PAGE;
        }

        $rowsPerPage = (int) $rowsPerPage;
        if ($rowsPerPage < 1) {
            return self::ITEMS_PER_PAGE;
        }

        if ($rowsPerPage > self::MAX_ITEMS_PER_PAGE) {
            return self::MAX_ITEMS_PER_PAGE;
        }

        return $rowsPerPage;
    }

    public static function extractChangesPath(ServerRequestInterface $request): string
    {
        $path        = $request->getUri()->getPath();
        $changesPath = preg_replace('#^/ticket/list#', '/ticket/list/changes', $path, 1);

        if ($changesPath === null || $changesPath === $path) {
            return '/ticket/list/changes';
        }

        return $changesPath;
    }
}
