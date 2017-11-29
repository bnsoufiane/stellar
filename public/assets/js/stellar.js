(function () {

    var TYPE_PREGNANCY = 1;
    var TYPE_WC15_AHC = 4;
    var TYPE_WC15_KF = 5;

    $(document).ajaxStop($.unblockUI);
    //$(document).ajaxStart($.blockUI);

    function init_date_pickers() {
        $(".datepicker:not(.disallow_future_dates)").removeAttr('id').datepicker({
                    //maxDate: new Date(),
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });

        $(".datepicker.disallow_future_dates").removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });
    }

    init_date_pickers();

    $('body').on('blur', '.disallow_future_dates', function (event) {

        var date = new Date($(this).val())
        var now = new Date();
        if (date > now) {
            $(this).parent().addClass("has-error future-date");

            if ($(this).parent().find('.help-block').length < 1) {
                $(this).parent().append('<span class="help-block">Future Dates are not allowed for this field.</span>');
            }


            $("html, body").animate({
                scrollTop: $(this).offset().top - 300
            }, 500);

        } else {
            if ($(this).parent().find('.help-block').length > 0) {
                $(this).parent().find('.help-block').remove();
                $(this).parent().removeClass("has-error future-date");
            }
        }
    });

    function actual_visit_date_field_changed(input) {
        if ($(input).hasClass('actual_visit_date_field')) {
            if ($(input).val().length < 1) {
                $('.actual_visit_date_related_field').each(function () {
                    $(this).attr('disabled', 'disabled');
                });
            } else {
                $('.actual_visit_date_related_field').each(function () {
                    $(this).removeAttr('disabled');
                });
            }

            if ($('.julian_date_field').length > 0) {
                var actual_visit_date = new Date($(input).val())
                $('.julian_date_field').val(actual_visit_date.getJulian());
            }
        }
    }

    $('.datepicker').each(function () {
        var _date = $(this).val();
        $(this).datepicker("option", "dateFormat", 'mm/dd/yy');
        $(this).datepicker("setDate", _date);
    });

    //$(manual_outreach_field_changed)

    $('body').on('change', '.manual_outreach_field', function (event) {
        manual_outreach_field_changed(this);
    });

    function manual_outreach_field_changed(input) {
        if ($(input).hasClass('manual_outreach_field')) {
            if ($(input).is(':checked')) {
                $(input).parents('.manual_outreach_row').find('.manual_outreach_related_field').each(function () {
                    $(this).removeAttr('disabled');
                });
            } else {
                $(input).parents('.manual_outreach_row').find('.manual_outreach_related_field').each(function () {
                    $(this).attr('disabled', 'disabled');
                });
            }
        }
    }

    $('body').on('change', '.gift_card_returned', function (event) {
        gift_card_returned_changed(this);
    });

    function gift_card_returned_changed(input) {
        if ($(input).is(':checked')) {
            $(input).parent().siblings('.gift_card_returned_notes, .gift_card_returned_date').find('.gift_card_returned_related_field').each(function () {
                $(this).removeAttr('disabled');
            });
            $(input).parents('.scheduled_visit_fields_row').find('.gift_card_returned_related_field').each(function () {
                $(this).removeAttr('disabled');
            });

        } else {
            $(input).parent().siblings('.gift_card_returned_notes, .gift_card_returned_date').find('.gift_card_returned_related_field').each(function () {
                $(this).attr('disabled', 'disabled');
            });
            $(input).parents('.scheduled_visit_fields_row').find('.gift_card_returned_related_field').each(function () {
                $(this).attr('disabled', 'disabled');
            });
        }
    }

    if ($('#mother_id').length > 0) {
        if ($('#items_pool li[program_type="WC15-AHC"]').length > 0) {
            $('#mother_id').parent().show();
        } else {
            if (!$('#mother_id').parent().hasClass('has-error')) {
                $('#mother_id').parent().hide();
            }
        }

    }


    /*$( document ).ajaxStart(function() {
     $( ".log" ).text( "Triggered ajaxStart handler." );
     });*/

    $('body').on('click', 'a.add_phone', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="phone_number_ids[]" type="hidden" value="0">'
                + '<input placeholder="Phone Number" class="form-control phones_input phone_mask" name="phone_number[]" type="text" <a></a>'
                + '<a href="javascript:void(0);" class="actions_button add-option add_phone"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_phone"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_phone', function (event) {
        $(this).parent().remove();
    });

    $('body').on('click', 'a.add_discontinue_tracking_reason', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="reason_ids[]" type="hidden" value="0">'
                + '<input placeholder="Discontinue Tracking Reason" class="form-control discontinue_tracking_reasons_input" name="reason[]" type="text" <a></a>'
                + '<a href="javascript:void(0);" class="actions_button add-option add_discontinue_tracking_reason"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_discontinue_tracking_reason"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_discontinue_tracking_reason', function (event) {
        $(this).parent().remove();
    });

    $('body').on('click', 'a.add_incentive', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="incentive_type_ids[]" type="hidden" value="0">'
                + '<input placeholder="Incentive Type" class="form-control incentives_input" name="incentive_type[]" type="text" <a></a>'
                + '<a href="javascript:void(0);" class="actions_button add-option add_incentive"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_incentive"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_incentive', function (event) {
        $(this).parent().remove();
    });


    $('body').on('click', 'a.add_how_did_you_hear', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="label_ids[]" type="hidden" value="0">'
                + '<input placeholder="How Did You Hear" class="form-control how_did_you_hears_input" name="label[]" type="text" <a></a>'
                + '<a href="javascript:void(0);" class="actions_button add-option add_how_did_you_hear"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_how_did_you_hear"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_how_did_you_hear', function (event) {
        $(this).parent().remove();
    });


    $('body').on('click', 'a.add_member_completed_required_visit_dates', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="member_completed_required_visit_dates_ids[]" type="hidden" value="0">'
                + '<input placeholder="Member Completed Required Visit Dates" class="form-control member_completed_required_visit_dates_input" name="member_completed_required_visit_dates[]" type="text" <a></a>'
                + '<a href="javascript:void(0);" class="actions_button add-option add_member_completed_required_visit_dates"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_member_completed_required_visit_dates"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_member_completed_required_visit_dates', function (event) {
        $(this).parent().remove();
    });


    $('body').on('click', 'a.add_outreach_code', function (event) {

        var _obj = '<div class="input-group input-group-lg sepH_a">'
                + '<input name="code_ids[]" type="hidden" value="0">'
                + '<input placeholder="Outreach Code" class="form-control outreach_codes_input" name="code[]" type="text">'
                + '<input placeholder="Code Name" class="form-control outreach_codes_name_input" name="code_name[]" type="text">'
                + '<a href="javascript:void(0);" class="actions_button add-option add_outreach_code"><i class="icon_plus_alt"></i></a>'
                + '<a href="javascript:void(0);" class="actions_button remove-option remove_outreach_code"><i class="icon_minus_alt"></i></a>'
                + '</div>';
        $(_obj).insertBefore('div.sepH_c.text-right');
    });

    $('body').on('click', 'a.remove_outreach_code', function (event) {
        $(this).parent().remove();
    });


    // Add remove function to table items.
    $('body').on('click', '[data-action="remove"]', function () {

        $(this).parents('.btn-group.open').find('.dropdown-toggle').dropdown('toggle');

        var row = $(this).parents('tr');
        var _href = $(this).attr('href');

        var _obj = $("#dialog-confirm");

        if ($(this).hasClass('delete_actual_visit')) {
            _obj = $("#dialog-confirm-visit");
        }

        $(_obj).dialog({
            resizable: false,
            height: 170,
            width: 360,
            modal: true,
            buttons: {
                "Delete": function () {
                    $.blockUI();
                    $.ajax({
                        url: _href,
                        method: 'DELETE',
                        dataType: 'html'
                    }).done(function (response) {
                        response = JSON.parse(response);

                        if (response.error) {
                            $("#couldnt_delete").dialog({modal: true});
                        } else {
                            row.remove();
                        }
                    });

                    $(this).dialog("close");
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });
        return false;
    });


    $('body').on('click', 'a.remove_patient', function (event) {
        var row = $(this).parents('tr');
        var _href = $(this).attr('data_href');

        var data = {
            "patient_id": $(this).attr('patient_id')
        }

        $("#dialog-confirm").dialog({
            resizable: false,
            height: 170,
            width: 360,
            modal: true,
            buttons: {
                "Delete": function () {
                    $.blockUI();
                    $.ajax({
                        url: _href,
                        type: 'delete',
                        dataType: 'json',
                        data: data
                    }).done(function (response) {
                        if (response.error) {
                            $("#couldnt_delete").dialog({modal: true});
                        } else {
                            row.remove();
                        }
                    });

                    $(this).dialog("close");
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });
        return false;

    });


    $('body').on('click', 'a[data-target="#programs_modal"]', function (event) {
        $('#programs_modal input[type="checkbox"]').each(function () {
            $(this).removeAttr('checked');
        });
    });

    $('body').on('click', '#choose_items', function (event) {

        var _items_obj = $(this).attr('items-obj');

        $('#items_modal input[type="checkbox"]').each(function () {
            if ($(this).is(':checked')) {
                var _program_type = $(this).parent().siblings('td').attr('program_type');
                if (_items_obj === "programs_id" && _program_type === "Pregnancy") {
                    insertItem($(this).val(), $(this).parent().siblings('td').html(), _items_obj, _program_type);
                    pregnancyProgramToggled(true);
                }
                else if (_items_obj === "programs_id" && _program_type === "WC15-AHC") {
                    insertItem($(this).val(), $(this).parent().siblings('td').html(), _items_obj, _program_type);
                    WC15ProgramToggled(true);
                }
                else {
                    insertItem($(this).val(), $(this).parent().siblings('td').html(), _items_obj);
                }
            }
        });
    });

    $('body').on('click', 'ul .remove_pool_item', function (event) {
        var _items_obj = $(this).siblings('[type="hidden"]').attr('name');
        var _program_type = $(this).parent().attr('program_type');

        if (_items_obj === "programs_id[]" && typeof _program_type !== undefined && _program_type === 'Pregnancy') {
            pregnancyProgramToggled(false);
        }
        else if (_items_obj === "programs_id[]" && typeof _program_type !== undefined && _program_type === 'WC15-AHC') {
            WC15ProgramToggled(false);
        }


        $(this).parents('li').remove();
    });

    function insertItem(id, label, _items_obj, _program_type) {

        if ($('#items_pool ul input[value="' + id + '"]').length > 0) {
            return;
        }

        var _attr = '';
        if (typeof _program_type !== undefined && _program_type === "Pregnancy") {
            _attr = 'program_type="Pregnancy"';
        }

        else if (typeof _program_type !== undefined && _program_type === "WC15-AHC") {
            _attr = 'program_type="WC15-AHC"';
        }

        var _obj = '<li ' + _attr + '><span>' + label + '</span>&nbsp;'
                + '<a class="remove_pool_item remove-option"><i class="icon_minus_alt"></i></a>'
                + '<input type="hidden" name="' + _items_obj + '[]" value="' + id + '" /></li>';

        $(_obj).appendTo($('#items_pool ul'));
    }

    function pregnancyProgramToggled(added) {
        if (added) {
            $('#pregnancy_fields').show();
        } else {
            $('#pregnancy_fields').hide();
        }
    }

    function WC15ProgramToggled(added) {
        if (added) {
            $('#mother_id').parent().show();
        } else {
            $('#mother_id').parent().hide();
        }
    }

    /*regions begin */

    $('.import_patients').on('click', function (e) {
        $.blockUI();
        $.ajax({
            url: '/admin/regions/' + $(this).attr('region_id') + '/import_patients',
            method: 'GET',
            //data: data_preview,
            dataType: 'html'
        }).done(function (response) {

            var modalContent = '<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"> \
                <div class="modal-dialog modal-lg" style="width: 80%;"> \
                <div class="modal-content"> \
                <div class="modal-header"> \
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button> \
                <h4 class="modal-title">Import Patients</h4> \
                </div> \
                <div class="modal-body" style="height: 800px;"> \
                <iframe style="width: 100%; height: 100%; border: none;" \
                id="import-patients-iframe" name="import-patients-iframe"></iframe> \
                </div> \
                </div> \
                </div> \
                </div>';
            var iframe = $(modalContent)
                    .appendTo(document.body)
                    .modal()
                    .find('iframe')[0];

            iframe.contentWindow.contents = response;
            iframe.src = 'javascript:window["contents"]';

        });
    });

    $('.import_visit_dates').on('click', function (e) {
        $.blockUI();

        var _insurance_company = $(this).attr('insurance_company');
        var _region = $(this).attr('region');
        var _program = $(this).attr('program');

        $.ajax({
            url: '/admin/regions/' + $(this).attr('region_id') + '/programs/' + $(this).attr('program_id') + '/import_visit_dates',
            method: 'GET',
            //data: data_preview,
            dataType: 'html'
        }).done(function (response) {

            var modalContent = '<div class="modal fade bs-example-modal-lg" id="importvisitdatesmodel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"> \
                <div class="modal-dialog modal-lg" style="width: 80%;"> \
                <div class="modal-content"> \
                <div class="modal-header"> \
                <button onclick="javascript:window.location.reload()" type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button> \
                <h4 class="modal-title">Import Visit Dates : ' + _insurance_company + ' / ' + _region + ' / ' + _program;
            modalContent += '</h4> \
                </div> \
                <div class="modal-body" style="height: 800px;"> \
                <iframe style="width: 100%; height: 100%; border: none;" \
                id="import-visit-dates-iframe" name="import-visit-dates-iframe"></iframe> \
                </div> \
                </div> \
                </div> \
                </div>';
            var iframe = $(modalContent)
                    .appendTo(document.body)
                    .modal()
                    .find('iframe')[0];

            iframe.contentWindow.contents = response;
            iframe.src = 'javascript:window["contents"]';
            $('#importvisitdatesmodel').on('hide.bs.modal', function(e) {
                window.location.reload()
            });
        });
    });

    $('.import_practice_groups').on('click', function (e) {
        $.blockUI();
        $.ajax({
            url: '/admin/regions/' + $(this).attr('region_id') + '/import_practice_groups',
            method: 'GET',
            //data: data_preview,
            dataType: 'html'
        }).done(function (response) {

            var modalContent = '<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"> \
                <div class="modal-dialog modal-lg" style="width: 80%;"> \
                <div class="modal-content"> \
                <div class="modal-header"> \
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button> \
                <h4 class="modal-title">Import Practice Groups</h4> \
                </div> \
                <div class="modal-body" style="height: 800px;"> \
                <iframe style="width: 100%; height: 100%; border: none;" \
                id="import-patients-iframe" name="import-patients-iframe"></iframe> \
                </div> \
                </div> \
                </div> \
                </div>';
            var iframe = $(modalContent)
                    .appendTo(document.body)
                    .modal()
                    .find('iframe')[0];

            iframe.contentWindow.contents = response;
            iframe.src = 'javascript:window["contents"]';

        });
    });

    $('.import_doctors').on('click', function (e) {
        $.blockUI();
        $.ajax({
            url: '/admin/regions/' + $(this).attr('region_id') + '/import_doctors',
            method: 'GET',
            //data: data_preview,
            dataType: 'html'
        }).done(function (response) {

            var modalContent = '<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"> \
                <div class="modal-dialog modal-lg" style="width: 80%;"> \
                <div class="modal-content"> \
                <div class="modal-header"> \
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button> \
                <h4 class="modal-title">Import Doctors</h4> \
                </div> \
                <div class="modal-body" style="height: 800px;"> \
                <iframe style="width: 100%; height: 100%; border: none;" \
                id="import-patients-iframe" name="import-patients-iframe"></iframe> \
                </div> \
                </div> \
                </div> \
                </div>';
            var iframe = $(modalContent)
                    .appendTo(document.body)
                    .modal()
                    .find('iframe')[0];

            iframe.contentWindow.contents = response;
            iframe.src = 'javascript:window["contents"]';

        });
    });

    $('.dropzone').each(function () {
        create_dropzone(this);
    });

    function create_dropzone(obj) {
        var button = $(obj).find('.btn');
        var file = $(obj).find('input[type=hidden]');

        $('body').on('click', '.import_visit_dates_drop_zone .btn', function (event) {
            if (ss._disabled) {

                var message = "Are you sure you want to proceed with these values?<br/><br/>";
                message += "Date of Service: " + $('#date_of_service').val() + "<br/>";
                if ($('#metric').length > 0) {
                    message += "Metric: " + $('#metric :selected').text() + "<br/>";
                }

                $("<p>" + message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "Proceed": function () {
                            ss.enable();
                            $('.import_visit_dates_drop_zone .btn').text('Choose file');
                            $('.import_visit_dates_drop_zone .btn').removeClass('btn-warning').addClass('btn-success');
                            $(this).dialog("close");
                        },
                        "Cancel": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function (event, ui) {
                    }
                });
            }

        });

        //*
        ss = new ss.SimpleUpload({
            button: button,
            url: '/admin/regions/upload',
            name: 'upload',
            responseType: 'json',
            allowedExtensions: $(obj).data('uploadExtensions').split(','),
            onSubmit: function () {
                button.attr('disabled', 'disabled');
                $.blockUI();
            },
            onComplete: function (filename, response) {
                button.removeAttr('disabled');
                $.unblockUI();

                // Store a filename of the uploaded file.
                file.val(response.filename).change();

                if ($(file).parent().hasClass('upload_no_ajax')) {
                    return;
                }

                if ($(obj).hasClass('import_visit_dates_drop_zone')) {
                    import_visit_dates_callback(filename, response);
                }
                else if ($(obj).hasClass('import_patients')) {
                    import_patients_callback(filename, response);
                }
                else if ($(obj).hasClass('import_practice_groups')) {
                    import_practice_groups_callback(filename, response);
                }
                else if ($(obj).hasClass('import_doctors')) {
                    import_doctors_callback(filename, response);
                }

            },
            onError: function (e) {
                button.removeAttr('disabled');
                $.unblockUI();
                alert('Upload has failed. Try again, please.');
            }
        });

        if ($(obj).hasClass('import_visit_dates_drop_zone')) {
            ss.disable();
        }

        //*/
    }

    function import_visit_dates_callback(filename, response) {
        var data = {
            "file": response.filename
        }
        $.blockUI();
        $.ajax({
            url: '/admin/parse_file',
            type: 'POST',
            data: data
        }).success(function (response) {
            response = JSON.parse(response);

            var atLeastOneRowImported = false;
            var validation_message = validate_visit_dates_file(response);
            if (validation_message.length > 0) {

                $("<p>" + validation_message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("close");
                    }
                });

                return;
            }

            //*

            var columns_count = 10;

            for (var i = 0; i < response.length; i++) {
                var res = response[i].split(/,(?=(?:[^"]*"[^"]*")*[^"]*$)/g);
                if (res.length == columns_count) {
                    atLeastOneRowImported = true;
                    res[7].replace(".00", "");
                    var row = '<tr>' +
                                '<td>' + res[0].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[1].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[2].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[3].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[4].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[5].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[6].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[7] + '</td>' +
                                '<td>' + res[8].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[9].replace(/\"/g,"") + '</td>' +
                            '</tr>';

                    $('#imported-visit-dates table tbody').append($(row));
                }
            }
            //*/

            if (atLeastOneRowImported) {
                $('#imported-visit-dates').css('display', 'block');
            }

        }).error(function (err) {
        });
    }

    function validate_visit_dates_file(response) {
        var validation_message = '';
        var columns_count = 10;
        var rows_count_to_test = 10;

        //*
        //check the number of columns.
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            //var res = response[i].split(",");
            var res = response[i].replace(/,,/g, ', ,').match(/("[^"]*")|[^,]+/g) || [];

            if (res.length !== columns_count) {
                validation_message += 'The number of columns of the row ' + (i + 1) + ' is ' + res.length + '. the number of columns should be ' + columns_count + '.<br/><br/>';
                break;
            }
        }

        // check date of service format MM/DD/YYYY
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            response[i] = response[i].replace(/,,/g, ',"",');
            var res = response[i].replace(/,,/g, ', ,').match(/("[^"]*")|[^,]+/g) || [];
            if (res.length === columns_count) {
                var dos_split = res[3].split("/");
                if (dos_split.length !== 3 || dos_split[0] > 12 || dos_split[1] > 31 || dos_split[2].length !== 4 || (res[3].indexOf(":") !== -1)) {
                    validation_message += 'The format of date of service column of the row ' + (i + 1) + ' is wrong. the correct format is MM/DD/YYYY.<br/><br/>';
                    break;
                }
            }
        }

        // check incentive date format MM/DD/YYYY
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            response[i] = response[i].replace(/,,/g, ',"",');
            var res = response[i].replace(/,,/g, ', ,').match(/("[^"]*")|[^,]+/g) || [];
            if (res.length === columns_count) {
                var dos_split = res[8].split("/");
                if (dos_split.length !== 3 || dos_split[0] > 12 || dos_split[1] > 31 || dos_split[2].length !== 4 || (res[8].indexOf(":") !== -1)) {
                    validation_message += 'The format of incentive date column of the row ' + (i + 1) + ' is wrong. the correct format is MM/DD/YYYY.<br/><br/>';
                    break;
                }
            }
        }

        // check incentive value format MM/DD/YYYY
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            response[i] = response[i].replace(/,,/g, ',"",');
            var res = response[i].replace(/,,/g, ', ,').match(/("[^"]*")|[^,]+/g) || [];
            if (res.length === columns_count) {
                if (isNaN(res[6])) {
                    validation_message += 'The incentive value of the row ' + (i + 1) + ' is not a number.<br/><br/>';
                    break;
                }
            }
        }

        //*/
        return validation_message;
    }

//post partum import JS starts

     $('.add_imported_visit_dates_post_partum').on('click', function (e) {
        var filename = $('.dropzone').find('input[type=hidden]').val();

        var data = {
            "file": filename,
            "region": $(this).attr('region_id'),
            "program": $(this).attr('program_id'),
            "date_of_service": $(this).parents('.content').find('form #date_of_service').val(),
            "metric": $(this).parents('.content').find('form #metric').val()
        }
       
        //$.blockUI();        
        
        var url = '/admin/programs/store_imported_visit_dates_post_partum';

       
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'html',
            data: data,
             success:function(result){ 
                window.close();            
                $("#pp_import").html(result);
               // window.parent.location.reload();
                //console.log(result);
                //window.location.href = '/admin/post_partum_import/process_imported_dates_post_partum';
            
             } 
        });     
             
        });

       $('.pp_paused_import').on('click', function (e) {
        console.log("clicked");
     var region_id = $(this).attr('region_id');
     console.log("rid ----------"+region_id);
      var url = '/admin/post_partum_import/resume_paused_records/'+region_id;

       
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success:function(result){             
                $("#pp_import").html(result);
               // window.parent.location.reload();
                //console.log(result);
                //window.location.href = '/admin/post_partum_import/process_imported_dates_post_partum';
            
             } 
        }); 
       
     });

//post partum import JS ends

    

    $('.add_imported_visit_dates').on('click', function (e) {
        var filename = $('.dropzone').find('input[type=hidden]').val();

        var data = {
            "file": filename,
            "region": $(this).attr('region_id'),
            "program": $(this).attr('program_id'),
            "date_of_service": $(this).parents('.content').find('form #date_of_service').val(),
            "metric": $(this).parents('.content').find('form #metric').val()
        }
        
       
        $.blockUI();
       
        var url = '/admin/programs/store_imported_visit_dates'
       
        $.ajax({
            url: url,
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            if (response.ok !== true) {
                var message = response.ok;

                $("<p>" + message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        },
                        "Download as CSV": function () {
                            location.href = "/" + response.result_file;
                        }
                    },
                    close: function (event, ui) {
                        window.parent.$('iframe').each(function () {
                            $(this).parents('.modal-content').find('button.close').trigger('click');
                            //window.parent.location.reload();
                        });
                    }
                });

                return false;

            } else {
                alert('Actual visit dates and incentives have been successfully saved.');
            }

            window.parent.$('iframe').each(function () {
                $(this).parents('.modal-content').find('button.close').trigger('click');
                //window.parent.location.reload();
            });

        }).error(function (err) {
            alert('An error has occurred.');
        });

    });

    function import_patients_callback(filename, response) {
        var data = {
            "file": response.filename
        }
        $.blockUI();
        $.ajax({
            url: '/admin/parse_file',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            var atLeastOneRowImported = false;
            for (var i = 0; i < response.length; i++) {
                var res = response[i].split(/,(?=(?:[^"]*"[^"]*")*[^"]*$)/g);
                if (res.length == 16) {
                    atLeastOneRowImported = true;
                    var row = '<tr>' +
                                '<td>' + res[0].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[1].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[2].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[3].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[4].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[5].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[6].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[7].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[8].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[9].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[10].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[11].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[12].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[13].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[14].replace(/\"/g,"") + '</td>' +
                                '<td>' + res[15].replace(/\"/g,"") + '</td>' +
                            '</tr>';

                    $('#imported-patients table tbody').append($(row));
                }
            }

            if (atLeastOneRowImported) {
                $('#imported-patients').css('display', 'block');
                $('.import_cancel_buttons').css('display', 'block');
            }

        }).error(function (err) {
        });
    }

    $('.add_imported_patients').on('click', function (e) {
        var filename = $('.dropzone').find('input[type=hidden]').val();
        var programs = [];

        $('input[name="programs_id[]"]').each(function (i) {
            programs[i] = $(this).val();
        });

        if (programs.length === 0) {
            $("#select_program").dialog({
                modal: true,
                buttons: {
                    "OK": function () {
                        $(this).dialog("close");
                    }
                }
            });

            return;
        }

        var data = {
            "file": filename,
            "region": $(this).attr('region_id'),
            "programs": programs
        }
        $.blockUI();
        $.ajax({
            url: '/admin/regions/store_imported_patients',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            if (response.ok !== true) {
                var message = response.ok;

                $("<p>" + message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        },
                        "Download as CSV": function () {
                            location.href = "/" + response.result_file;
                        }

                    },
                    close: function (event, ui) {
                        window.parent.$('iframe').each(function () {
                            $(this).parents('.modal-content').find('button.close').trigger('click');
                            //window.parent.location.reload();
                        });
                    }
                });

                return false;

            } else {
                alert('Patients have been successfully saved.');
            }

            window.parent.$('iframe').each(function () {
                $(this).parents('.modal-content').find('button.close').trigger('click');
                //window.parent.location.reload();
            });

        }).error(function (err) {
            alert('An error has occurred.');
        });

    });

    function import_practice_groups_callback(filename, response) {
        var data = {
            "file": response.filename
        }
        $.blockUI();
        $.ajax({
            url: '/admin/parse_file',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            var atLeastOneRowImported = false;

            var validation_message = validate_practice_groups_file(response);
            if (validation_message.length > 0) {

                $("<p>" + validation_message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("close");
                    }
                });

                return;
            }

            for (var i = 0; i < response.length; i++) {
                var res = response[i].split(/,(?=(?:[^"]*"[^"]*")*[^"]*$)/g);
                if (res.length == 9) {
                    atLeastOneRowImported = true;
                    var row = '<tr>' +
                            '<td>' + res[0].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[1].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[2].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[3].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[4].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[5].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[6].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[7].replace(/\"/g,"") + '</td>' +
                            '<td>' + res[8].replace(/\"/g,"") + '</td>' +
                            '</tr>';

                    $('#imported-practice_groups table tbody').append($(row));
                }
            }

            if (atLeastOneRowImported) {
                $('#imported-practice_groups').css('display', 'block');
                $('.import_cancel_buttons').css('display', 'block');
            }

        }).error(function (err) {
        });
    }

    function validate_practice_groups_file(response) {
        var validation_message = '';
        var columns_count = 9;
        var rows_count_to_test = 10;

        //check the number of columns.
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            var res = response[i].split(",");
            if (res.length !== columns_count) {
                validation_message += 'The number of columns of the row ' + (i + 1) + ' is ' + res.length + '. the number of columns should be ' + columns_count + '.<br/><br/>';
                break;
            }
        }

        return validation_message;
    }

    $('.add_imported_practice_groups').on('click', function (e) {
        var filename = $('.dropzone').find('input[type=hidden]').val();

        var data = {
            "file": filename,
            "region": $(this).attr('region_id')
        }
        $.blockUI();
        $.ajax({
            url: '/admin/regions/store_imported_practice_groups',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            if (response.ok !== true) {
                var message = response.ok;

                $("<p>" + message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        },
                        "Download as CSV": function () {
                            location.href = "/" + response.result_file;
                        }
                    },
                    close: function (event, ui) {
                        window.parent.$('iframe').each(function () {
                            $(this).parents('.modal-content').find('button.close').trigger('click');
                            //window.parent.location.reload();
                        });
                    }
                });

                return false;

            } else {
                alert('Practice groups have been successfully saved.');
            }

            window.parent.$('iframe').each(function () {
                $(this).parents('.modal-content').find('button.close').trigger('click');
                //window.parent.location.reload();
            });

        }).error(function (err) {
            alert('An error has occurred.');
        });

    });

    function import_doctors_callback(filename, response) {
        var data = {
            "file": response.filename
        }
        $.blockUI();
        $.ajax({
            url: '/admin/parse_file',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            var atLeastOneRowImported = false;

            var validation_message = validate_doctors_file(response);
            if (validation_message.length > 0) {

                $("<p>" + validation_message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        }
                    },
                    close: function (event, ui) {
                        $(this).dialog("close");
                    }
                });

                return;
            }

            for (var i = 0; i < response.length; i++) {
                var res = response[i].split(",");
                if (res.length == 4) {
                    atLeastOneRowImported = true;
                    var row = '<tr>' +
                            '<td>' + res[0] + '</td>' +
                            '<td>' + res[1] + '</td>' +
                            '<td>' + res[2] + '</td>' +
                            '<td>' + res[3] + '</td>' +
                            '</tr>';

                    $('#imported-doctors table tbody').append($(row));
                }
            }

            if (atLeastOneRowImported) {
                $('#imported-doctors').css('display', 'block');
                $('.import_cancel_buttons').css('display', 'block');
            }

        }).error(function (err) {
        });
    }

    function validate_doctors_file(response) {
        var validation_message = '';
        var columns_count = 4;
        var rows_count_to_test = 10;

        //check the number of columns.
        for (var i = 0; i < Math.min(response.length, rows_count_to_test); i++) {
            var res = response[i].split(",");
            if (res.length !== columns_count) {
                validation_message += 'The number of columns of the row ' + (i + 1) + ' is ' + res.length + '. the number of columns should be ' + columns_count + '.<br/><br/>';
                break;
            }
        }

        return validation_message;
    }

   
     

    $('.add_imported_doctors').on('click', function (e) {
        var filename = $('.dropzone').find('input[type=hidden]').val();

        var data = {
            "file": filename,
            "region": $(this).attr('region_id')
        }
        $.blockUI();
        $.ajax({
            url: '/admin/regions/store_imported_doctors',
            type: 'POST',
            dataType: 'json',
            data: data
        }).success(function (response) {
            if (response.ok !== true) {
                var message = response.ok;

                $("<p>" + message + "</p>").dialog({
                    modal: true,
                    buttons: {
                        "OK": function () {
                            $(this).dialog("close");
                        },
                        "Download as CSV": function () {
                            location.href = "/" + response.result_file;
                        }
                    },
                    close: function (event, ui) {
                        window.parent.$('iframe').each(function () {
                            $(this).parents('.modal-content').find('button.close').trigger('click');
                            //window.parent.location.reload();
                        });
                    }
                });

                return false;

            } else {
                alert('Doctors have been successfully saved.');
            }

            window.parent.$('iframe').each(function () {
                $(this).parents('.modal-content').find('button.close').trigger('click');
                //window.parent.location.reload();
            });

        }).error(function (err) {
            alert('An error has occurred.');
        });

    });

  

    $('.cancel_importing').on('click', function (e) {
        window.parent.$('iframe').each(function () {
            $(this).parents('.modal-content').find('button.close').trigger('click');
        });
    });

    /*regions end */

    // first_trimester_report change insurance company => load regions
    $('body').on('change', '.first_trimester_report select[name="insurance_company"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/insurance_company/' + $(this).val() + '/first_trimester_regions',
            method: 'GET',
            dataType: 'html'
        }).done(function (response) {
            response = JSON.parse(response);

            $('.regions_area select[name="region"]').empty();

            if (response.length !== 0) {
                $('.no_regions_found').parent().removeClass('has-error');
                $('.no_regions_found').remove();
            }

            for (var key in response) {
                var _obj = '<option value="' + key + '">' + response[key] + '</option>';
                $('.regions_area select[name="region"]').append($(_obj));
            }

            $('.regions_area').show();
            $('.program_report select[name="region"]').trigger('change');
        });

    });

    $('body').on('change', '.pregnancy_report select[name="insurance_company"], .cribs_for_kids_report  select[name="insurance_company"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/insurance_company/' + $(this).val() + '/pregnancy_regions',
            method: 'GET',
            dataType: 'html'
        }).done(function (response) {
            response = JSON.parse(response);

            $('.regions_area select[name="region"]').empty();

            if (response.length !== 0) {
                $('.no_regions_found').parent().removeClass('has-error');
                $('.no_regions_found').remove();
            }

            for (var key in response) {
                var _obj = '<option value="' + key + '">' + response[key] + '</option>';
                $('.regions_area select[name="region"]').append($(_obj));
            }

            $('.regions_area').show();
            $('.program_report select[name="region"]').trigger('change');
        });

    });


    $('body').on('change', '.wc15_program_report select[name="insurance_company"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/insurance_company/' + $(this).val() + '/wc15_regions',
            method: 'GET',
            dataType: 'html'
        }).done(function (response) {
            response = JSON.parse(response);

            $('.regions_area select[name="region"]').empty();

            if (response.length !== 0) {
                $('.no_regions_found').parent().removeClass('has-error');
                $('.no_regions_found').remove();
            }

            for (var key in response) {
                var _obj = '<option value="' + key + '">' + response[key] + '</option>';
                $('.regions_area select[name="region"]').append($(_obj));
            }

            $('.regions_area').show();
            $('.program_report select[name="region"]').trigger('change');
        });

    });


    /* programs report begin */

    $('body').on('change', '.program_report select[name="insurance_company"], .member_roster_report select[name="insurance_company"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/insurance_company/' + $(this).val() + '/regions',
            method: 'GET',
            dataType: 'html'
        }).done(function (response) {
            response = JSON.parse(response);

            $('.regions_area select[name="region"]').empty();

            if (response.length !== 0) {
                $('.no_regions_found').parent().removeClass('has-error');
                $('.no_regions_found').remove();
            }

            for (var key in response) {
                var _obj = '<option value="' + key + '">' + response[key] + '</option>';
                $('.regions_area select[name="region"]').append($(_obj));
            }

            $('.regions_area').show();
            if ($('.program_report select[name="region"]').length) {
                $('.program_report select[name="region"]').trigger('change');
            } else {
                $('.member_roster_report select[name="region"]').trigger('change');
            }
        });

    });

    $('body').on('change', '.program_report:not(.pregnancy_report) select[name="region"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/regions/' + $(this).val() + '/programs',
            method: 'GET',
            dataType: 'html'
        }).success(function (response) {
            response = JSON.parse(response);

            $('.programs_area select[name="program"]').empty();

            for (var key in response) {
                var _obj = '<option value="' + key + '">' + response[key] + '</option>';
                $('.programs_area select[name="program"]').append($(_obj));
            }

            $('.programs_area').show();
            if ($('.programs_area select[name="program"] option').length > 0) {
                $('.date_range_area').show();
                $('.programs_area select[name="program"]').trigger('change');
            }
        }).error(function (err) {
            $('select#program option').each(function () {
                $(this).remove();
            });
        });
    });
    /* programs report end */

    /* phone input mask */

    $('body').on('keyup', '.phone_mask', function (event) {

        setTimeout(function () {
            var v = mphone($(this).val());

            if (v != $(this).val()) {
                $(this).val(v);
            }
        }.bind(this), 1);

    });

    $('.phone_mask').each(function () {
        var v = mphone($(this).val());

        if (v != $(this).val()) {
            $(this).val(v);
        }
    });

    function mphone(v) {
        var r = v.replace(/\D/g, "");
        r = r.replace(/^0/, "");

        if (r.length > 5) {
            r = r.replace(/^(\d\d\d)(\d{3})(\d{0,4}).*/, "$1-$2-$3");
        }
        else if (r.length > 2) {
            r = r.replace(/^(\d\d\d)(\d{0,3})/, "$1-$2");
        }
        else {
            r = r.replace(/^(\d*)/, "$1");
        }
        return r;
    }

    $('body').on('change', '#group', function (event) {
        if ($(this).val() == 1) {
            $('.insurance_companies_list').hide();
        } else {
            $('.insurance_companies_list').show();
        }
    });

    $('#group').trigger('change');

    $('body').on('click', '#pregnancy_fields .add_new_row', function (event) {
        var clone = $('.scheduled_visit_fields_row:first').clone(true)
                .find("input:text").val("").end()
                .appendTo('.scheduled_visit_fields:last');

        var uniq_id = uniqID.get('new_visit_');
        var first_element_id = clone.find('[name="scheduled_visit_ids[]"]').val();

        clone.find('[name="scheduled_visit_ids[]"]').val(uniq_id);
        clone.find('[for="scheduled_visit_' + first_element_id + '"]').attr('for', 'scheduled_visit_' + uniq_id);
        clone.find('[name="scheduled_visit_' + first_element_id + '"]').attr('name', 'scheduled_visit_' + uniq_id);

        clone.find('[for="actual_visit_' + first_element_id + '"]').attr('for', 'actual_visit_' + uniq_id);
        clone.find('[name="actual_visit_' + first_element_id + '"]').attr('name', 'actual_visit_' + uniq_id);

        clone.find('[for="doctor_id_' + first_element_id + '"]').attr('for', 'doctor_id_' + uniq_id);
        clone.find('[name="doctor_id_' + first_element_id + '"]').attr('name', 'doctor_id_' + uniq_id);

        clone.find('[for="incentive_type_' + first_element_id + '"]').attr('for', 'incentive_type_' + uniq_id);
        clone.find('[name="incentive_type_' + first_element_id + '"]').attr('name', 'incentive_type_' + uniq_id);

        clone.find('[for="incentive_value_' + first_element_id + '"]').attr('for', 'incentive_value_' + uniq_id);
        clone.find('[name="incentive_value_' + first_element_id + '"]').attr('name', 'incentive_value_' + uniq_id);

        clone.find('[for="gift_card_serial_' + first_element_id + '"]').attr('for', 'gift_card_serial_' + uniq_id);
        clone.find('[name="gift_card_serial_' + first_element_id + '"]').attr('name', 'gift_card_serial_' + uniq_id);

        clone.find('[for="gift_card_serial_' + first_element_id + '"]').attr('for', 'gift_card_serial_' + uniq_id);
        clone.find('[name="gift_card_serial_' + first_element_id + '"]').attr('name', 'gift_card_serial_' + uniq_id);

        clone.find('[for="incentive_date_' + first_element_id + '"]').attr('for', 'incentive_date_' + uniq_id);
        clone.find('[name="incentive_date_' + first_element_id + '"]').attr('name', 'incentive_date_' + uniq_id);

        clone.find('[for="visit_notes_' + first_element_id + '"]').attr('for', 'visit_notes_' + uniq_id);
        clone.find('[name="visit_notes_' + first_element_id + '"]').attr('name', 'visit_notes_' + uniq_id);

        clone.find('[for="gift_card_returned_' + first_element_id + '"]').attr('for', 'gift_card_returned_' + uniq_id);
        clone.find('[name="gift_card_returned_' + first_element_id + '"]').attr('name', 'gift_card_returned_' + uniq_id);

        clone.find('[for="gift_card_returned_date_' + first_element_id + '"]').attr('for', 'gift_card_returned_date_' + uniq_id);
        clone.find('[name="gift_card_returned_date_' + first_element_id + '"]').attr('name', 'gift_card_returned_date_' + uniq_id);

        clone.find('[for="gift_card_returned_notes_' + first_element_id + '"]').attr('for', 'gift_card_returned_notes_' + uniq_id);
        clone.find('[name="gift_card_returned_notes_' + first_element_id + '"]').attr('name', 'gift_card_returned_notes_' + uniq_id);

        clone.find('[for="manual_outreach_' + first_element_id + '[]"]').attr('for', 'manual_outreach_' + uniq_id + '[]');
        clone.find('[name="manual_outreach_' + first_element_id + '[]"]').attr('name', 'manual_outreach_' + uniq_id + '[]');

        clone.find('[for="manual_outreach_date_' + first_element_id + '[]"]').attr('for', 'manual_outreach_date_' + uniq_id + '[]');
        clone.find('[name="manual_outreach_date_' + first_element_id + '[]"]').attr('name', 'manual_outreach_date_' + uniq_id + '[]');

        clone.find('[for="manual_outreach_code_' + first_element_id + '[]"]').attr('for', 'manual_outreach_code_' + uniq_id + '[]');
        clone.find('[name="manual_outreach_code_' + first_element_id + '[]"]').attr('name', 'manual_outreach_code_' + uniq_id + '[]');

        clone.find('[for="manual_outreach_notes_' + first_element_id + '[]"]').attr('for', 'manual_outreach_notes_' + uniq_id + '[]');
        clone.find('[name="manual_outreach_notes_' + first_element_id + '[]"]').attr('name', 'manual_outreach_notes_' + uniq_id + '[]');

        clone.find('[name="scheduled_visit_' + uniq_id + '"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });
        clone.find('[name="actual_visit_' + uniq_id + '"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });
        clone.find('[name="incentive_date_' + uniq_id + '"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });

        clone.find('[name="gift_card_returned_date_' + uniq_id + '"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });

        clone.find('[name="manual_outreach_date_' + uniq_id + '[]"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });

    });

    $('body').on('click', 'a.remove_scheduled_row', function (event) {
        var remove_button = $(this);

        $("#dialog-confirm").dialog({
            resizable: false,
            height: 170,
            width: 360,
            modal: true,
            buttons: {
                "Delete": function () {
                    $(remove_button).parents('.scheduled_visit_fields_row').remove();
                    $(this).dialog("close");
                },
                Cancel: function () {
                    $(this).dialog("close");
                }
            }
        });
        return false;

    });

    $('body').on('change', '#date_of_service, #metric', function (event) {
        ss.disable();
        $('.import_visit_dates_drop_zone .btn').text('Proceed');
        $('.import_visit_dates_drop_zone .btn').removeClass('btn-success').addClass('btn-warning');
    });

    $('body').on('change', '#patients_report_year', function (event) {
        location.href = $(this).find("option:selected").attr('href');
    });


    $('body').on('click', '.add_pregnancy_outreach', function (event) {

        var clone = $(this).parents('.row').siblings('.manual_outreach_rows')
                .find('.manual_outreach_row:first').clone(true)
                .find("input:text").val("").end()
                .appendTo($(this).parents('.row').siblings('.manual_outreach_rows:last'));

        clone.find('.datepicker.manual_outreach_related_field').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        //actual_visit_date_field_changed(this);
                    }
                })
                .keyup(function (event) {
                    //actual_visit_date_field_changed(this);
                });
    });

    $('body').on('click', '.add_new_outreach', function (event) {

        var clone = $('.manual_outreach_row:first').clone(true)
                .find("input:text").val("").end()
                .appendTo($(this).parents('div').siblings('.manual_outreach_rows:last'));

        clone.find('[name="manual_outreach_date[]"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    maxDate: new Date(),
                    onSelect: function (dateText) {
                        //actual_visit_date_field_changed(this);
                    }
                })
                .keyup(function (event) {
                    //actual_visit_date_field_changed(this);
                });
    });

    $('body').on('click', '.add_new_scheduled_visit_metric', function (event) {

        var clone = $('.scheduled_visit_row:first').clone(true)
                .find("input:text").val("").end()
                .appendTo('.scheduled_visit_rows:last');

        var uniq_id = uniqID.get('new_visit_');
        var first_element_id = clone.find('[name="scheduled_visit_ids[]"]').val();

        clone.find('[name="scheduled_visit_ids[]"]').val(uniq_id);
        clone.find('[for="metric_' + first_element_id + '"]').attr('for', 'metric_' + uniq_id);
        clone.find('[name="metric_' + first_element_id + '"]').attr('name', 'metric_' + uniq_id);

        clone.find('[for="scheduled_visit_date_' + first_element_id + '"]').attr('for', 'scheduled_visit_date_' + uniq_id);
        clone.find('[name="scheduled_visit_date_' + first_element_id + '"]').attr('name', 'scheduled_visit_date_' + uniq_id);

        clone.find('[for="scheduled_visit_date_notes_' + first_element_id + '"]').attr('for', 'scheduled_visit_date_notes_' + uniq_id);
        clone.find('[name="scheduled_visit_date_notes_' + first_element_id + '"]').attr('name', 'scheduled_visit_date_notes_' + uniq_id);


        clone.find('[name="scheduled_visit_date_' + uniq_id + '"]').datepicker('destroy').removeClass('hasDatepicker').removeAttr('id').datepicker({
                    onSelect: function (dateText) {
                        actual_visit_date_field_changed(this);
                        $(this).trigger('blur');
                    }
                })
                .keyup(function (event) {
                    actual_visit_date_field_changed(this);
                });
    });


    $('.edit_patient_info').on('click', function (e) {
        $.blockUI();
        $.ajax({
            url: '/admin/patients/' + $(this).attr('patient_id') + '/edit_patient_record',
            method: 'GET',
            //data: data_preview,
            dataType: 'html'
        }).done(function (response) {

            var modalContent = '<div class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"> \
                <div class="modal-dialog modal-lg" style="width: 80%;"> \
                <div class="modal-content"> \
                <div class="modal-header"> \
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">x</span><span class="sr-only">Close</span></button> \
                <h4 class="modal-title">Edit Patient Info</h4> \
                </div> \
                <div class="modal-body" style="height: 800px;"> \
                <iframe style="width: 100%; height: 100%; border: none;" \
                id="import-patients-iframe" name="import-patients-iframe"></iframe> \
                </div> \
                </div> \
                </div> \
                </div>';
            var iframe = $(modalContent)
                    .appendTo(document.body)
                    .modal()
                    .find('iframe')[0];

            iframe.contentWindow.contents = response;
            iframe.src = 'javascript:window["contents"]';

        });
    });

    $('.save_patient_info').on('click', function (e) {
        var self = $(this);
        $(this).parents('form').submit(function () {
            $.post($(this).attr('action'), $(this).serialize(), function (json) {
            }, 'json');

            window.parent.$('iframe').each(function () {

                setTimeout(function () {
                    $(this).parents('.modal-content').find('button.close').trigger('click');
                    window.parent.location.reload();
                }.bind(this), 500);

            });
        });
    });


    $("form").submit(function (event) {

        if ($(this).find('.regions_area').length > 0 && $('.regions_area select option').length == 0) {

            $('select[name="region"]').parent().addClass("has-error");

            if ($('select[name="region"]').parent().find('.help-block').length < 1) {
                $('select[name="region"]').parent().append('<span class="help-block no_regions_found">No regions found.</span>');
            }

            return false;

        }

        if ($('select[multiple="multiple"]').length > 0 && $('select[multiple="multiple"]').val() === null) {

            $('select[multiple="multiple"]').parent().addClass("has-error")
            $('select[multiple="multiple"]').parent().prepend('<span class="help-block">Please select at least one option.</span><br/>');

            return false;
        }

        if ($(".future-date").length > 0) {

            $("html, body").animate({
                scrollTop: $(".has-error").offset().top - 300
            }, 500);

            return false;
        }

        $('.has-error').each(function () {
            $(this).removeClass('has-error');
            $(this).find('.help-block').remove();
        });

        if ($('#pregnancy_scheduled_visits input[name="date_added"]').length > 0 && $('#pregnancy_scheduled_visits input[name="date_added"]').val().length < 1) {
            $('#pregnancy_scheduled_visits input[name="date_added"]').parent().addClass("has-error");

            if ($('#pregnancy_scheduled_visits input[name="date_added"]').parent().find('.help-block').length < 1) {
                $('#pregnancy_scheduled_visits input[name="date_added"]').parent().append('<span class="help-block">This field is required.</span>');
            }

            $("html, body").animate({
                scrollTop: $('#pregnancy_scheduled_visits input[name="date_added"]').offset().top - 300
            }, 500);

            return false;
        }

        if ($('#pregnancy_scheduled_visits input[name="due_date"]').length > 0 && $('#pregnancy_scheduled_visits input[name="due_date"]').val().length < 1) {
            $('#pregnancy_scheduled_visits input[name="due_date"]').parent().addClass("has-error");

            if ($('#pregnancy_scheduled_visits input[name="due_date"]').parent().find('.help-block').length < 1) {
                $('#pregnancy_scheduled_visits input[name="due_date"]').parent().append('<span class="help-block">This field is required.</span>');
            }


            $("html, body").animate({
                scrollTop: $('#pregnancy_scheduled_visits input[name="due_date"]').offset().top - 300
            }, 500);

            return false;
        }

        if ($('#pregnancy_scheduled_visits select[name="enrolled_by"]').length > 0 && $('#pregnancy_scheduled_visits select[name="enrolled_by"]').val() == 0) {
            $('#pregnancy_scheduled_visits select[name="enrolled_by"]').parent().addClass("has-error");

            if ($('#pregnancy_scheduled_visits select[name="enrolled_by"]').parent().find('.help-block').length < 1) {
                $('#pregnancy_scheduled_visits select[name="enrolled_by"]').parent().append('<span class="help-block">This field is required.</span>');
            }


            $("html, body").animate({
                scrollTop: $('#pregnancy_scheduled_visits select[name="enrolled_by"]').offset().top - 300
            }, 500);

            return false;
        }

        $(this).find('button[type="submit"]:not(.do_not_disable_on_submit)').attr("disabled", true);

    });

    var uniqID = {
        counter: 1,
        get: function (prefix) {
            if (!prefix) {
                prefix = "uniqid";
            }
            var id = prefix + "" + uniqID.counter++;
            if (jQuery("#" + id).length == 0)
                return id;
            else
                return uniqID.get()

        }
    }

    Date.prototype.getJulian = function () {
        var number = Math.floor((this / 86400000) - (this.getTimezoneOffset() / 1440) + 2440587.5);
        return isNaN(number) ? 0 : number;
    }

    $('body').on('click', '#select_all', function (event) {
        if ($(this).html() == "Select All") {
            $(this).siblings('select').find('option').prop('selected', true);
            $(this).html('Deselect All');
        } else {
            $(this).siblings('select').find('option').removeAttr("selected");
            $(this).html('Select All');
        }
    });

    if ($('input[name="eligible_for_gift_incentive"]').is(":checked")) {
        $('#eligibility_fields').show();
    } else {
        $('#eligibility_fields').hide();
    }

    $('body').on('change', 'input[name="eligible_for_gift_incentive"]', function (event) {
        if (this.checked) {
            $('#eligibility_fields').show();
        } else {
            $('#eligibility_fields').hide();
        }
    });


    $('body').on('change', '.member_roster_report select[name="region"]', function (event) {
        $.blockUI();
        $.ajax({
            url: '/admin/regions/' + $(this).val() + '/programs_with_types',
            method: 'GET',
            dataType: 'html'
        }).success(function (response) {
            response = JSON.parse(response);

            $('.programs_area select[name="program"]').empty();


            for (var i = 0; i < response.length; i++) {
                var _obj = '<option value="' + response[i].id + '" data-type="' + response[i].type + '">' + response[i].name + '</option>';
                $('.programs_area select[name="program"]').append($(_obj));
            }

            $('.member_roster_report select[name="program"]').trigger('change');

        }).error(function (err) {
            $('select#program option').each(function () {
                $(this).remove();
            });
        });
    });

    $('body').on('change', '.member_roster_report select[name="program"]', function (event) {
        if ([TYPE_PREGNANCY, TYPE_WC15_AHC, TYPE_WC15_KF].indexOf(+$(this).find('option:selected').attr('data-type')) > -1) {
            $('.date_range_area').show();
            $('.all_dates_area').show();
        } else {
            $('.date_range_area').hide();
            $('.all_dates_area').hide();
        }
    });

    $('body').on('click', '#add_new_pregnancy', function (event) {
        $('#pregnancy_instance').show();
        $("html, body").animate({
            scrollTop: $("#pregnancy_instance").offset().top - 300
        }, 500);
    });

    $('body').on('click', '.cancel_add_new_pregnancy', function (event) {
        $('#pregnancy_instance').hide();
        $("html, body").animate({
            scrollTop: $("#add_new_pregnancy").offset().top - 300
        }, 500);
    });

})();
