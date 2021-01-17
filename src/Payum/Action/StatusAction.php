<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;

final class StatusAction implements ActionInterface
{
    public function execute($request): void
    {
        // TODO: Add constant variables for status

        RequestNotSupportedException::assertSupports($this, $request);

        $payment = $request->getFirstModel();
        $details = $payment->getDetails();

        $status = $details['status'] ?? null;

        if(!$status) {
            $request->markNew();
            return;
        }

        if($status === 'Completed') {
            $request->markCaptured();
            return;
        }

        if($status === 'Pending') {
            $request->markPending();
            return;
        }

        if($status === 'Failed') {
            $request->markFailed();
            return;
        }


        $request->markUnknown();
    }

    public function supports($request): bool
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getFirstModel() instanceof SyliusPaymentInterface
        ;
    }
}
