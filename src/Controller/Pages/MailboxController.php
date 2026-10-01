<?php

namespace Wexample\SymfonyMailDs\Controller\Pages;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyDesignSystem\Attribute\MenuItem;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyMail\Class\MailboxMail;
use Wexample\SymfonyMail\Service\MailboxService;
use Wexample\SymfonyMail\Twig\MailExtension;
use Wexample\SymfonyMailDs\Traits\SymfonyMailDsBundleClassTrait;

/**
 * The mails the development mailbox kept (MAILER_DSN=mailbox://default),
 * newest first. Routed in dev and test only: in any other environment the
 * page does not exist. It is read before signing in — a sign-in link, a
 * code —, so an application guarding every path opens this one.
 */
#[Route(path: '/mailbox/', name: 'mailbox_', env: ['dev', 'test'])]
final class MailboxController extends AbstractPagesController
{
    use SymfonyMailDsBundleClassTrait;

    /**
     * The menu group of the mail pages: the mailbox, and the demo sending into it.
     */
    public const string MENU_GROUP = 'mail';

    public const string ROUTE_INDEX = MailExtension::ROUTE_MAILBOX;

    public const string ROUTE_HTML = 'mailbox_html';

    public const string ROUTE_RAW = 'mailbox_raw';

    public const string ROUTE_PURGE = 'mailbox_purge';

    public const string CSRF_PURGE = 'mailbox_purge';

    private const string QUERY_RECIPIENT = 'to';

    private const string QUERY_ID = 'id';

    #[Route(path: '', name: 'index')]
    #[MenuItem(self::MENU_GROUP, 0)]
    public function index(
        Request $request,
        MailboxService $mailbox
    ): Response {
        $recipient = $request->query->getString(self::QUERY_RECIPIENT) ?: null;
        $mails = $mailbox->list($recipient);
        $id = $request->query->getString(self::QUERY_ID);

        return $this->renderPage('index', [
            'mails' => $mails,
            'selected' => '' !== $id ? $mailbox->find($id) : ($mails[0] ?? null),
            'recipient' => $recipient,
            'recipients' => $mailbox->listRecipients(),
        ]);
    }

    /**
     * The HTML body alone, for the page's iframe. The sandbox policy gives it
     * an origin of its own, without scripts or forms: a mail's HTML neither
     * wears the application's styles nor runs as the application.
     */
    #[Route(path: '{id}/html', name: 'html', requirements: ['id' => MailboxService::ID_REQUIREMENT])]
    public function html(
        string $id,
        MailboxService $mailbox
    ): Response {
        return new Response((string) $this->findMail($mailbox, $id)->html, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => 'sandbox allow-popups allow-popups-to-escape-sandbox',
        ]);
    }

    /**
     * The mail as it would have left, to open in a mail client.
     */
    #[Route(path: '{id}/raw', name: 'raw', requirements: ['id' => MailboxService::ID_REQUIREMENT])]
    public function raw(
        string $id,
        MailboxService $mailbox
    ): Response {
        return new Response((string) $mailbox->getRaw($this->findMail($mailbox, $id)->id), Response::HTTP_OK, [
            'Content-Type' => 'message/rfc822',
            'Content-Disposition' => 'attachment; filename="'.$id.'.eml"',
        ]);
    }

    #[Route(path: 'purge', name: 'purge', methods: [Request::METHOD_POST])]
    public function purge(
        Request $request,
        MailboxService $mailbox
    ): RedirectResponse {
        if ($this->isCsrfTokenValid(self::CSRF_PURGE, $request->request->getString('_token'))) {
            $mailbox->purge();
        }

        return $this->redirectToRoute(self::ROUTE_INDEX);
    }

    private function findMail(
        MailboxService $mailbox,
        string $id
    ): MailboxMail {
        return $mailbox->find($id) ?? throw new NotFoundHttpException('No such mail in the mailbox.');
    }
}
