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

define(
    [
        'Sistel_Redsys/js/view/payment/method-renderer/redsys-method-abstract'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Sistel_Redsys/payment/redsys'
            }
        });
    }
);
