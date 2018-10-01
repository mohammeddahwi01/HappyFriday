/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
window.onload = function () {
    require(["jquery", "mage/mage"], function ($) {
        $(function () {
            CodeMirror = CodeMirror.fromTextArea(document.getElementById('script'), {
                matchBrackets: true,
                mode: "application/x-httpd-php",
                indentUnit: 2,
                indentWithTabs: false,
                lineWrapping: true,
                lineNumbers: false,
                styleActiveLine: true,
            });

        })
    })
}