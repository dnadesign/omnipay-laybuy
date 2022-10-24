<?php

namespace Omnipay\Laybuy\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends Response implements RedirectResponseInterface
{

    /**
     * @return string
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * @return bool
     */
    public function isRedirect()
    {
        return $this->isSuccessful();
    }

    /**
     * @return string|null
     */
    public function getRedirectUrl()
    {
        if ($this->isRedirect()) {
            return $this->data['paymentUrl'];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getPaymentUrl()
    {
        return isset($this->data['paymentUrl']) ? $this->data['paymentUrl'] : null;
    }

    /**
     * Required function from the interface.
     * Based on other modules, it is ok to return null
     */
    public function getRedirectData()
    {
        return null;
    }
}