"use strict";
var timeFormat = "H:i";
if (app.options.time_format == 24) {
    timeFormat = "H:i";
} else {
    timeFormat = "g:i A";
}
var capability = {};
var taCalendar_selector = $("#appointment_manager");
var disabledDates = "";
requestGetJSON(
    admin_url +
    "appointment_manager/get_holidays"
).done(function(respons) {
    disabledDates = respons;
});
if (disabledDates !== "") {
    var parsedDates = JSON.parse(disabledDates);
    var formattedDates = parsedDates.map((date) => ({
        start: date.start,
        end: date.end,
    }));

    disabledDates = formattedDates;
}

var taCalendar = "";

$(function() {
    getTaCapabilty();
    $("#appoint_div").draggable();
    if (taCalendar_selector.length > 0) {
        var taCalendar_settings = {
            customButtons: {
                gCalSyncBtn: {
                    text: 'Google Calendar Sync', // Text for the custom button
                    click: function() {
                        window.location.href = admin_url + 'appointment_manager/googleSync';
                    },
                }
            },

            locale: app.locale,

            headerToolbar: {
                left: "prev,next today",

                center: "title",

                right: "gCalSyncBtn,dayGridMonth,timeGridWeek,timeGridDay",
            },

            editable: true,

            dayMaxEventRows: parseInt(app.options.calendar_events_limit) + 1,

            views: {
                day: {
                    dayMaxEventRows: false,
                },
            },

            direction: isRTL == "true" ? "rtl" : "ltr",

            eventStartEditable: false,

            firstDay: parseInt(app.options.calendar_first_day),

            initialView: "timeGridDay",

            timeZone: app.options.timezone,

            loading: function(isLoading, view) {
                !isLoading
                    ?
                    $(".dt-loader").addClass("hide") :
                    $(".dt-loader").removeClass("hide");
            },
            slotMinTime: "00:00:00",
            slotMaxTime: "24:00:00",
            eventSources: [
                function(info, successCallback, failureCallback) {
                    console.log('main-cal');
                    var params = {};
                    params["practitioner"] = $("#staff option:selected").val();
                    if ($("#location option:selected").val()) {
                        params["location"] = $("#location option:selected").val();
                    }
                    if (!jQuery.isEmptyObject(params)) {
                        params["calendar_filters"] = true;
                    }

                    return $.getJSON(
                        admin_url + "appointment_manager/appointments_calendar",
                        $.extend({}, params, {
                            start: info.startStr,
                            end: info.endStr,
                        })
                    ).then(function(data) {
                        successCallback(
                            data.map(function(e) {
                                return $.extend({}, e, {
                                    title: "", // Clear the title
                                    start: e.start || e.date,
                                    extendedProps: {
                                        client: e.client,
                                        appointee: e.practitioner,
                                        location: e.location,
                                        treatment: e.treatment,
                                        rooms: e.rooms,
                                        time: e.time,
                                        duration: e.duration,
                                    },
                                });
                            })
                        );
                    });
                },
            ],

            eventContent: function(arg) {
                var customHtml = `

               <div class="fc-event-appointment">

                   <div><i class="fa-solid fa-user-doctor"></i>: <span>${
                     arg.event.extendedProps.appointee || "No Staff"
                   }</span></div>
                   <div><i class="fa-solid fa-location-dot"></i>: <span>${
                     arg.event.extendedProps.location || "No location"
                   }</span></div>
                   <div><i class="fa-solid fa-user"></i>: <span>${
                     arg.event.extendedProps.client || "No client"
                   }</span></div>
                   <div><i class="fa-solid fa-suitcase-medical"></i>: <span>${
                     arg.event.extendedProps.treatment || "No service"
                   }</span></div>
                   <div><i class="fa-solid fa-house-chimney-medical"></i>: <span>${
                     arg.event.extendedProps.cats || "No Categories"
                   }</span></div>
                   <div><i class="fa-regular fa-clock"></i>: <span>${
                     arg.event.extendedProps.time || "No Time"
                   }</span></div>
               </div>
           `;

                return {
                    html: customHtml,
                };
            },
            eventDidMount: function(data) {
                var $el = $(data.el);
                $el.attr({
                    "data-toggle": "popover",
                    "data-trigger": "manual",
                    "data-placement": "top",
                    "data-html": "true",
                    title: `
            <div style="display:flex;justify-content:space-between;">
                <span>${app.lang.appmgr_apt_dtails_heading}</span>
            </div>
        `,
                    "data-content": `
            <div><strong><i class="fa-solid fa-user-doctor"></i> ${app.lang.appmgr_appointee_label}:</strong> <span>${
              data.event.extendedProps.appointee || "No staff"
            }</span></div>
            <div><strong><i class="fa-solid fa-location-dot"></i> ${app.lang.appmgr_location}:</strong> <span>${
              data.event.extendedProps.location || "No location"
            }</span></div>
            <div><strong><i class="fa-solid fa-user"></i> ${app.lang.appmgr_client_label}:</strong> <span>${
              data.event.extendedProps.client || "No client"
            }</span></div>
            <div><strong><i class="fa-solid fa-suitcase-medical"></i> ${app.lang.appmgr_treatment_heading}:</strong> <span>${
              data.event.extendedProps.treatment || "No service"
            }</span></div>
            <div><strong><i class="fa-solid fa-file-lines"></i> ${app.lang.appmgr_appointm_description}:</strong> <span>${
              data.event.extendedProps.description || "No description"
            }</span></div>
            <div><strong><i class="fa-solid fa-house-chimney-medical"></i> ${app.lang.appmgr_ser_cats}:</strong> <span>${
              data.event.extendedProps.cats || "No Categories"
            }</span></div>
            <div><strong><i class="fa-regular fa-clock"></i> ${app.lang.appmgr_appointment_time}:</strong> <span>${
              data.event.extendedProps.time || "No Time"
            }</span></div>
            <div><strong><i class="fa-regular fa-clock"></i> ${app.lang.appmgr_appointment_duration}:</strong> <span>${
              data.event.extendedProps.duration || "No Duration"
            }</span></div>
        `,
                });
                if (!data.event.extendedProps.url) {
                    $el.on("click", function() {
                        view_appointment(data.event.extendedProps.eventid);
                    });
                }
                try {
                    $el.popover({
                        container: "body",
                        trigger: "manual",
                        html: true,
                    });
                } catch (error) {
                    console.error("Popover initialization error:", error);
                }

                function updatePopoverPlacement() {
                    try {
                        var scrollTop = $(".fc-scroller").scrollTop();
                        var scrollHeight = $(".fc-scroller").prop("scrollHeight");
                        var clientHeight = $(".fc-scroller").prop("clientHeight");
                        var scrollPercent =
                            (scrollTop / (scrollHeight - clientHeight)) * 100;

                        var placement = scrollPercent >= 20 ? "bottom" : "top";

                        $el.each(function() {
                            var $popoverEl = $(this);
                            var popover = $popoverEl.data("bs.popover"); // For Bootstrap 5
                            if (popover) {
                                popover.config.placement = placement;
                                $popoverEl.attr("data-placement", placement);
                                $popoverEl.popover("update");
                            }
                        });
                    } catch (error) {
                        //console.error("Error updating popover placement:", error);
                    }
                }

                // Add scroll event listener to update popover placement
                $(".fc-scroller").on("scroll", function() {
                    updatePopoverPlacement();
                });

                // Ensure popover remains visible during scrolling
                $el.on("show.bs.popover", function() {
                    updatePopoverPlacement();
                });

                $el.on("mouseenter", function() {
                    $el.popover("show");
                    $(".popover").on("mouseleave", function() {
                        $el.popover("hide");
                    });
                });
                $el.on("mouseleave", function() {
                    $el.popover("hide");
                });
                $(".close-btn-popover").click(function() {
                    $el.popover("hide");
                });
                setTimeout(function() {
                    updatePopoverPlacement();
                }, 0);
            },
            dateClick: function(info) {
                if ($("#location").val() == "") {
                    alert_float("danger", "Please select location first!");
                    return false;
                }
                if (!capability.create) {
                    return false;
                }
                var clickedDate = info.dateStr.split("T")[0];
                if (disabledDates) {
                    var isDisabled = disabledDates.some(function(disabledDate) {
                        return (
                            clickedDate >= disabledDate.start &&
                            clickedDate <= disabledDate.end
                        );
                    });
                }
                if (isDisabled) {
                    return false;
                }
                if ($("#appoint_div").hasClass("hide")) {
                    if (info.dateStr.length <= 10) {
                        info.dateStr += "T00:00:00";
                    }
                    var fmt = new DateFormatter();
                    var formattedDate = fmt.formatDate(
                        new Date(info.dateStr),
                        app.options.date_format
                    );
                    var formattedTime = fmt.formatDate(
                        new Date(info.dateStr),
                        app.options.time_format == 24 ? "H:i" : "g:i A"
                    );
                    $("input[name='appointment_date']").val(formattedDate);
                    $("input[name='appointment_start_time']").val(formattedTime);
                    $("#appoint_div").toggleClass("hide");
                    $("#calendar_div").toggleClass("");
                }
                return false;
            },
            dayCellDidMount: function(info) {
                var date = info.date.getDate();
                var month = info.date.getMonth() + 1;
                var year = info.date.getFullYear();
                var dateStr =
                    year +
                    "-" +
                    (month < 10 ? "0" + month : month) +
                    "-" +
                    (date < 10 ? "0" + date : date);
                if (disabledDates) {
                    var isDisabled = disabledDates.some(function(disabledDate) {
                        return dateStr >= disabledDate.start && dateStr <= disabledDate.end;
                    });
                }
                if (isDisabled) {
                    info.el.classList.add("fc-disabled-date");
                }
            },
            datesSet: function() {
                taCalendar.refetchEvents();
            },
        };
        taCalendar = new FullCalendar.Calendar(
            taCalendar_selector[0],
            taCalendar_settings
        );
        taCalendar.render();
        var smallCalendarSettings = {
            initialView: "dayGridMonth",
            headerToolbar: {
                left: "",
                center: "title",
                right: "prev,next",
            },
            height: "auto",
            selectable: true,
            datesSet: function(info) {
                requestGetJSON(
                    admin_url +
                    "appointment_manager/get_holidays/small"
                ).done(function(respons) {
                    disabledDates = respons;
                    if (disabledDates !== "") {
                        $('#small-calendar .fc-daygrid-day').each(function(index, cell) {
                            var dateStr = $(cell).data('date');
                            if (disabledDates.includes(dateStr)) {
                                $(cell).addClass('fc-disabled-date');
                                $(cell).on('click', function(e) {
                                    e.preventDefault();
                                });
                            }
                        });
                    }
                });
            },
            dateClick: function(info) {
                taCalendar.changeView("timeGridDay", info.dateStr);
            },
            eventSources: function(info, successCallback, failureCallback) {
                console.log('small-cal');
                var params = {};
                return $.getJSON(
                    admin_url + "appointment_manager/appointments_calendar",
                    $.extend({}, params, {
                        start: info.startStr,
                        end: info.endStr,
                    })
                ).then(function(data) {
                    successCallback(
                        data.map(function(e) {
                            return $.extend({}, e, {
                                title: "",
                                start: e.start || e.date,
                                extendedProps: {
                                    client: e.client,
                                    appointee: e.practitioner,
                                    location: e.location,
                                    treatment: e.treatment,
                                    rooms: e.rooms,
                                    time: e.time,
                                    duration: e.duration,
                                },
                            });
                        })
                    );
                });
            },
            eventContent: function(arg) {
                var customHtml = `<div class="fc-event-small-appointment cust-apm"><i class="fa-solid fa-circle"></i></div>`;
                return {
                    html: customHtml,
                };
            },
        };
        var smallCalendar = new FullCalendar.Calendar(
            document.getElementById("small-calendar"),
            smallCalendarSettings
        );
        smallCalendar.render();
    }
    validateAppointmentForm();
    $("div#calendar_div #location").change(function() {
        $("#appmgr_appointment_form").find("#appointee").val('').selectpicker('refresh');
        appointment_start_time(
            $("option:selected", this).data("tfrom"),
            $("option:selected", this).data("tto")
        );
        _init_practitioner_search();
        $('input[name="location"]').val($(this).val());
        updateVisibleTimeRange(
            $("option:selected", this).data("tfrom"),
            $("option:selected", this).data("tto")
        );
        roomsRendering($(this).val(), $('input[name="isEdit"]').val(), true);
    });

    _init_practitioner_loaded_search();

    $("#appointee_filter").change(function() {
        taCalendar.refetchEvents();
    });
    $("div#appointess select#staff").change(function() {
        taCalendar.refetchEvents();
    });

    function updateVisibleTimeRange(minTime, maxTime) {
        taCalendar.setOption("slotMinTime", minTime);
        taCalendar.setOption("slotMaxTime", maxTime);
    }

    $(document).on("click", ".edit-link", function(e) {
        e.preventDefault();
        if (capability.edit) {
            if ($("#appoint_div").hasClass("hide")) {
                if ($("#location").val() === "") {
                    alert_float("danger", "Please select a location first!");
                    return false;
                } else {
                    $("#appoint_div").toggleClass("hide");
                    _init_practitioner_search();
                }
            }
        }
    });

    $('#newAppointmentModal').on('hide.bs.modal', function() {
        $('#newAppointmentModal #appointment_start_time').datetimepicker('destroy');
        $('#newAppointmentModal #appointment_end_time').datetimepicker('destroy');
    });
    $('#editAppointmentModal').on('hide.bs.modal', function() {
        $('#editAppointmentModal #appointment_start_time').datetimepicker('destroy');
        $('#editAppointmentModal #appointment_end_time').datetimepicker('destroy');
    });

    function initializeDateTimePicker(form) {
        form.find($('#appointment_start_time').datetimepicker({
            datepicker: false,
            format: timeFormat,
            step: 30,
            validateOnBlur: false,
        }));
        form.find($('#appointment_end_time').datetimepicker({
            datepicker: false,
            format: timeFormat,
            step: 30,
            validateOnBlur: false,
        }));
    }
    $('#appmgr_appointment_form select[name="treatment"]').on('change', function() {
        init_service_categories_select($(this).val(), $('#appmgr_appointment_form select[name="service_cat[]"]'));
    })
    $('#add-appointment-form select[name="treatment"]').on('change', function() {
        init_service_categories_select($(this).val(), $('#add-appointment-form select[name="service_cat[]"]'));
    })
    validate_calendar_form();
});

initDataTable(".table-appmgr_bookings", window.location.href, [], []);

function getDisabledTimeIntervals() {
    return [
        [moment("2024-08-05 09:00"), moment("2024-08-05 10:00")], // Disable 9:00 AM to 10:00 AM
        [moment("2024-08-05 12:00"), moment("2024-08-05 13:00")], // Disable 12:00 PM to 1:00 PM
    ];
}

function appointment_start_time(minT, maxT) {
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
            data.location = $("#location").val();
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
                            console.log(selectedDate + '  apttime: ' + formatTime(el.appointment_time));
                            if (el.appointment_time) {
                                var currentTime = $("body").find(
                                    '.xdsoft_time:contains("' + formatTime(el.appointment_time) + '")'
                                );
                                console.log(currentTime);

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

function _init_practitioner_search() {
    init_ajax_search(
        "practitioner",
        "#appointee", {
            location: function() {
                return $("#location").val() != "" ?
                    $("#location").val() :
                    $('input[name="location"]').val();
            },
            appointment_date: function() {
                return $("#appointment_date").val();
            }
        },
        admin_url + "appointment_manager/ajax_search_practitioner"
    );
}

function _init_practitioner_loaded_search() {
    init_ajax_search(
        "practitioner",
        "#appointee_filter", {
            location: function() {
                return $("#location").val();
            },
            appointment_date: function() {
                return $("#appointment_date").val();
            }
        },
        admin_url + "appointment_manager/ajax_search_practitioner"
    );
}

function view_appointment(id) {
    var appointmentDateTimePickerOptions = {};
    appointmentDateTimePickerOptions.formatTime = timeFormat;
    appointmentDateTimePickerOptions.datepicker = false;
    appointmentDateTimePickerOptions.format = timeFormat;
    appointmentDateTimePickerOptions.timezone = app.options.timezone;
    appointmentDateTimePickerOptions.validateOnBlur = false;
    if (typeof id == "undefined") {
        return;
    }
    if (!capability.edit) {
        return false;
    }
    $('button[type="submit"]').text("Update");

    $.post(admin_url + "appointment_manager/view_appointment/" + id).done(function(
        response
    ) {
        response = JSON.parse(response);
        $('input[name="location"]').val(response.event.location);
        $('input[name="isEdit"]').val(response.event.aptid);
        $('select[name="status"]').val(response.event.status).selectpicker('refresh');
        $('input[name="reminder_before"]').val(response.event.reminder_before);
        $('select[name="reminder_before_type"]')
            .val(response.event.reminder_before_type)
            .selectpicker('refresh');
        roomsRendering(response.event.location, response.event.aptid, true);
        init_service_categories_select(response.event.treatment, $('#appmgr_appointment_form select[name="service_cat[]"]'), response.event.service_cat);
        if (response.event.appointee) {
            requestGetJSON(
                admin_url +
                "appointment_manager/get_appointies_json/" +
                response.event.appointee
            ).done(function(respons) {
                _init_practitioner_search();
                var appointeeSelect = $("form#appmgr_appointment_form").find("select[name=appointee]");
                appointeeSelect.append(new Option(respons.name, respons.appointeeid));
                appointeeSelect.val(response.event.appointee).trigger('change');
            });
        }

        $("form#appmgr_appointment_form")
            .find("#appointment_date")
            .val(response.event.appointment_date);

        $("form#appmgr_appointment_form")
            .find("select[name=client]")
            .val(response.event.client)
            .selectpicker('refresh');

        $("form#appmgr_appointment_form")
            .find("select[name=treatment]")
            .val(response.event.treatment)
            .selectpicker('refresh');

        $("form#appmgr_appointment_form")
            .find("#description")
            .val(response.event.aptdesc);

        $("form#appmgr_appointment_form")
            .find("#appointment_start_time")
            .val(response.event.startTime).datetimepicker(appointmentDateTimePickerOptions);
        $("form#appmgr_appointment_form")
            .find("#appointment_end_time")
            .val(response.event.endTime).datetimepicker(appointmentDateTimePickerOptions);

        init_selectpicker();

        _init_practitioner_loaded_search();

        $("#appoint_div").toggleClass("hide");
    });
}

function validateAppointmentForm() {
    appValidateForm($("form"), {
        appointee: "required",
        appointment_date: "required",
        appointment_start_time: "required",
        appointment_end_time: "required",
        client: "required",
        status: "required"
    });
}

function closeAppointmentModal() {
    $("#appmgr_appointment_form input[name='location']").val('');
    $("#appmgr_appointment_form input[name='isEdit']").val('');
    $("#appmgr_appointment_form").find("#appointee").val('').selectpicker('refresh');
    $("#appmgr_appointment_form").find("#treatment").val('').selectpicker('refresh');
    $("#appmgr_appointment_form").find("#client").val('').selectpicker('refresh');
    $("#appmgr_appointment_form").find("#status").val('').selectpicker('refresh');
    $("#appmgr_appointment_form").find(".rooms_appointment_form").html('');
    $("#appmgr_appointment_form")[0].reset();
    init_selectpicker();
    $("#appoint_div").toggleClass("hide");
}

function roomsRendering(locid, appid, check = false) {
    var params = {};
    params['check'] = check;
    requestGetJSON(
        admin_url + "appointment_manager/getrooms/" + locid + "/" + appid,
        params
    ).done(function(response) {
        if (response.success === true || response.success == "true") {
            $(".rooms_appointment_form").html(response.html);
        } else {
            $(".rooms_appointment_form").html("Not Available");
        }
    });
}
$(document).on("click", ".clk-evnt", function() {
    $("body").toggleClass("cldr-full-view");
});

function initAppointmentDateInput(event) {
    var data = {};
    var todaysDate = new Date();
    var currentDate =
        todaysDate.getFullYear() +
        "-" +
        (todaysDate.getMonth() + 1 < 10 ? "0" : "") +
        (todaysDate.getMonth() +
            1 +
            "-" +
            (todaysDate.getDate() < 10 ? "0" : "") +
            todaysDate.getDate());
    data.location = event.location;
    data.appointee = event.appointee;
    data.appointment_date = event.appointment_date;
    $.post("appointment_manager/get_practitioner_busy_times", data).done(function(r) {
        r = JSON.parse(r);
        console.log(r);
        var dateFormat = app.options.date_format;
        var appointmentDatePickerOptions = {
            dayOfWeekStart: app.options.calendar_first_day,
            daysOfWeekDisabled: [],
            timezone: "America/Toronto",
            minDate: 0,
            closeOnDateSelect: 0,
            closeOnTimeSelect: 1,
            validateOnBlur: false,
            onGenerate: function(ct) {
                var selectedDate =
                    ct.getFullYear() +
                    "-" +
                    (ct.getMonth() + 1 < 10 ? "0" : "") +
                    (ct.getMonth() +
                        1 +
                        "-" +
                        (ct.getDate() < 10 ? "0" : "") +
                        ct.getDate());
                $(r).each(function(i, el) {
                    console.log(el.appointment_date, selectedDate);

                    if (el.appointment_date == selectedDate) {
                        if (el.start_hour) {
                            var currentTime = $("body").find(
                                '.xdsoft_time:contains("' + el.start_hour + '")'
                            );
                            currentTime.addClass("busy_time");
                        }
                    }
                });
            },
            onSelectDate: function(ct, $input) {
                $input.val("");
                var selectedDate =
                    ct.getFullYear() +
                    "-" +
                    (ct.getMonth() + 1 < 10 ? "0" : "") +
                    (ct.getMonth() +
                        1 +
                        "-" +
                        (ct.getDate() < 10 ? "0" : "") +
                        ct.getDate());

                setTimeout(function() {
                    $("body")
                        .find(".xdsoft_time")
                        .removeClass("xdsoft_current xdsoft_today");

                    if (currentDate !== selectedDate) {
                        $("body")
                            .find(".xdsoft_time.xdsoft_disabled")
                            .removeClass("xdsoft_disabled");
                    }
                }, 200);
            },
            onChangeDateTime: function() {
                var currentTime = $("body").find(".xdsoft_time");
                console.log("Entered");
                currentTime.removeClass("busy_time");
            },
        };
    });
}

$(function() {
    var ClienmtBookingServerParams = [];
    ClienmtBookingServerParams["client_user_id"] = '[name="client_user_id"]';
    console.log(ClienmtBookingServerParams);
    initDataTable(
        ".table-appmgr_client_bookings",
        admin_url + "appointment_manager", [], [],
        ClienmtBookingServerParams
    );
    $("#appmgr_appointment_form #appointee").change(function(event) {
        if ($('input[name="isEdit"]').val() == '') {
            $("#appointment_date").val('');
            $("#appointment_start_time").val('');
            $("#appointment_end_time").val('');
        }
        $.post(admin_url + 'appointment_manager/practionars_availibility', { appointee: $(this).val() }, function(response) {
            response = JSON.parse(response);
            if (response !== null) {
                var unavailibilityDates = [];
                if (response.unavailibility != 'undefined') {
                    $.each(response.unavailibility, function(index, item) {
                        if (isValidDate(item['unavailable_date'])) {
                            unavailibilityDates.push(item['unavailable_date']);
                        }
                    })
                }
                if (response.holidays != 'undefined' || response.holidays != null) {
                    $.each(response.holidays, function(index, item) {
                        if (isValidDate(item)) {
                            unavailibilityDates.push(item);
                        }
                    })
                }
                appointmentDateReRenderForAvailibility(response.availibility, unavailibilityDates, $("#appmgr_appointment_form"));
            }
        });
    });
    $("#edit-form-appointment #appointee").change(function(event) {
        $.post(admin_url + 'appointment_manager/practionars_availibility', { appointee: $(this).val() }, function(response) {
            response = JSON.parse(response);
            var unavailibilityDates = [];
            if (response.unavailibility != 'undefined') {
                $.each(response.unavailibility, function(index, item) {
                    if (isValidDate(item['unavailable_date'])) {
                        unavailibilityDates.push(item['unavailable_date']);
                    }
                })
            }
            if (response.holidays != 'undefined' || response.holidays != null) {
                $.each(response.holidays, function(index, item) {
                    if (isValidDate(item)) {
                        unavailibilityDates.push(item);
                    }
                })
            }
            appointmentDateReRenderForAvailibility(response.availibility, unavailibilityDates, $("#edit-form-appointment"));
        });
    });
    $("#edit-appointment-form #appointee").change(function(event) {
        $.post(admin_url + 'appointment_manager/practionars_availibility', { appointee: $(this).val() }, function(response) {
            response = JSON.parse(response);
            var unavailibilityDates = [];
            if (response.unavailibility != 'undefined') {
                $.each(response.unavailibility, function(index, item) {
                    if (isValidDate(item['unavailable_date'])) {
                        unavailibilityDates.push(item['unavailable_date']);
                    }
                })
            }
            if (response.holidays != 'undefined' || response.holidays != null) {
                $.each(response.holidays, function(index, item) {
                    if (isValidDate(item)) {
                        unavailibilityDates.push(item);
                    }
                })
            }
            appointmentDateReRenderForAvailibilityEditAppointmentClient(response.availibility, unavailibilityDates);
        });
    });
    $("#add-appointment-form #appointee").change(function(event) {
        $.post(admin_url + 'appointment_manager/practionars_availibility', { appointee: $(this).val() }, function(response) {
            response = JSON.parse(response);
            var unavailibilityDates = [];
            if (response.unavailibility != 'undefined') {
                $.each(response.unavailibility, function(index, item) {
                    if (isValidDate(item['unavailable_date'])) {
                        unavailibilityDates.push(item['unavailable_date']);
                    }
                })
            }
            if (response.holidays != 'undefined' || response.holidays != null) {
                $.each(response.holidays, function(index, item) {
                    if (isValidDate(item)) {
                        unavailibilityDates.push(item);
                    }
                })
            }
            appointmentDateReRenderForAvailibility(response.availibility, unavailibilityDates, $("#newAppointmentModal #add-appointment-form"));
        });
    });
});

function appointment_mark_as(status, appointment_id) {
    var url = "appointment_manager/mark_as/" + status + "/" + appointment_id;
    $("body").append('<div class="dt-loader"></div>');
    requestGetJSON(url).done(function(response) {
        $("body").find(".dt-loader").remove();
        if (response.success === true || response.success == "true") {
            reloadTable(".table-appmgr_bookings");
            reloadTable(".table-appmgr_client_bookings");
        }
    });
}

function reloadTable(table) {
    if ($.fn.dataTable.isDataTable(table)) {
        $(table).DataTable().ajax.reload(null, false);
    }
}

function addAppointment() {
    if (capability.create) {
        if ($("#appoint_div").hasClass("hide")) {
            if ($("#location").val() === "") {
                alert_float("danger", "Please select a location first!");
                return false;
            } else {
                $("#appoint_div").toggleClass("hide");
                _init_practitioner_search();
            }
        }
    }
}

function getTaCapabilty() {
    return requestGetJSON("appointment_manager/getTaCapabilty/").done(function(
        response
    ) {
        capability.create = response.create;
        capability.edit = response.edit;
    });
}

function fetchPractitionerAvailability(appointeeId) {
    var csrf_token = $('input[name="csrf_token_name"]').val();
    $.ajax({
        url: "<?php echo site_url('appointment_manager/get_practitioner_availability'); ?>",
        type: "POST",
        data: {
            csrf_token_name: csrf_token,
            appointee_id: appointeeId,
        },
        dataType: "json",
        success: function(data) {
            if (data.success) {
                populateTimePickers(data.availability);
                populateRooms(data.rooms);
            } else {
                alert("Error fetching practitioner availability");
            }
        },
        error: function() {
            alert("Error in AJAX request");
        },
    });
}

function populateTimePickers(availability) {
    var startTimePicker = $("#appointment_start_time");
    var endTimePicker = $("#appointment_end_time");
    startTimePicker.datetimepicker({
        datepicker: false,
        format: timeFormat,
        step: 30,
        validateOnBlur: false,
        minTime: availability.start_time,
        maxTime: availability.end_time,
    });

    endTimePicker.datetimepicker({
        datepicker: false,
        format: timeFormat,
        step: 30,
        validateOnBlur: false,
        minTime: availability.start_time,
        maxTime: availability.end_time,
    });
}

function populateRooms(rooms) {
    var roomsContainer = $(".rooms_appointment_form");
    roomsContainer.empty();

    if (rooms.length > 0) {
        $.each(rooms, function(index, room) {
            roomsContainer.append(
                '<div class="checkbox"><label><input type="checkbox" name="rooms[]" value="' +
                room.id +
                '"> ' +
                room.name +
                "</label></div>"
            );
        });
    } else {
        roomsContainer.html("No rooms available for the selected practitioner.");
    }
}

function edit_appointment_client(invoker, id) {
    $("#edit-appointment-form").find('#appointment_date').datetimepicker('destroy');
    $("#edit-appointment-form").find('#appointment_date').datetimepicker({
        format: app.options.date_format,
        timepicker: false
    });
    var data = {};
    $.post($(invoker).data("href"), data, function(response) {
        try {
            response = JSON.parse(response);
            if (response && response.appointment) {
                $("#edit-appointment-form").find("#clocation").val(response.appointment.location).selectpicker('refresh');
                $("#edit-appointment-form").find("#cust_fields_area").html(response.c_fileds);
                appointment_start_time_for_client_page(
                    response.appointment.operation_start_time,
                    response.appointment.operation_end_time,
                    $("#edit-appointment-form")
                );
                if (response.appointment.appointee) {
                    requestGetJSON(
                            admin_url +
                            "appointment_manager/get_appointies_json/" +
                            response.appointment.appointee
                        )
                        .done(function(respons) {
                            var appointeeSelect = $("form#edit-appointment-form").find(
                                "select[name=appointee]"
                            );
                            appointeeSelect.append(new Option(respons.name, respons.appointeeid));
                            appointeeSelect.val(respons.appointeeid).trigger('change');
                        })
                        .fail(function() {
                            alert("Failed to load appointee data.");
                        });
                    _init_practitioner_search_for_client();
                }
                init_service_categories_select(response.appointment.treatment, $('#edit-appointment-form select[name="service_cat[]"]'), response.appointment.service_cat);
                $("#edit-appointment-form").find("#clocation").change(function() {
                    var locationId = $(this).val();
                    var selectedLoc = null;
                    $(response.locations).each(function(index, el) {
                        if (locationId == response.locations[index].id) {
                            selectedLoc = response.locations[index];
                        }
                    });
                    var start_time = selectedLoc.operation_start_time;
                    var end_time = selectedLoc.operation_end_time;
                    appointment_start_time_for_client_page(start_time, end_time, $("#edit-appointment-form"));
                    var locationId = $(this).val();
                    $.ajax({
                        url: admin_url + "appointment_manager/ajax_search_practitioner",
                        type: "POST",
                        data: { location: locationId },
                        dataType: "json",
                        success: function(data) {
                            $("#edit-appointment-form").find("#appointee").empty().append('<option value=""></option>');
                            $.each(data, function(key, value) {
                                $("#edit-appointment-form").find("#appointee").append(
                                    '<option value="' + value.id + '">' + value.name + "</option>"
                                );
                            });
                            $("#edit-appointment-form").find("#appointee").selectpicker("refresh");
                        },
                    });

                });

                $("#edit-appointment-form").find("#appointment_date").val(response.appointment.appointment_date);
                $("#edit-appointment-form").find("#appointment_start_time").val(
                    response.appointment.startTime
                );
                $("#edit-appointment-form").find("#appointment_end_time").val(
                    response.appointment.endTime
                );
                $("#edit-appointment-form").find("#client").val(response.appointment.client).trigger("change");
                $("#edit-appointment-form").find("#treatment").val(response.appointment.treatment).trigger("change");
                $("#edit-appointment-form").find("#description").val(response.appointment.aptdesc);
                $("#edit-appointment-form").find("#reminder_before").val(response.appointment.reminder_before);
                $("#edit-appointment-form").find("#reminder_before_type")
                    .val(response.appointment.reminder_before_type)
                    .trigger("change");
                $("input[name=isEdit]").val(id);
                init_selectpicker();
                var locationId = response.appointment.location;
                $("input[name=location]").val(locationId);
                if (locationId) {
                    loadRooms(locationId, id);
                }
                if (response.rooms) {
                    $('input[name="rooms[]"]').prop("checked", false);
                    $.each(response.rooms, function(index, room) {
                        $('input[name="rooms[]"][value="' + room + '"]').prop(
                            "checked",
                            true
                        );
                    });
                }
                if (response.statuses) {
                    $("#edit-appointment-form").find("#status").val(response.appointment.status).trigger("change");
                }
                $("#editAppointmentModal").modal("show");
                init_selectpicker();
            } else {
                alert("Failed to load appointment data.");
            }
        } catch (e) {
            console.error("Error parsing response: ", e);
            alert("Error processing the request.");
        }
    }).fail(function() {
        alert("Failed to fetch appointment data.");
    });
}

function loadRooms(locationId, id) {
    var params = {};
    params['check'] = true;
    requestGetJSON(
        admin_url + "appointment_manager/getrooms/" + locationId + "/" + id
    ).done(function(response) {
        if (response.success === true || response.success == "true") {
            $(".rooms_appointment_form").html(response.html);
        } else {
            $(".rooms_appointment_form").html("Not Available");
        }
    });
}

function add_clients_appointment() {
    $("#add-appointment-form").find('#appointment_date').datetimepicker('destroy');
    $("#add-appointment-form").find('#appointment_date').datetimepicker({
        format: app.options.date_format,
        timepicker: false
    });
    $("#newAppointmentModal").find("form#add-appointment-form")[0].reset();
    $("#add-appointment-form").find("#clocation").selectpicker('refresh');
    $("#add-appointment-form").find("#appointee").val('').selectpicker('refresh');
    $("#add-appointment-form").find("#treatment").selectpicker('refresh');
    $("#add-appointment-form").find("#status").selectpicker('refresh');
    $("#add-appointment-form").find(".rooms_appointment_form").html('');
    appointment_start_time_for_client_page(
        $("option:selected", this).data("tfrom"),
        $("option:selected", this).data("tto"),
        $("#add-appointment-form")
    );
    $("#newAppointmentModal").modal("show");
    $("#add-appointment-form").find("#clocation").change(function() {
        appointment_start_time_for_client_page(
            $("option:selected", this).data("tfrom"),
            $("option:selected", this).data("tto"),
            $("#add-appointment-form")
        );
        var locationId = $(this).val();
        $.ajax({
            url: admin_url + "appointment_manager/ajax_search_practitioner",
            type: "POST",
            data: {
                location: locationId,
            },
            dataType: "json",
            success: function(data) {
                $("#add-appointment-form").find("#appointee").empty().append('<option value=""></option>');
                $.each(data, function(key, value) {
                    $("#add-appointment-form").find("#appointee").append(
                        '<option value="' + value.id + '">' + value.name + "</option>"
                    );
                });

                $("#add-appointment-form").find("#appointee").selectpicker("refresh");
            },
        });
        $.ajax({
            url: admin_url + "appointment_manager/getrooms/" + locationId,
            type: "GET",
            dataType: "json",
            data: { check: true },
            success: function(response) {
                if (response.success === true || response.success == "true") {
                    $("#add-appointment-form").find(".rooms_appointment_form").html(response.html);
                } else {
                    $("#add-appointment-form").find(".rooms_appointment_form").html("Not Available");
                }
            },
            error: function() {
                $("#add-appointment-form").find(".rooms_appointment_form").html("Error loading rooms.");
            },
        });
    });

    $("#newAppointmentModal #appointee").change(function() {
        var locationId = $("#add-appointment-form").find("#clocation").val();
        var appointeeId = $(this).val();
        if (locationId && appointeeId) {
            rooms_Rendering(locationId, appointeeId);
        } else {
            $("#add-appointment-form").find(".rooms_appointment_form").html("");
        }
    });
}

function rooms_Rendering(locid, appid) {
    var params = {};
    params['check'] = false;
    requestGetJSON(
        admin_url + "appointment_manager/getrooms/" + locid + "/" + appid,
        params
    ).done(function(response) {
        if (response.success === true || response.success == "true") {
            $(".rooms_appointment_form").html(response.html);
        } else {
            $(".rooms_appointment_form").html("Not Available");
        }
    });
}

function appointment_start_time_for_client_page(minT, maxT, form) {
    var appointmentDateTimePickerOptions = {};
    appointmentDateTimePickerOptions.formatTime = timeFormat;
    appointmentDateTimePickerOptions.step = 30;
    appointmentDateTimePickerOptions.datepicker = false;
    appointmentDateTimePickerOptions.timezone = app.options.timezone;
    appointmentDateTimePickerOptions.defaultDate = new Date();
    form.find("#appointment_start_time").datetimepicker({
        datepicker: false,
        validateOnBlur: false,
        timezone: app.options.timezone,
        format: timeFormat,
        onShow: function() {
            if (minT && maxT) {
                this.setOptions({
                    minTime: minT,
                    maxTime: maxT,
                });
            }
        },
        onGenerate: function(ct) {
            var data = {};
            data.location = form.find("#clocation").val();
            data.appointee = form.find("#appointee").val();
            var selectedDate = form.find("input[name='appointment_date']").val();
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
    });

    form.find("#appointment_start_time").datetimepicker(appointmentDateTimePickerOptions);

    form.find("#appointment_end_time").datetimepicker({
        datepicker: false,
        validateOnBlur: false,
        timezone: app.options.timezone,
        format: timeFormat,
        onShow: function() {
            this.setOptions({
                minTime: form.find("#appointment_start_time").val(),
                maxTime: maxT,
            });
        },
        onChangeDateTime: function(currentTime, $input) {
            console.log('asdads')
                //return;
        },
        onGenerate: function(ct) {
            console.log('dsdasdas', ct);
        },
    });
    form.find("#appointment_end_time").datetimepicker(appointmentDateTimePickerOptions);
    jQuery.datetimepicker.setLocale(app.locale);
}

function convertTo24Hour(timeStr) {
    var [time, modifier] = timeStr.split(' '); // Split time and AM/PM.
    var [hours, minutes] = time.split(':'); // Split hours and minutes.

    if (modifier === 'PM' && hours !== '12') {
        hours = parseInt(hours, 10) + 12;
    } else if (modifier === 'AM' && hours === '12') {
        hours = '00';
    }

    hours = hours.toString().padStart(2, '0');
    minutes = minutes.padStart(2, '0');
    return hours + ':' + minutes;
}

function _init_practitioner_search_for_client() {
    init_ajax_search(
        "practitioner",
        "#appointee", {
            location: function() {
                return $("#clocation").val();
            },
            appointment_date: function() {
                return $("#appointment_date").val();
            }
        },
        admin_url + "appointment_manager/ajax_search_practitioner"
    );
}

function formatTime(timeString) {
    const parts = timeString.split(' ');
    let time = parts[0];
    let period = parts.length > 1 ? parts[1] : ''; // Check if period exists

    const [hours, minutes] = time.split(':');

    const formattedHours = parseInt(hours, 10);

    return `${formattedHours}:${minutes} ${period}`.trim(); // Trim to remove extra space if period is empty
}

function isValidDate(dateString) {
    const date = new Date(dateString);
    return date instanceof Date && !isNaN(date);
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

function appointmentDateReRenderForAvailibilityEditAppointmentClient(availability = null, unavailibilityDates) {
    if ($("#editAppointmentModal #edit-appointment-form #appointment_date").data('DateTimePicker') === undefined) {
        console.log('DateTimePicker is destroyed in availibility');
    } else {
        $("#editAppointmentModal #edit-appointment-form #appointment_date").datetimepicker('destroy');
    }
    var params = {};
    if (availability && availability.repetition == 'monthly' && isValidDate(availability.available_date_from) && isValidDate(availability.available_date_to)) {
        params.minDate = availability.available_date_from;
        params.maxDate = availability.available_date_to;
    }
    params.timepicker = false;
    params.format = app.options.date_format;
    params.onGenerate = function(ct) {
        console.log(unavailibilityDates);

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
    $("#editAppointmentModal #edit-appointment-form #appointment_date").datetimepicker(params);
}

function copyEmbeddedCode() {
    var embeddedCode = `<iframe width="600" height="850" src="` + site_url + `appointment_manager/appointment_manager_client/public_form" frameborder="0" sandbox="allow-top-navigation allow-forms allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe>`;
    var tempTextarea = document.createElement('textarea');
    tempTextarea.value = embeddedCode;
    document.body.appendChild(tempTextarea);
    tempTextarea.select();
    document.execCommand('copy');
    document.body.removeChild(tempTextarea);
    alert_float('success', 'Embedded code copied to clipboard!');
    return false;
}

function init_service_categories_select(serviceId, elem, selectedValues = []) {
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
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            console.error("Failed to fetch service categories:", textStatus, errorThrown);
        });
}