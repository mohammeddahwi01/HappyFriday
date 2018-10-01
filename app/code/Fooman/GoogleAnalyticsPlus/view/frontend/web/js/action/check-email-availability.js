/**
 * @author     Kristof Ringleff
 * @package    Fooman_GoogleAnalyticsPlus
 * @copyright  Copyright (c) 2015 Fooman Limited (http://www.fooman.co.nz)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
define(['mage/utils/wrapper'], function (wrapper) {
    'use strict';

    return function (checkEmailAction) {
        return wrapper.wrap(checkEmailAction, function (originalAction, deferred, email) {
            if (typeof(ga) != "undefined") {
                var urlToTrack = foomanGaBaseUrl + '/email-entered';
                if (foomanGaQuery.length > 0) {
                    urlToTrack += '?' + foomanGaQuery
                }
                ga('set', 'page', urlToTrack);
                ga('send', 'pageview');
            }
            return originalAction(deferred, email);
        });
    };
});
