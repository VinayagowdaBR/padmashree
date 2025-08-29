"use strict";
var siteUrl = $('input[name="siteUrl"]').val();
var time_format = $('input[name="time_format"]').val();
var time_zone = $('input[name="time_zone"]').val();
var timeFormat = "H:i";
if (time_format == 24) {
    timeFormat = "H:i";
} else {
    timeFormat = "g:i A";
}
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(siteUrl + 'modules/appointment_manager/service-worker.js')
        .then(registration => {
            console.log('Service Worker registered with scope:', registration.scope);
        })
        .catch(error => {
            console.log('Service Worker registration failed:', error);
        });
}


$(document).ready(function() {
    $("form#appmgr_appointment_form_public").appFormValidator({
        rules: {
            location: "required",
            appointee: "required",
            appointment_date: "required",
            company: "required",
            email: "required",
            phonenumber: "required",
            appointment_start_time: "required"
        }
    });
    $("#appointment_start_time,#appointment_end_time").datetimepicker({ datepicker: false });
    if (window.self !== window.top) {
        $('input[name="iframe"]').val(1);
    }
    $('#location').change(function() {
        $("#appointment_start_time, #appointment_end_time").datetimepicker('destroy');
        var locationId = $(this).val();
        var minT = $('option:selected', this).data("tfrom");
        var maxT = $('option:selected', this).data("tto");
        var csrf_token = $('input[name="csrf_token_name"]').val();
        appointment_start_time(minT, maxT);
        if (locationId) {
            $.ajax({
                url: siteUrl + "appointment_manager/appointment_manager_client/ajax_search_practitioner",
                type: "POST",
                data: {
                    csrf_token_name: csrf_token,
                    location_id: locationId
                },
                dataType: "json",
                success: function(data) {
                    $('#appointee').empty().append('<option value=""></option>');
                    $.each(data, function(key, value) {
                        $('#appointee').append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $('#appointee').selectpicker('refresh');
                }
            });
        } else {
            $('#appointee').empty().selectpicker('refresh');
        }
        $('#appointee').change(function() {
            var locationId = $('#location').val();
            var appointment_date = $('#appointment_date').val();
            var appointeeId = $(this).val();
            if (locationId && appointment_date && appointeeId) {
                roomsRendering(locationId, appointeeId);
            } else {
                $('.rooms_appointment_form').html('');
            }
            $.post(siteUrl + 'appointment_manager/appointment_manager_client/practionars_availibility', { appointee: $(this).val() }, function(response) {
                response = JSON.parse(response);
                var unavailibilityDates = [];
                if (response.unavailibility != 'undefined') {
                    $.each(response.unavailibility, function(index, item) {
                        if (isValidDate(item['unavailable_date'])) {
                            unavailibilityDates.push(item['unavailable_date']);
                        }
                    })
                }
                appointmentDateReRenderForAvailibility(response.availibility, unavailibilityDates, $("#appmgr_appointment_form_public"));
            });
        });
        $('#appointment_date').change(function() {
            $('#appointee').trigger('change');
        });
    });
    $('#appmgr_appointment_form_public select[name="treatment"]').on('change', function() {
        init_service_categories_select($(this).val(), $('#appmgr_appointment_form_public select[name="service_cat[]"]'));
    })
});

function roomsRendering(locid, appid) {
    var params = {};
    params.check = false;
    params.csrf_token_name = $('input[name="csrf_token_name"]').val();
    params.app_date = $('input[name="appointment_date"]').val();
    $.ajax({
        url: siteUrl + 'appointment_manager/appointment_manager_client/getrooms/' + locid + '/' + appid,
        type: "GET",
        data: params,
        dataType: "json",
        success: function(response) {
            if (response.success === true || response.success == "true") {
                $('.rooms_appointment_form').html(response.html);
            } else {
                $('.rooms_appointment_form').html('Not Available');
            };
        }
    });
}

function appointment_start_time(minT, maxT) {
    var appointmentDateTimePickerOptions = {};
    appointmentDateTimePickerOptions.formatTime = timeFormat;
    appointmentDateTimePickerOptions.timezone = time_zone;
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
            data.location = $("#location").val();
            data.appointee = $("#appointee").val();
            var selectedDate = formatDate($("input[name='appointment_date']").val());
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
            $.get(siteUrl + "appointment_manager/appointment_manager_client/get_practitioner_busy_times", data).done(
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
                            //if (el.appointment_time) {
                            //     var currentTime = $("body").find(
                            //         '.xdsoft_time:contains("' + formatTime(el.appointment_time) + '")'
                            //     );
                            //     currentTime.addClass("appmgr_busy_time");
                            //     currentTime.on('click', function(event) {
                            //         event.preventDefault();
                            //         event.stopImmediatePropagation();
                            //         return false; // Prevents default action
                            //     });
                            // }
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
            var startTime24 = convertTo24Hour($("#appointment_start_time").val());
            this.setOptions({
                minTime: startTime24,
                maxTime: maxT,
            });
        },
    });
    $("#appointment_end_time").datetimepicker(appointmentDateTimePickerOptions);
}

function formatTime(timeString) {
    const [time, period] = timeString.split(' ');
    const [hours, minutes] = time.split(':');
    const formattedHours = parseInt(hours, 10);
    return `${formattedHours}:${minutes} ${period}`;
}

function appointmentDateReRenderForAvailibility(availability = null, unavailibilityDates, form) {
    if (form.find('#appointment_date').data('DateTimePicker') === undefined) {
        console.log('DateTimePicker is destroyed in availibility');
    } else {
        form.find('#appointment_date').datetimepicker('destroy');
    }
    var params = {};
    if (availability && availability.repetition == 'monthly' && isValidDate(availability.available_date_from) && isValidDate(availability.available_date_to)) {
        params.minDate = availability.available_date_from;
        params.maxDate = availability.available_date_to;
    }
    params.timepicker = false;
    params.format = app.options.date_format;
    params.onGenerate = function(ct) {
        var disabledDates = unavailibilityDates;
        $('.xdsoft_date').each(function() {
            var date = $(this).data('date');
            var month = $(this).data('month') + 1; // months are 0 indexed
            var year = $(this).data('year');
            var fullDate = year + '-' + (month < 10 ? '0' : '') + month + '-' + (date < 10 ? '0' : '') + date;
            if (disabledDates.indexOf(fullDate) !== -1) {
                $(this).addClass('xdsoft_disabled');
            }
        });
    }
    form.find($('#appointment_date')).datetimepicker(params);
}

function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
}

function convertTo24Hour(time12) {
    const [time, modifier] = time12.split(' ');
    let [hours, minutes] = time.split(':');

    if (hours === '12') {
        hours = '00';
    }

    if (modifier === 'PM') {
        hours = parseInt(hours, 10) + 12;
    }

    return `${hours}:${minutes}`;
}

function formatDate(input) {
    if (input) {
        input = input.trim();
        const regexDDMMYYYY = /^(\d{2})[-\/.](\d{2})[-\/.](\d{4})$/; // d-m-Y, d/m/Y, d.m.Y
        const regexMMDDYYYY = /^(\d{2})[-\/.](\d{2})[-\/.](\d{4})$/; // m-d-Y, m/d/Y, m.d.Y
        const regexYYYYMMDD = /^(\d{4})[-\/.](\d{2})[-\/.](\d{2})$/; // Y-m-d
        let match = input.match(regexDDMMYYYY); // For d-m-Y, d/m/Y, d.m.Y
        if (match) {
            const [_, day, month, year] = match;
            return formatToISO(new Date(`${year}-${month}-${day}`)); // Convert to YYYY-MM-DD
        }
        match = input.match(regexMMDDYYYY); // For m-d-Y, m/d/Y, m.d.Y
        if (match) {
            const [_, month, day, year] = match;
            return formatToISO(new Date(`${year}-${month}-${day}`)); // Convert to YYYY-MM-DD
        }
        match = input.match(regexYYYYMMDD); // For Y-m-d
        if (match) {
            const [_, year, month, day] = match;
            return formatToISO(new Date(`${year}-${month}-${day}`)); // Already in YYYY-MM-DD
        }
        throw new Error("Invalid date format");
    }
}

function formatToISO(date) {
    if (isNaN(date.getTime())) {
        throw new Error("Invalid date");
    }
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function init_service_categories_select(serviceId, elem, selectedValues = []) {
    $.ajax({
        url: siteUrl + "appointment_manager/appointment_manager_client/get_service_categories_ajax/" + serviceId,
        type: "GET",
        data: {},
        dataType: "json",
        success: function(response) {
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
                    if (!Array.isArray(selectedValues) || selectedValues.length === 0) {
                        if (selectedValues.includes(category.id.toString())) {
                            option.selected = true;
                        }
                    }
                });

                console.log("Options added:", response); // Debug log
            } else {
                console.error("Response is not an array:", response);
            }

            // Trigger change to ensure UI updates (for selectpicker or other libraries)
            selector.selectpicker('refresh');
        }
    });
}