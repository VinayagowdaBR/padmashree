"view strict";
$(function() {
    var appSummServerParams = {};
    if ($.fn.DataTable.isDataTable('.table-appmgr_bookings')) {
        $('.table-appmgr_bookings').DataTable().destroy();
    }
    initDataTable('.table-appmgr_bookings', window.location.href, [undefined], [undefined], appSummServerParams, [4, 'desc']);
    $("body").on("click", ".close-modal-opportunity", function() {
        $(".modal-opportunity").modal("hide");
    });
    $('#appointmentNewModal').on('hide.bs.modal', function() {
        $('#appointmentNewModal #appointment_start_time').datetimepicker('destroy');
        $('#appointmentNewModal #appointment_end_time').datetimepicker('destroy');
    });
});

function view_appointment(invoker, id) {
    var practitioner = $(invoker).closest('tr').find('td').eq(1).find('a:first').text();
    var client = $(invoker).closest('tr').find('td').eq(2).text();
    var appointmentDate = $(invoker).closest('tr').find('td').eq(3).text();
    var time = $(invoker).closest('tr').find('td').eq(4).text();
    var duration = $(invoker).closest('tr').find('td').eq(5).text();
    var location = $(invoker).closest('tr').find('td').eq(6).text();
    var service = $(invoker).closest('tr').find('td').eq(7).text();
    var cats = $(invoker).closest('tr').find('td').eq(8).text();
    var status = $(invoker).closest('tr').find('td').eq(9).find('span').attr('title');
    var addedBy = $(invoker).closest('tr').find('td').eq(10).text();

    $('#modal-practitioner').text(practitioner);
    $('#modal-client').text(client);
    $('#modal-appointment-date').text(appointmentDate);
    $('#modal-time').text(time);
    $('#modal-duration').text(duration);
    $('#modal-location').text(location);
    $('#modal-status').text(status);
    $('#modal-service').text(service);
    $('#modal-cats').text(cats);
    $('#modal-added-by').text(addedBy);
    $('#viewappointment').modal('show');
}

function edit_appointment(invoker, id) {
    var data = {};
    var appointmentDateTimePickerOptions = {};
    appointmentDateTimePickerOptions.formatTime = timeFormat;
    appointmentDateTimePickerOptions.datepicker = false;
    appointmentDateTimePickerOptions.format = timeFormat;
    appointmentDateTimePickerOptions.step = 30;
    appointmentDateTimePickerOptions.validateOnBlur = false;

    $("#edit-form-appointment").find('#appointment_date').datetimepicker('destroy');
    $("#edit-form-appointment").find('#appointment_date').datetimepicker({
        format: app.options.date_format,
        timepicker: false
    });
    $.post($(invoker).data('href'), data, function(response) {
        try {
            response = JSON.parse(response);
            if (response && response.appointment) {
                $("#edit-form-appointment").find("#cust_fields_area").html(response.c_fileds);
                if (response.appointment.appointee) {
                    requestGetJSON(
                        admin_url + "appointment_manager/get_appointies_json/" + response.appointment.appointee
                    ).done(function(respons) {
                        var appointeeSelect = $("form#edit-form-appointment").find('select[name=appointee]');
                        appointeeSelect.append(new Option(respons.name, respons.appointeeid));
                        appointeeSelect.val(response.appointment.appointee).trigger("change");
                        $('#edit-form-appointment #apt_location').val(response.appointment.location).selectpicker("refresh");
                        init_selectpicker();
                        appointment_start_end_time_for_summ(
                            $("option:selected", $("#apt_location")).data("tfrom"),
                            $("option:selected", $("#apt_location")).data("tto"),
                            $("#edit-form-appointment")
                        );

                    }).fail(function() {
                        alert('Failed to load appointee data.');
                    });
                }
                $('#appointment_date').val(response.appointment.appointment_date);
                $('#appointment_start_time').val(response.appointment.startTime).datetimepicker(appointmentDateTimePickerOptions);
                $('#appointment_end_time').val(response.appointment.endTime).datetimepicker(appointmentDateTimePickerOptions);
                $('#client').val(response.appointment.client).trigger('change');
                $('#treatment').val(response.appointment.treatment).trigger('change');
                $('#description').val(response.appointment.aptdesc);
                $('#reminder_before').val(response.appointment.reminder_before);
                $('#reminder_before_type').val(response.appointment.reminder_before_type).trigger('change');
                $('input[name=isEdit]').val(id);
                var locationId = response.appointment.location;
                if (locationId) {
                    loadRooms(locationId, id);
                }
                if (response.appointment.opted_rooms) {
                    $('input[name="rooms[]"]').prop('checked', false);
                    $.each(response.rooms, function(index, room) {
                        $('input[name="rooms[]"][value="' + room + '"]').prop('checked', true);
                    });
                }
                if (response.statuses) {
                    $('#status').val(response.appointment.status).trigger('change');
                }
                init_service_categories_select(response.appointment.treatment, $('#edit-form-appointment select[name="service_cat[]"]'), response.appointment.service_cat);
                $('#appointmentNewModal').modal('show');
            } else {
                alert('Failed to load appointment data.');
            }
        } catch (e) {
            console.error("Error parsing response: ", e);
            alert('Error processing the request.');
        }
    }).fail(function() {
        alert('Failed to fetch appointment data.');
    });
    $("#edit-form-appointment").find("#apt_location").change(function() {
        var locationId = $(this).val();
        appointment_start_end_time_for_summ(
            $("option:selected", this).data("tfrom"),
            $("option:selected", this).data("tto"),
            $("#edit-form-appointment")
        );
        var locationId = $(this).val();
        $.ajax({
            url: admin_url + "appointment_manager/ajax_search_practitioner",
            type: "POST",
            data: {
                location: locationId
            },
            dataType: "json",
            success: function(data) {
                $("#edit-form-appointment").find("#appointee").empty().append('<option value=""></option>');
                $.each(data, function(key, value) {
                    $("#edit-form-appointment").find("#appointee").append(
                        '<option value="' + value.id + '">' + value.name + "</option>"
                    );
                });
                $("#edit-form-appointment").find("#appointee").selectpicker("refresh");
            },
        });
    });
}

function loadRooms(locationId, id) {
    var params = {};
    params.check = false;
    requestGetJSON(
        admin_url + "appointment_manager/getrooms/" + locationId + "/" + id,
        params
    ).done(function(response) {
        if (response.success === true || response.success == "true") {
            $(".rooms_appointment_form").html(response.html);
        } else {
            $(".rooms_appointment_form").html("Not Available");
        }
    });
}

function createTaProject(e) {
    var url = $(e).data('href');
    $.post(url, {}, function(response) {
        $("#modal-wrapper-ta").html(response);
        if ($(".modal-opportunity").is(":hidden")) {
            $(".modal-opportunity").modal({
                backdrop: "static",
                show: true,
            });
            init_selectpicker();
            init_tags_inputs();
            init_ajax_search("customer", "#clientid.ajax-search");
            init_progress_bars();
            init_editor();
            validate_project_ta();
        }
    });
}

function validate_project_ta() {
    var validationObject = {
        name: "required",
        clientid: "required",
        status: "required",
        start_date: "required",
        'project_members[]': "required",
    };
    appValidateForm(
        $("#form-opportunity"),
        validationObject
    );
}

function appointment_start_end_time_for_summ(minT, maxT, form) {
    var appointmentDateTimePickerOptions = {};
    appointmentDateTimePickerOptions.formatTime = timeFormat;
    appointmentDateTimePickerOptions.timezone = app.options.timezone;

    $("#appointment_start_time").datetimepicker({
        datepicker: false,
        format: timeFormat,
        step: 30,
        validateOnBlur: false,
        onShow: function() {
            this.setOptions({
                minTime: minT,
                maxTime: maxT,
            });
        },
        onGenerate: function(ct) {
            var data = {};
            data.location = $("#apt_location").val();
            data.appointee = $("#appointee").val();
            var selectedDate = $("input[name='appointment_date']").val();
            var todayDate =
                ct.getFullYear() +
                "-" +
                (ct.getMonth() + 1 < 10 ? "0" : "") +
                (ct.getMonth() +
                    1 +
                    "-" +
                    (ct.getDate() < 10 ? "0" : "") +
                    ct.getDate());
            data.appointment_date = selectedDate;
            $.post(admin_url + "appointment_manager/get_practitioner_busy_times", data).done(
                function(r) {
                    r = JSON.parse(r);
                    $(r).each(function(i, el) {
                        if (el.appointment_date == selectedDate) {
                            if (el.appointment_time) {
                                var currentTime = $("body").find(
                                    '.xdsoft_time:contains("' + formatTime(el.appointment_time) + '")'
                                );
                                currentTime.addClass("appmgr_busy_time");
                                currentTime.on('click', function(event) {
                                    event.preventDefault();
                                    event.stopImmediatePropagation();
                                    return false; // Prevents default action
                                });
                            }
                        }
                    });
                }
            );
        },
        onChangeDateTime: function() {
            console.log("time changed");
        },
    });
    $("#appointment_start_time").datetimepicker(appointmentDateTimePickerOptions);
    $("#appointment_end_time").datetimepicker({
        datepicker: false,
        format: timeFormat,
        step: 30,
        validateOnBlur: false,
        onShow: function() {
            this.setOptions({
                minTime: convertTo24Hour($("#appointment_start_time").val()),
                maxTime: maxT,
            });
        },
    });
    $("#appointment_end_time").datetimepicker(appointmentDateTimePickerOptions);
}

function init_service_categories_select_summery(serviceId, elem, selectedValues) {
    requestGetJSON(
            admin_url +
            "appointment_manager/get_service_categories_ajax/" +
            serviceId
        )
        .done(function(response) {
            var selector = $(elem); // Ensure this is a valid jQuery object
            console.log(selector);

            // Clear existing options
            selector.empty();

            // Add a placeholder/default option
            // selector.append(new Option("Select a category", "", true, true)); // Placeholder (unselectable)

            // Check if response is an array
            if (Array.isArray(response)) {
                response.forEach(function(category) {
                    // Append each category as an option
                    var option = new Option(category.name, category.id);
                    selector.append(option);

                    // If the category.id is in the selectedValues array, mark it as selected
                    if (selectedValues.includes(category.id.toString())) {
                        option.selected = true;
                    }
                });

                console.log("Options added:", response); // Debug log
            } else {
                console.error("Response is not an array:", response);
            }

            // Trigger change to ensure UI updates (for selectpicker or other libraries)
            selector.selectpicker('refresh');
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch service categories:", textStatus, errorThrown);
        });
}