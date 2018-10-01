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

class PaymentMethods implements ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => 0, 'label'=>__('All')),
            array('value' => "C", 'label'=>__('Only Cc')),
		        array('value' => "T", 'label'=>__('Cc and Iupay')),
        );
    }

	public function toArray()
	{
		return array(
			0 => __('All'),
			"C" => __('Only Cc'),
			"T" => __('Cc and Iupay'),
		);
	}
}
