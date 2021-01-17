<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\Payum\Action;

use Turquoise\SyliusPayBrightPlugin\Payum\SyliusApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Symfony\Component\HttpFoundation\Response;

final class CaptureAction implements ActionInterface, ApiAwareInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /** @var Client */
    private $client;

    /** @var SyliusApi */
    private $api;

    private $httpRequest;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function execute($request)
    {
        // TODO: Finish the isPayBrightRequestValid function
        // TODO: Clean this class
        // TODO: Handle when a user cancelss the PayBright payment
        // TODO: Rename some classes

        RequestNotSupportedException::assertSupports($this, $request);

        /** @var SyliusPaymentInterface $payment */
        $payment = $request->getModel();

        $this->httpRequest = new GetHttpRequest();
        $this->gateway->execute($this->httpRequest);

        if($this->isPayBrightRequest()) {
            $status = 'Failed';

            if($this->isPayBrightRequestValid()) {
                $status = $this->httpRequest->query['x_result'];
            }

            $payment->setDetails([ 'status' => $status ]);
        } else {
            $response = $this->client->request('POST', 'https://sandbox.paybright.com/CheckOut/ApplicationForm.aspx', [
                'body' => http_build_query($this->preparePayBrightData($request)),
            ]);

            $response = $response->getBody()->getContents();

            throw new HttpResponse($response);
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof SyliusPaymentInterface
        ;
    }

    public function setApi($api): void
    {
        if (!$api instanceof SyliusApi) {
            throw new UnsupportedApiException('Not supported. Expected an instance of ' . SyliusApi::class);
        }

        $this->api = $api;
    }

    private function isPayBrightRequest() {
        return isset($this->httpRequest->query['x_result']) && isset($this->httpRequest->query['x_signature']);
    }

    private function isPayBrightRequestValid() {
        return true;
    }

    private function preparePayBrightData($request) {
        $payment = $request->getModel();
        $order = $payment->getOrder();
        $customer = $order->getCustomer();
        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $data = [
            'x_account_id' => $this->api->getApiKey(),
            'x_amount' => $payment->getAmount() / 100,
            'x_currency' => $payment->getCurrencyCode(),
            'x_customer_billing_address1' => $billingAddress->getStreet(),
            'x_customer_billing_city' => $billingAddress->getCity(),
            'x_customer_billing_country' => $billingAddress->getCountryCode(),
            'x_customer_billing_phone' => $billingAddress->getPhoneNumber(),
            'x_customer_billing_state' => 'QC',//$billingAddress->getProvinceName(),
            'x_customer_billing_zip' => $billingAddress->getPostcode(),
            'x_customer_email' => $customer->getEmail(),
            'x_customer_first_name' => $billingAddress->getFirstName(),
            'x_customer_last_name' => $billingAddress->getLastName(),
            'x_customer_phone' => $customer->getPhoneNumber() ?: $billingAddress->getPhoneNumber(),
            'x_customer_shipping_address1' => $shippingAddress->getStreet(),
            'x_customer_shipping_city' => $shippingAddress->getCity(),
            'x_customer_shipping_country' => $shippingAddress->getCountryCode(),
            'x_customer_shipping_first_name' => $shippingAddress->getFirstName(),
            'x_customer_shipping_last_name' => $shippingAddress->getLastName(),
            'x_customer_shipping_phone' => $shippingAddress->getPhoneNumber(),
            'x_customer_shipping_state' => 'QC',//$shippingAddress->getProvinceName(),
            'x_customer_shipping_zip' => $shippingAddress->getPostcode(),
            'x_reference' => $order->getNumber(),
            'x_shop_country' => 'CA',
            'x_shop_name' => 'Mobile Expert', // TODO: To change
            'x_test' => 'true',
            'x_url_callback' => $request->getToken()->getTargetUrl(),
            'x_url_cancel' => $request->getToken()->getTargetUrl(),
            'x_url_complete' => $request->getToken()->getTargetUrl()
        ];

        $data['x_signature'] = $this->generatePayBrightSignature($data);

        return $data;
    }

    private function generatePayBrightSignature(array $data) {
        // TODO: Change all of this... took from the PayBright API doc
        $signatureString = '';

        foreach (explode('&', http_build_query($data)) as $chunk) {
            $param = explode("=", $chunk);
            if ($param && $param[1] != '') {
                $signatureString = $signatureString . urldecode($param[0]) . urldecode($param[1]);
            }
        }

        return hash_hmac('sha256', $signatureString, $this->api->getApiToken());
    }
}
