<?php

namespace Wexample\SymfonyMailDs\DevMenu;

use Wexample\SymfonyDesignSystem\Interface\DevMenuProviderInterface;
use Wexample\SymfonyMail\Twig\MailExtension;

/**
 * The development mailbox, opened beside the page, which stays where it is.
 * Routed in dev and test only: elsewhere, nothing.
 */
class MailboxDevMenuProvider implements DevMenuProviderInterface
{
    public function __construct(
        private readonly MailExtension $mail,
    ) {
    }

    public function getDevMenuItems(): array
    {
        $url = $this->mail->mailboxUrl();

        return $url ? [[
            'icon' => 'ph:bold/envelope-simple',
            'label' => 'WexampleSymfonyMailDsBundle.pages.mailbox.index::page_title',
            'href' => $url,
            'new_window' => true,
        ]] : [];
    }
}
