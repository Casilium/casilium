<?php

declare(strict_types=1);

namespace TicketTest\Form;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Mezzio\Csrf\SessionCsrfGuard;
use PHPUnit\Framework\TestCase;
use Ticket\Entity\CannedResponse;
use Ticket\Form\CannedResponseForm;

class CannedResponseFormTest extends TestCase
{
    private SessionCsrfGuard $guard;
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;

    protected function setUp(): void
    {
        $this->guard         = $this->createMock(SessionCsrfGuard::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository    = $this->createMock(EntityRepository::class);

        $this->guard->method('validateToken')->willReturn(true);
        $this->entityManager->method('getRepository')
            ->with(CannedResponse::class)
            ->willReturn($this->repository);
    }

    public function testAcceptsValidResponse(): void
    {
        $this->repository->method('findOneBy')->willReturn(null);
        $form = new CannedResponseForm($this->guard, $this->entityManager);
        $form->setData([
            'title'    => 'Password reset',
            'response' => 'Please reset your password.',
            'csrf'     => 'valid-token',
        ]);

        $this->assertTrue($form->isValid());
    }

    public function testRejectsBlankTitleAndResponse(): void
    {
        $form = new CannedResponseForm($this->guard, $this->entityManager);
        $form->setData([
            'title'    => '',
            'response' => '',
            'csrf'     => 'valid-token',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey('title', $form->getMessages());
        $this->assertArrayHasKey('response', $form->getMessages());
    }

    public function testRejectsDuplicateTitle(): void
    {
        $existing = new CannedResponse();
        $existing->setId(4);
        $existing->setTitle('Password reset');
        $existing->setResponse('Existing response');

        $this->repository->method('findOneBy')
            ->with(['title' => 'Password reset'])
            ->willReturn($existing);

        $form = new CannedResponseForm($this->guard, $this->entityManager);
        $form->setData([
            'title'    => 'Password reset',
            'response' => 'A different response.',
            'csrf'     => 'valid-token',
        ]);

        $this->assertFalse($form->isValid());
        $this->assertArrayHasKey('title', $form->getMessages());
    }

    public function testAllowsCurrentTitleWhenEditing(): void
    {
        $existing = new CannedResponse();
        $existing->setId(4);
        $existing->setTitle('Password reset');
        $existing->setResponse('Existing response');

        $this->repository->method('findOneBy')->willReturn($existing);

        $form = new CannedResponseForm($this->guard, $this->entityManager, $existing);
        $form->setData([
            'id'       => 4,
            'title'    => 'Password reset',
            'response' => 'Updated response.',
            'csrf'     => 'valid-token',
        ]);

        $this->assertTrue($form->isValid());
    }
}
