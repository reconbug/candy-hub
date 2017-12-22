<?php

namespace GetCandy\Api\Payments\Providers;

use Braintree_Configuration;
use Braintree_ClientToken;
use Braintree_PaymentMethodNonce;
use Braintree_Exception_NotFound;
use Braintree_Transaction;
use Braintree_Test_Transaction;

class Braintree extends AbstractProvider
{
    public function __construct()
    {
        Braintree_Configuration::environment(config('getcandy.payments.environment'));
        Braintree_Configuration::merchantId(config('services.braintree.merchant_id'));
        Braintree_Configuration::publicKey(config('services.braintree.key'));
        Braintree_Configuration::privateKey(config('services.braintree.secret'));
    }

    public function getName()
    {
        return 'Braintree';
    }

    public function getClientToken()
    {
        return Braintree_ClientToken::generate();
    }

    public function threeDSecured()
    {
        return config('services.braintree.3D_secure');
    }

    //TODO: REMOVE BEFORE LIVE
    private function settle($sale)
    {
        if (!app()->isLocal()) {
            return $sale;
        }
        return Braintree_Test_Transaction::settle($sale->transaction->id);
    }

    public function validateToken($token)
    {
        try {
            $token = Braintree_PaymentMethodNonce::find($token);
            $info = $token->threeDSecureInfo;
            if ($token->consumed || (empty($info) && $this->threeDSecured())) {
                return false;
            }
        } catch (Braintree_Exception_NotFound $e) {
            return false;
        }
        return true;
    }

    protected function getMerchant($currency)
    {
        return config(
            'services.braintree.merchants.' . strtolower($currency),
            config('services.braintree.merchants.default')
        );
    }

    public function charge($token, $order)
    {
        $merchant = $this->getMerchant($order->currency);

        $billing = $order->billingDetails;
        $shipping = $order->shippingDetails;

        $sale = Braintree_Transaction::sale([
            'amount' => $order->total,
            'paymentMethodNonce' => $token,
            'merchantAccountId' => $merchant,
            'customer' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname']
            ],
            'billing' => [
                'firstName' => $billing['firstname'],
                'lastName' => $billing['lastname'],
                'locality' => $billing['city'],
                'region' =>   $billing['county'] ?: $billing['state'],
                'postalCode' =>   $billing['zip'],
                'streetAddress' => $billing['address']
            ],
            'shipping' => [
                'firstName' => $shipping['firstname'],
                'lastName' => $shipping['lastname'],
                'locality' => $shipping['city'],
                'region' => $shipping['county'] ? : $shipping['state'],
                'postalCode' =>   $shipping['zip'],
                'streetAddress' => $shipping['address']
            ],
            'options' => [
                'submitForSettlement' => true
            ]
        ]);
    
        return $sale;
    }

    public function refund($token, $amount = null)
    {
        $transaction = Braintree_Transaction::refund($token, $amount);
        return $transaction;
    }

    public function void($token)
    {
        $result = Braintree_Transaction::void($token);
        return $result;
    }
}