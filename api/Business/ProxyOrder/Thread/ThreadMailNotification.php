<?php

namespace Business\ProxyOrder\Thread;

use Business\MailTheme\BaseMailThemeInterface;
use Data\ProxyOrder\Order\ProxyOrderRepositoryInterface;
use Data\ProxyOrder\Thread\ProxyOrderThreadRepositoryInterface;
use Data\User\UserRepositoryInterface;
use R2Packages\Framework\Application\Mail\MailServiceInterface;

class ThreadMailNotification implements ThreadMailNotificationInterface
{

    private MailServiceInterface $mailService;
    private BaseMailThemeInterface $baseMailTheme;
    private ProxyOrderRepositoryInterface $proxyOrderRepository;
    private ProxyOrderThreadRepositoryInterface $proxyOrderThreadRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        MailServiceInterface $mailService,
        BaseMailThemeInterface $baseMailTheme,
        ProxyOrderRepositoryInterface $proxyOrderRepository,
        ProxyOrderThreadRepositoryInterface $proxyOrderThreadRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->mailService = $mailService;
        $this->baseMailTheme = $baseMailTheme;
        $this->proxyOrderRepository = $proxyOrderRepository;
        $this->proxyOrderThreadRepository = $proxyOrderThreadRepository;
        $this->userRepository = $userRepository;
    }



    function sendToCustomer(int $proxyOrderId, int $threadId)
    {
        $thread = $this->proxyOrderThreadRepository->find($threadId);
        $order = $this->proxyOrderRepository->find($proxyOrderId);
        $userId = $order->user_id;
        $customer = $this->userRepository->find($userId);
        $to = $customer->email;
        $subject = '';
        $from = '';
        $body = $this->baseMailTheme->wrapTemplate('
        <p>Hello, {$customer->name}</p>
        <p>You have a new message from support.</p>
        <p>Message: {$thread->message}</p>
        <p>Thank you for your business.</p>
        ');

        $this->mailService->send($to, $subject, $from, $body);
    }
}
