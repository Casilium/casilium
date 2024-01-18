<?php

declare(strict_types=1);

namespace Organisation\Handler;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Organisation\Service\ImportExportService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function implode;

use const PHP_EOL;

class ExportHandler implements RequestHandlerInterface
{
    /** @var ImportExportService */
    protected $service;

    public function __construct(ImportExportService $service)
    {
        $this->service = $service;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->service->fetchOrganisations();
        return $this->export($result);
    }

    protected function export(array $data): ResponseInterface
    {
        $body = implode(',', $data['headers']) . PHP_EOL;
        foreach ($data['content'] as $row) {
            $body .= implode(',', $row) . PHP_EOL;
        }

        $stream = new Stream('php://memory', 'w');
        $stream->write($body);

        $response = new Response();
        return $response
            ->withHeader('Content-Type', 'text/csv')
            ->withHeader('Content-Disposition', 'attachment; filename="export.csv"')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', 0)
            ->withBody($stream);
    }
}
