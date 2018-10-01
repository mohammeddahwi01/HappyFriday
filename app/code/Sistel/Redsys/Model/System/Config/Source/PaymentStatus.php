<?php
/**
 * Sistel
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the sistel.com license
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Paymen Methods - Redsys
 * @package     Sistel_Redsys
 * @author      Juan Pedro Barba Soler || Developer at Sistel, Servicios Informáticos de Software y Telecomunicaciones
 * @copyright   Copyright (c) 2016 Sistel (http://www.sistel.es/)
 * @license     See root folder of this extension
**/

namespace Sistel\Redsys\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Sales\Model\Order;

class PaymentStatus implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => Order::STATE_PENDING_PAYMENT, 'label'=>__('Pending Payment')),
            array('value' => Order::STATE_PAYMENT_REVIEW, 'label'=>__('Payment Review')),
            array('value' => Order::STATE_PROCESSING, 'label'=>__('Processing')),
            array('value' => Order::STATE_HOLDED, 'label'=>__('Holded')),
            array('value' => Order::STATE_NEW, 'label'=>__('New')),
        );
    }
}
