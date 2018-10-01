/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
var OrdersExportTool = {
    _delete: function (order, profil, url) {
        if (confirm('Are you sure ?')) {
            data = {order: order, profil: profil};
            jQuery.ajax({
                url: url,
                type: 'POST',
                showLoader: true,
                data: data,
                success: function (data) {

                    jQuery('#orderexported-' + order + '-' + profil).find('a')[0].remove();
                    jQuery('#orderexported-' + order + '-' + profil).css({textDecoration: 'line-through'})
                }

            })
        }
    },
    _udpateExportTo: function (item_id, value, url) {

        data = {item_id: item_id, value: value};
        jQuery.ajax({
            url: url,
            data: data,
            type: 'post',
            onFailure: function () {
                alert("Error : can't update the item.")
            }

        })

    },
}
