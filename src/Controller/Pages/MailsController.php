<?php

namespace Wexample\SymfonyMailDs\Controller\Pages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyMail\Entity\Mail;
use Wexample\SymfonyMail\Repository\MailRepository;
use Wexample\SymfonyMailDs\Traits\SymfonyMailDsBundleClassTrait;

/**
 * The mails the application sent, from its journal (symfony-mail's Mail),
 * newest first: to whom, about what, how it ended — and what they said when
 * it was kept, a contact message. The administrator's, in every environment.
 */
#[Route(path: '/mails/', name: 'mails_')]
#[IsGranted(RoleHelper::ROLE_ADMIN)]
final class MailsController extends AbstractPagesController
{
    use SymfonyMailDsBundleClassTrait;

    public const string ROUTE_INDEX = 'mails_index';

    public const string ROUTE_HTML = 'mails_html';

    /** Enough to read the latest; older ones stay in the table. */
    private const int LIMIT = 200;

    private const string QUERY_ID = 'id';

    #[Route(path: '', name: 'index')]
    public function index(
        Request $request,
        MailRepository $journal
    ): Response {
        $mails = $journal->findLatest(self::LIMIT);
        $id = $request->query->getString(self::QUERY_ID);

        return $this->renderPage('index', [
            'mails' => $mails,
            'selected' => '' !== $id ? $this->findMail($journal, $id, false) : ($mails[0] ?? null),
        ]);
    }

    /**
     * The HTML body alone, for the page's iframe, sandboxed as the
     * mailbox's is.
     */
    #[Route(path: '{id}/html', name: 'html', format: 'mail')]
    public function html(
        string $id,
        MailRepository $journal
    ): Response {
        return new Response((string) $this->findMail($journal, $id)?->getHtml(), Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => 'sandbox allow-popups allow-popups-to-escape-sandbox',
        ]);
    }

    private function findMail(
        MailRepository $journal,
        string $id,
        bool $required = true
    ): ?Mail {
        $mail = Uuid::isValid($id) ? $journal->find($id) : null;

        if (null === $mail && $required) {
            throw new NotFoundHttpException('No such mail in the journal.');
        }

        return $mail;
    }
}
