$(document).ready(function () {
    var roomFormModal = $('#room_form'),
            statisticsTable = $('#calendar_statistics'),
            addOrderPopupForm = $('#create_reservation_popup_form');


    $('#calendar_reservation').roomCalendar({
        headConfig: {
            buttonNextPrev: true,
            headerTitle: ""
        },
        countShowDay: 25,
        sourceConfig: {
            actionUrl: reservationCalendarActionUrl,
            dataType: 'JSON'
        },
        locationFilter: {
            enable: true,
            actionUrl: '',
            dataType: 'JSON',
            items: reservationFilterLocationItems
        },
        items: []
    });
    
    /**
     * Show info for reservation calendar cell
     * 
     */
    $('#calendar_reservation').on("rc-roomclick", function (event, param) {
        var orderNumberField = roomFormModal.find('#order_number_info'),               
                orderSumField = roomFormModal.find('#order_sum_info'),
                clientNameField = roomFormModal.find('#client_name_info'),
                orderPaymentStatus = roomFormModal.find('#order_payment_status_info'),
                orderNumberPeopleField = roomFormModal.find('#order_number_of_people_info'),
                formTitle = roomFormModal.find('.modal-header .modal-title');
        if (true !== param.roomData.roomFree) {          
            clientNameField.text(param.cellData.client.title);
            if(param.cellData.client.email !== undefined){
                $('#client_email_info').text(param.cellData.client.email);
            }else{
                $('#client_email_info').text("-");
            }
            if(param.cellData.client.phone !== undefined){
                $('#client_phone_info').text(param.cellData.client.phone);
            }else{
                $('#client_phone_info').text("-");
            }
            
            /*Payment information*/
            orderSumField.text(param.cellData.summ.value + ' ' + param.cellData.summ.currency);
            $('#order_tax_info').text(param.cellData.tax.value + ' ' + param.cellData.tax.currency);
            $('#order_total_sum_info').text(param.cellData.total_sum.value + ' ' + param.cellData.total_sum.currency);
            $('#order_need_to_pay_info').text(param.cellData.need_to_pay.value + ' ' + param.cellData.need_to_pay.currency);
            $('#order_already_payed_info').text(param.cellData.alredy_payed.value + ' ' + param.cellData.alredy_payed.currency);
            $('#order_need_to_pay_total_info').text(param.cellData.need_to_pay_total.value + ' ' + param.cellData.need_to_pay_total.currency);
            $('#order_already_payed_total_info').text(param.cellData.alredy_payed_total.value + ' ' + param.cellData.alredy_payed_total.currency);
            $('#order_deposit_info').text(param.cellData.deposit + ' ' + param.cellData.alredy_payed_total.currency);
            $('#order_deposit_flag_info').text(param.cellData.deposit_flag);
            orderPaymentStatus.text(param.cellData.payment_status.text);
            $('#order_payment_method_info').text(param.cellData.payment_method.title);
            /*Order Details*/
            orderNumberField.text(param.cellData.number);
            $('#order_reservation_period_info').text(param.cellData.check_in + ' - ' + param.cellData.check_out);
            orderNumberPeopleField.text(param.cellData.number_of_people);
            $('#order_status_info').text(param.cellData.status.text);
            $('#order_resident_status_info').text(param.cellData.resident_status.text);
            $('#order_link').attr("href", '/order/details/?id=' + param.cellData.id);
            roomFormModal.modal('toggle');
        }        

    });

    /*Render statistics*/
    $('#calendar_reservation').on("rc-datareceived", function (event, params) {          
        
    });
    
    /*Add new order from reservation*/
    $('#calendar_reservation').on("rc-multiple-selection", function (event, param) {
        /*Generate form reservations html*/        
        var reservations = param.data,
            currentLocation = $('#rc-select-location').val(),
            currentLocationText = $('#rc-select-location option:selected').text(),
            reservationHtml = '';
        for(var ind = 0; ind < reservations.length; ind++){
            var reservationNumber = ind + 1,
                bedRoom = reservations[ind].roomBed.split("/"),
                room = bedRoom[0].slice(1), 
                bed = parseInt(bedRoom[1]);
            reservationHtml += '<div class="reservation-item-wrapper" data-reservation-number="' + reservationNumber + '">'
                                    +'<h4 class="reservation-header">Reservation #' + reservationNumber + (reservationNumber !== 1 ? '<button id="remove_reservation_' + reservationNumber + '" data-reservation="' + reservationNumber + '" class="btn btn-danger btn-xs pull-right">Remove</button>' : '') + '</h4>'
                                    +'<div class="form-group required">'
                                        +'<label for="reservation_location_text' + reservationNumber + '" class="col-lg-2 control-label">Location</label>'
                                        +'<div class="col-lg-10">'
                                            +'<select class="form-control" id="reservation_location' + reservationNumber + '" data-app-location="' + currentLocation + '" disabled="">'
                                                +'<option id="reservation_location_text' + reservationNumber + '" value="' + currentLocation + '">' + currentLocationText + '</option>'
                                            +'</select>'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="form-group required">'
                                        +'<label for="reservation_room' + reservationNumber + '" class="col-lg-2 control-label">Room</label>'
                                        +'<div class="col-lg-10">'
                                            +'<select class="form-control" id="reservation_room' + reservationNumber + '" disabled="">'
                                                +'<option value="' + reservations[ind].roomId + '">' + room + '</option>'
                                            +'</select>'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="form-group required">'
                                        +'<label for="reservation_bed' + reservationNumber + '" class="col-lg-2 control-label">Bed</label>'
                                        +'<div class="col-lg-10">'
                                            +'<select class="form-control" id="reservation_bed' + reservationNumber + '" disabled="">'
                                                +'<option class="form-control"  value="' + reservations[ind].bedId + '" data-price="' + reservations[ind].bedPricePerDay + '" data-price-month="' + reservations[ind].bedPricePerMonth + '">' + bed + '</option>'
                                            +'</select>'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="form-group required">'
                                        +'<label for="reservation_check_in' + reservationNumber + '" class="col-lg-2 control-label">Check In</label>'
                                        +'<div class="col-lg-10">'
                                            +'<input class="form-control" id="reservation_check_in' + reservationNumber + '" placeholder="Check In" type="text" value="' + reservations[ind].dateRange[0] + '">'
                                        +'</div>'
                                    +'</div>'
                                    +'<div class="form-group required">'
                                        +'<label for="reservation_check_out' + reservationNumber + '" class="col-lg-2 control-label">Check Out</label>'
                                        +'<div class="col-lg-10">'
                                            +'<input class="form-control" id="reservation_check_out' + reservationNumber + '" placeholder="Check Out" type="text" value="' + reservations[ind].dateRange[reservations[ind].dateRange.length - 1] + '">'
                                        +'</div>'
                                    +'</div>'
                            +'</div>';
        }
        /*Clear old reservation*/
        $('.reservation-item-wrapper').remove();
        $(".order-reservation-block").append(reservationHtml);
        /*Set event for reservations*/
        $('input[id^="reservation_check_in"]').datetimepicker({
                pickTime: false,
                language: 'en',
                format: 'YYYY-MM-DD'
            });
        $('input[id^="reservation_check_out"]').datetimepicker({
            pickTime: false,
            language: 'en',
            format: 'YYYY-MM-DD'
        });
        /*Set event for new remove button reservation*/
        $('button[id^="remove_reservation_').on("click", function () {
            var el = $(this),
                    wrapper = el.parents('div.reservation-item-wrapper');
            wrapper.empty();
            wrapper.remove();
            $('#form_reservation').data("reservation_checked", false);
            return false;
        });      
        addOrderPopupForm.modal('show');
    });
    
    addOrderPopupForm.on("hide.bs.modal", function(){
        $('#calendar_reservation').roomCalendar('clearMultipleSelectionData');
    });

});