/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

define(['jquery', "Magento_Ui/js/modal/confirm"], function ($, confirm) {
    'use strict';
    return {
        current_type: "xml",
        current_value: 1,
        CodeMirrorTxt: null,
        CodeMirrorBodyPattern: null,
        CodeMirrorHeaderPattern: null,
        CodeMirrorFooterPattern: null,
        updateType: function (automatic) {
            var manual = false;
            if (automatic) {
                if (this.current_type !== this.getType()) {
                    confirm({
                        title: "",
                        content: "Changing file type from/to xml will clear all your settings. Do you want to continue ?",
                        actions: {
                            confirm: function () {
                                manual = true;
                                this.updateTypeContinue(manual);
                            }.bind(this),
                            cancel: function () {
                                manual = false;
                                $('#type').val(this.current_value);
                                this.updateTypeContinue(manual);
                            }.bind(this)
                        }
                    });
                }
            } else {
                this.updateTypeContinue(manual);
            }
        },
        updateTypeContinue: function (manual) {
            var displayForXml = new Array("header", "body", "footer", "enclose_data");
            var displayForCsv = new Array("extra_header", "include_header", "extra_footer", "separator", "protector", "escaper");
            var toEmpty = new Array("header", "body", "footer", "extra_header", "extra_footer");

            this.current_type = this.getType();
            this.current_value = $("#type").val();

            if (manual) { // seulement si changement manuel
                // empty all text field
                toEmpty.each(function (id) {
                    $('#' + id).val("");
                });

                if (this.isXML()) {
                    $("#fields").remove();
                }
            }

            if (!this.isXML()) { // others
                displayForXml.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'none'});
                });
                displayForCsv.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'block'});
                });
                this.displayTxtTemplate();
            } else { // XML
                displayForXml.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'block'});
                });
                displayForCsv.each(function (id) {
                    $('#' + id).parent().parent().css({display: 'none'});
                });
            }

            if (manual) {
                this.CodeMirrorBodyPattern.setValue('');
                this.CodeMirrorHeaderPattern.setValue('');
                this.CodeMirrorFooterPattern.setValue('');
                this.CodeMirrorBodyPattern.refresh();
                this.CodeMirrorHeaderPattern.refresh();
                this.CodeMirrorFooterPattern.refresh();
            }
        },
        getType: function () {
            if ($('#type').val() === "1")
                return "xml";
            else
                return "txt";
        },
        isXML: function (type) {
            if (typeof type === "undefined") {
                return $('#type').val() === "1";
            } else {
                return type === "1";
            }
        },
        displayTxtTemplate: function () {
            if ($("#fields").length === 0) {
                var content = "<div id='fields'>";
                content += "     Column name";
                content += "      <span style='margin-left:96px'>Pattern</span>";
                content += "<ul class='fields-list' id='fields-list'></ul>";
                content += "<button type='button' class='add-field' onclick='require([\"oet_template\"], function (template) {template.addField(\"\",\"\",true); });'>Insert a new field</button>";
                content += "<div class='overlay-txtTemplate'>\n\
                            <div class='container-txtTemplate'> \n\
                            <textarea id='codemirror-txtTemplate'>&nbsp;</textarea>\n\
                            <button type='button' class='validate' onclick='require([\"oet_template\"], function (template) {template.popup_validate(); });'>Validate</button>\n\
                            <button type='button' class='cancel' onclick='require([\"oet_template\"], function (template) {template.popup_close(); });'>Cancel</button>\n\
                            </div>\n\
                            </div>";
                content += "</div>";
                $(content).insertAfter("#extra_header");
                $("#footer").val(".");

                this.CodeMirrorTxt = CodeMirror.fromTextArea(document.getElementById('codemirror-txtTemplate'), {
                    matchBrackets: true,
                    mode: "application/x-httpd-php",
                    indentUnit: 2,
                    indentWithTabs: false,
                    lineWrapping: true,
                    lineNumbers: false,
                    styleActiveLine: true
                });

                $("#fields-list").sortable({
                    revert: true,
                    axis: "y",
                    stop: function () {
                        this.fieldsToJson();
                    }.bind(this)
                });

                this.jsonToFields();
            }

        },
        addField: function (header, body, refresh) {
            var content = "<li class='txt-fields'>";
            content += "   <input class='txt-field  header-txt-field input-text ' type='text' value=\"" + header.replace(/"/g, "&quot;") + "\"/>";
            content += "   <input class='txt-field  body-txt-field input-text ' type='text' value=\"" + body.replace(/"/g, "&quot;") + "\"/>";
            content += "   <button class='txt-field remove-field'>\u2716</button>";
            content += "</li>";
            $("#fields-list").append(content);
            if (refresh) {
                this.fieldsToJson();
            }
        },
        removeField: function (elt) {
            $(elt).parents('li').remove();
            this.fieldsToJson();
        },
        fieldsToJson: function () {
            var data = new Object;
            data.header = new Array;
            var c = 0;
            $('INPUT.header-txt-field').each(function () {
                data.header[c] = $(this).val();
                c++;
            });
            data.body = new Array;
            c = 0;
            $('INPUT.body-txt-field').each(function () {
                data.body[c] = $(this).val();
                c++;
            });
            var pattern = '{"body":' + JSON.stringify(data.body) + "}";
            var header = '{"header":' + JSON.stringify(data.header) + "}";
            $("#body").val(pattern);
            $("#header").val(header);
            this.CodeMirrorBodyPattern.setValue(pattern);
            this.CodeMirrorHeaderPattern.setValue(header);
            this.CodeMirrorBodyPattern.refresh();
            this.CodeMirrorHeaderPattern.refresh();
        },
        jsonToFields: function () {
            var data = new Object;

            var header = [];
            if ($('#header').val() !== '') {
                try {
                    header = $.parseJSON($('#header').val()).header;
                } catch (e) {
                    header = [];
                }
            }

            var body = [];
            if ($('#body').val() !== '') {
                try {
                    body = $.parseJSON($('#body').val()).body;
                } catch (e) {
                    body = [];
                }
            }

            data.header = header;
            data.body = body;

            var i = 0;
            data.body.each(function () {
                this.addField(data.header[i], data.body[i], false);
                i++;
            }.bind(this));
        },
        popup_current: null,
        popup_close: function () {
            $(".overlay-txtTemplate").css({"display": "none"});
        },
        popup_validate: function () {
            $(this.popup_current).val(this.CodeMirrorTxt.getValue());
            this.current = null;
            this.popup_close();
            this.fieldsToJson();
        },
        popup_open: function (content, field) {
            $(".overlay-txtTemplate").css({"display": "block"});
            this.CodeMirrorTxt.refresh();
            this.CodeMirrorTxt.setValue(content);
            this.popup_current = field;
            this.CodeMirrorTxt.focus();
        },
        generate: function () {
            confirm({
                title: "Generate a profile",
                content: "Generate a profile can take a while. Are you sure you want to generate it now ?",
                actions: {
                    confirm: function () {
                        $('#generate_i').val('1');
                        $('#edit_form').submit();
                    }
                }
            });
        }
    };

});

