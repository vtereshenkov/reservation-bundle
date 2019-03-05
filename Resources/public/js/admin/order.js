/* 
 * Script for orders
 */

$(document).ready(function () {
    var clientName = $('#client_name'),
            clientEmail = $('#client_email'),
            clientSocial = $('#client_social_profile'),
            clientPhone = $('#client_phone'),          
            reservationMessageBlock = $('#reservation_message_block'),
            orderMessageBlock = $('#order_message_block'),
            orderReservationBlock = $('.order-reservation-block'),
            mainForm = $('#form_reservation'),
            orderDetailForm = $('.order-detail-form'),
            reservationItemInit = $('.reservation-item-wrapper'),
            buttonCalculateSumm = $('.calvulate-sum-reservation'),
            orderField = $('#order_summ'),
            taxField = $('#order_tax'),
            totalSumField = $('#order_total_sum'),
            needToPayField = $('#order_need_to_pay'),
            needToPayTotalField = $('#order_need_to_pay_total'),
            invoiceCustomSumBlock = $('.invoice-sum-group'),
            invoiceCustomSumField = $('#order_invoice_sum'),          
            orderCreateInvoiceSelect = $('#order_create_invoice'),
            invoiceSumHelper = $('#invoice_sum_helper'),
            invoiceSumHelperBlock = $('.invoice-sum-group .help-block'),
            orderPaymentMethod = $('#order_payment_method'),
            checkboxSendEmailBlock = $('.checkbox-send-email'),
            checkboxSendEmail = $('#send_email_payment'),
            reservationList = [],
            selectLocationList = [],
            taxKoef = parseFloat($('#tax_koef_value').val()),
            enableTax = $('#enable_tax'),
            nextReservationNumber = 2,
            airnbnTaxActive = false;

    var orderInterface = {
        init: function () {
            var me = this;
            var selectLocationItems = $('#reservation_location1 option'),
                    me = this;
            selectLocationItems.each(function (index, elem) {
                var el = $(elem);
                selectLocationList[index] = {
                    value: el.val(),
                    text: (el.data("address")!=="" ? el.data("address") :'Select location'),
                    address: (el.data("address-code")!=="" ? el.data("address-code") :'Select location')
                };
            });
            var appId = $('#app_id').val(),
                    orderId = $('#order_id').val();
            if (appId !== "" || (orderId !== "" && orderId !== undefined)) {
                /*Find value select*/
                var valueOptionLocaton = "",
                        appLocation = $('#reservation_location1').data("app-location");
                for (var i = 0; i < selectLocationList.length; i++) {
                    if (selectLocationList[i].address === appLocation) {
                        valueOptionLocaton = selectLocationList[i].value;
                        break;
                    }
                }
                ;

                reservationItemInit.each(function () {
                    var el = $(this),
                            elNumber = el.data("reservation-number");
                    if (elNumber > 1) {
                        me.setEventNewSelectReservation(elNumber);
                        $('#remove_reservation_' + elNumber).on("click", function () {
                            var el = $(this),
                                    wrapper = el.parents('div.reservation-item-wrapper');
                            wrapper.empty();
                            wrapper.remove();
                            mainForm.data("reservation_checked", false);
                            return false;
                        });
                    }
                    if (orderId !== "" && orderId !== undefined) {
                        var editReservationOptionLocaton = $('#reservation_location' + elNumber).data("app-location");
                        $('#reservation_location' + elNumber).val(editReservationOptionLocaton);
                        $('#reservation_location' + elNumber).trigger("change");
                    } else {
                        if (valueOptionLocaton !== "") {
                            $('#reservation_location' + elNumber).val(valueOptionLocaton);
                            $('#reservation_location' + elNumber).trigger("change");
                        }
                    }

                });
                nextReservationNumber = reservationItemInit.length + 1;
            }
            /**
             * Calculate sum button
             */
            buttonCalculateSumm.on("click", function () {

                /*Get reservation list*/
                var reservations = me.getReservationList();
                if (reservations.message !== "success") {
                    var errorMessage = reservations.message;
                    /*Show reservation error*/
                    me.showMessage(reservationMessageBlock, errorMessage);
                } else {
                    me.renderPriceField(reservations.list);
                }


                return false;
            });
            
            enableTax.on("click", function () {
                /*Get reservation list*/
                var reservations = me.getReservationList();
                if (reservations.message !== "success") {
                    var errorMessage = reservations.message;
                    /*Show reservation error*/
                    me.showMessage(reservationMessageBlock, errorMessage);
                } else {
                    me.renderPriceField(reservations.list);
                }               
            });

            /* Field Invoice sum*/
            me.renderInvoicePaymentMethod();
            me.renderCheckBoxSendEmail();
            
            /*Airbnb method*/
            me.airbnbPaymentMethod();
            
            /*Send membership pdf*/
            $('#send_membership').on('click', function(){
                $('#send_membership_form').submit();
                return false;
            });
        },
        /**
         * Render Invoice Payment Method
         * 
         * @returns {void}
         */
        renderInvoicePaymentMethod: function () {
            var createInvoiceVal = orderCreateInvoiceSelect.val();
            switch (createInvoiceVal) {
                case 'without_invoice':
                    invoiceCustomSumBlock.hide();
                    invoiceSumHelperBlock.hide();                   
                    invoiceCustomSumField.attr('disabled', true);
                    break;
                case 'any_sum':
                    invoiceCustomSumField.attr('disabled', false);
                    invoiceSumHelperBlock.show();
                    invoiceCustomSumBlock.show();                    
                    break;
                case 'whole_sum':
                case '10_prepayment':
                    invoiceSumHelperBlock.hide();
                    invoiceCustomSumField.attr('disabled', true);
                    invoiceCustomSumBlock.show();                    
                    break;
            }
        },
        /**
         * Render CheckBox SendEmail
         * @returns {void}
         */
        renderCheckBoxSendEmail: function () {
            var paymentMethod = parseInt(orderPaymentMethod.val());
            if (paymentMethod === 4) {
                /*Braintree*/
                checkboxSendEmail.prop('checked', true);
                checkboxSendEmailBlock.show();
            } else {
                checkboxSendEmailBlock.hide();
                checkboxSendEmail.prop('checked', false);
            }

        },
        /**
         * Set tax for airbnb payment method (0$)
         * 
         * @returns {undefined}
         */
        airbnbPaymentMethod: function () {
            var paymentMethod = parseInt(orderPaymentMethod.val()),
                    me = this;
            if (paymentMethod === 5) {
                /*Airbnb*/
                airnbnTaxActive = true;
            } else {
                airnbnTaxActive = false;
            }
            if (reservationList.length > 0) {
                /*Render price*/
                me.renderPriceField(reservationList);
            }
        },
        /**
         * Update room select
         * 
         * @param {type} items
         * @param {type} bedSelectLoader
         * @param {type} roomSelect
         * @param {type} bedSelect
         * @returns {undefined}
         */
        updateRoomSelect: function (items, bedSelectLoader, roomSelect, bedSelect) {
            var renderHtml = '',
                    me = this;
            var roomSelectValue = roomSelect.data("app-room");
            if (items.length > 0) {
                $.each(items, function () {
                    renderHtml += '<option value="' + this.id + '">' + this.room_number + '</option>';
                });
                /*Get beds for first room*/
                bedSelectLoader.show();
                $.ajax({
                    type: "POST",
                    url: '/order/getbed',
                    data: {
                        room: (roomSelectValue !== undefined && roomSelectValue !== "" ? roomSelectValue : items[0].id)
                    },
                    success: function (data, status) {
                        if (status === "success") {
                            if (data.status === 'success') {
                                bedSelectLoader.hide();
                                me.updateBedSelect(data.beds, bedSelect);
                            } else {
                                window.location.href = data.redirect_error;
                            }
                        }
                    },
                    dataType: 'JSON'
                });
            } else {
                renderHtml += '<option value="">Room not found</option>';
            }
            roomSelect.empty();
            roomSelect.append(renderHtml);
            if (roomSelectValue !== undefined && roomSelectValue !== "") {
                roomSelect.val(roomSelectValue);
                roomSelect.attr("data-app-room", "");
                roomSelect.data("app-room", "");
                roomSelectValue = roomSelect.data("app-room");
            }


        },
        /**
         * Update bed select
         * 
         * @param {type} items
         * @param {type} bedSelect
         * @returns {undefined}
         */
        updateBedSelect: function (items, bedSelect) {
            var renderHtml = '',
                    me = this;
            var bedSelectValue = bedSelect.data("app-bed");
            if (items.length > 0) {
                $.each(items, function () {
                    renderHtml += '<option value="' + this.id + '" data-price="' + (this.price_per_day !== undefined ? this.price_per_day.value : 0) + '" data-price-month="' + (this.price_per_month !== undefined ? this.price_per_month.value : 0) + '">' + this.bed_number + '</option>';
                });
            } else {
                renderHtml += '<option value="">Bed not found</option>';
            }
            bedSelect.empty();
            bedSelect.append(renderHtml);
            if (bedSelectValue !== undefined && bedSelectValue !== "") {
                bedSelect.val(bedSelectValue);
                bedSelect.attr("data-app-bed", "");
                bedSelect.data("app-bed", "");
            }

        },
        showMessage: function (block, message, status) {
            var status = status || 'error';
            switch (status) {
                case 'error':
                    if (!block.hasClass('alert-danger')) {
                        block.addClass('alert-danger');
                    }
                    if (block.hasClass('alert-success')) {
                        block.removeClass('alert-success');
                    }
                    break;
                case 'success':
                    if (!block.hasClass('alert-success')) {
                        block.addClass('alert-success');
                    }
                    if (block.hasClass('alert-danger')) {
                        block.removeClass('alert-danger');
                    }
                    break;
            }
            block.find('strong').text(message);
            block.show();
            $('html, body').animate({scrollTop: block.offset().top - 60}, 800);
        },
        hideMessage: function (block) {
            block.hide();
        },
        renderPriceField: function (reservationList, sendMainForm) {
            var price = 0,
                    range = 0,
                    sendMainForm = sendMainForm || false,
                    statusTaxFlag = enableTax.is(':checked');
            if (reservationList.length > 0) {
                $.each(reservationList, function () {
                    var start = moment(this.checkIn).valueOf(),
                            end = moment(this.checkOut).valueOf(),
                            rangeS = parseInt(end) - parseInt(start),
                            rangeDays = rangeS / 1000 / 3600 / 24,
                            pricePeriod = 0;

                    if (rangeDays === 0) {
                        rangeDays = 1;
                    } else {
                        rangeDays = Math.ceil(rangeDays);
                    }
                    if (rangeDays < 30) {
                        pricePeriod = rangeDays * parseFloat(this.bed.price);
                    } else {
                        pricePeriod = rangeDays * (parseFloat(this.bed.priceMonth) / 30);
                    }
                    price += pricePeriod;
                });
                var tax = parseFloat(price) * ((airnbnTaxActive === false && statusTaxFlag === true) ? taxKoef : 0.0),
                        total = parseFloat(price) + tax;


                /*Set input value*/
                var currentSumValue = parseFloat(orderField.val());
                if (false !== sendMainForm && currentSumValue !== 0 && currentSumValue !== undefined && currentSumValue !== "" && true !== isNaN(currentSumValue)) {
                    /*Custom sum values*/
                    var custTax = currentSumValue * ((airnbnTaxActive === false && statusTaxFlag === true) ? taxKoef : 0.0),
                            custTotal = currentSumValue + custTax;
                    taxField.val(custTax.toFixed(2));
                    totalSumField.val(custTotal.toFixed(2));
                    invoiceSumHelper.text(currentSumValue.toFixed(2));
                    needToPayField.val(currentSumValue.toFixed(2));
                    needToPayTotalField.val(custTotal.toFixed(2));
                    var createInvoiceVal = orderCreateInvoiceSelect.val();
                    if (createInvoiceVal === "whole_sum") {
                        invoiceCustomSumField.val(currentSumValue.toFixed(2));
                    }
                    if (createInvoiceVal === "10_prepayment") {
                        var procent = currentSumValue * 0.1;
                        invoiceCustomSumField.val(procent.toFixed(2));
                    }
                    mainForm.data("totalprice", currentSumValue);
                    mainForm.data("taxprice", tax);
                    mainForm.data("totalpricesum", total);
                } else {
                    mainForm.data("totalprice", price);
                    mainForm.data("taxprice", custTax);
                    mainForm.data("totalpricesum", custTotal);

                    orderField.val(price.toFixed(2));
                    taxField.val(tax.toFixed(2));
                    totalSumField.val(total.toFixed(2));
                    invoiceSumHelper.text(price.toFixed(2));
                    
                    needToPayField.val(price.toFixed(2));
                    needToPayTotalField.val(total.toFixed(2));                    
                    var createInvoiceVal = orderCreateInvoiceSelect.val();
                    if (createInvoiceVal === "whole_sum") {
                        invoiceCustomSumField.val(price.toFixed(2));
                    }
                    if (createInvoiceVal === "10_prepayment") {
                        var procent = price * 0.1;
                        invoiceCustomSumField.val(procent.toFixed(2));
                    }
                }


            }

        },
        setEventNewSelectReservation: function (numberReservation) {
            var me = this,
                    locationSelect = $('#reservation_location' + numberReservation),
                    roomSelect = $('#reservation_room' + numberReservation),
                    roomSelectLoader = roomSelect.parent('div').find('.ajax-loader-select'),
                    bedSelect = $('#reservation_bed' + numberReservation),
                    bedSelectLoader = bedSelect.parent('div').find('.ajax-loader-select'),
                    checkIn = $('#reservation_check_in' + numberReservation),
                    checkOut = $('#reservation_check_out' + numberReservation);
            /*Location select*/
            locationSelect.on('change', function () {
                var locationId = this.value;
                roomSelectLoader.show();
                $.ajax({
                    type: "POST",
                    url: '/order/getroom',
                    data: {
                        location: locationId
                    },
                    success: function (data, status) {
                        if (status === "success") {
                            if (data.status === 'success') {
                                roomSelectLoader.hide();
                                me.updateRoomSelect(data.rooms, bedSelectLoader, roomSelect, bedSelect);
                            } else {
                                window.location.href = data.redirect_error;
                            }
                        }
                    },
                    dataType: 'JSON'
                });

            });
            /*Room select*/
            roomSelect.on('change', function () {
                var roomId = this.value;
                bedSelectLoader.show();
                $.ajax({
                    type: "POST",
                    url: '/order/getbed',
                    data: {
                        room: roomId
                    },
                    success: function (data, status) {
                        if (status === "success") {
                            if (data.status === 'success') {
                                bedSelectLoader.hide();
                                me.updateBedSelect(data.beds, bedSelect);
                            }
                        } else {
                            window.location.href = data.redirect_error;
                        }
                    },
                    dataType: 'JSON'
                });

            });
            checkIn.datetimepicker({
                pickTime: false,
                language: 'en',
                format: 'YYYY-MM-DD'
            });
            checkOut.datetimepicker({
                pickTime: false,
                language: 'en',
                format: 'YYYY-MM-DD'
            });
        },
        getReservationList: function () {
            var reservationItem = $('.reservation-item-wrapper'),
                    resultMessage = "success";
            reservationList = [];
            if (reservationItem.length > 0) {
                reservationItem.each(function (index) {
                    var el = $(this),
                            number = el.data("reservation-number"),
                            idReservation = el.data("reservation-id"),
                            initStartReservation = el.data("reservation-start"),
                            initEndReservation = el.data("reservation-end"),
                            locationId = el.find('#reservation_location' + number).val(),
                            roomId = el.find('#reservation_room' + number).val(),
                            bedId = el.find('#reservation_bed' + number).val(),
                            bedPrice = el.find('#reservation_bed' + number + ' option:selected').data("price"),
                            bedPriceMonth = el.find('#reservation_bed' + number + ' option:selected').data("price-month"),
                            checkIn = el.find('#reservation_check_in' + number).val(),
                            checkOut = el.find('#reservation_check_out' + number).val(),
                            errorMessage = "";
                    if (locationId === "") {
                        errorMessage = "Select location";
                    }
                    if (roomId === "") {
                        errorMessage = "Select room";
                    }
                    if (bedId === "") {
                        errorMessage = "Select bed";
                    }
                    if (checkIn === "") {
                        errorMessage = "Select Check In";
                    }
                    if (checkOut === "") {
                        errorMessage = "Select Check Out";
                    }
                    if (checkIn !== "" && checkOut !== "") {
                        var startDate = moment(checkIn),
                                endDate = moment(checkOut),
                                diff = endDate.diff(startDate);
                        if (diff < 0) {
                            errorMessage = "Input Check Out >= Check In";
                        }
                    }
                    if (errorMessage === "") {
                        var reservationData = {
                            reservationNumber: number,
                            reservationId: (idReservation !== undefined && idReservation !== "" ? idReservation : null),
                            initDateRange: {
                                start: (initStartReservation !== undefined && initStartReservation !== "" ? initStartReservation : ""),
                                end: (initEndReservation !== undefined && initEndReservation !== "" ? initEndReservation : "")
                            },
                            location: {
                                id: locationId
                            },
                            room: {
                                id: roomId
                            },
                            bed: {
                                id: bedId,
                                price: bedPrice,
                                priceMonth: bedPriceMonth
                            },
                            checkIn: checkIn,
                            checkOut: checkOut
                        };
                        reservationList[index] = reservationData;
                    } else {
                        resultMessage = "Reservation #" + number + ". " + errorMessage;
                    }

                });
            }
            return {
                list: reservationList,
                message: resultMessage
            };
        },
        /**
         * 
         * @param {type} reservations
         * @returns {undefined}
         */
        checkIntersectionReservation: function (reservations) {
            var result = {
                state: true,
                message: "Ok"
            };
            var reservationCount = reservations.length,
                    notIntersection = true,
                    me = this;
            if (reservationCount > 1) {
                var tempReservationList = reservations.slice();
                for (var k = 0; k < reservationCount; k++) {
                    var reservationItem = reservations[k];
                    for (var i = tempReservationList.length - 1; i >= 0; i--) {
                        if (reservationItem.reservationNumber !== tempReservationList[i].reservationNumber && reservationItem.room.id === tempReservationList[i].room.id && reservationItem.location.id === tempReservationList[i].location.id && reservationItem.bed.id === tempReservationList[i].bed.id) {
                            /*Check dates*/
                            var reservationStart = moment(reservationItem.checkIn),
                                    reservationEnd = moment(reservationItem.checkOut),
                                    reservationStartTemp = moment(tempReservationList[i].checkIn),
                                    reservationEndTemp = moment(tempReservationList[i].checkOut);
                            var rangeDateReservation = me.getRangeDate(reservationStart, reservationEnd),
                                    rangeDateReservationTemp = me.getRangeDate(reservationStartTemp, reservationEndTemp);
                            for (var j = 0; j < rangeDateReservation.length; j++) {
                                var isIntersection = me.inArray(rangeDateReservation[j], rangeDateReservationTemp);
                                if (true === isIntersection) {
                                    notIntersection = false;
                                    result.message = "Exist intersection reservation " + reservationItem.reservationNumber + " and reservation " + tempReservationList[i].reservationNumber;
                                    break;
                                }
                            }
                            if (false === notIntersection) {
                                break;
                            }
                        }
                    }
                    if (true !== notIntersection) {
                        break;
                    }

                }
            }
            result.state = notIntersection;
            return result;
        },
        /**
         * Get range duration
         * 
         * @param {momemt} startDate
         * @param {moment} endDate
         * @returns {array}
         */
        getRangeDate: function (startDate, endDate) {
            var rangeDate = [],
                    countShowDay = endDate.diff(startDate, "days"),
                    nextDate;
            if (countShowDay > 0) {
                rangeDate[0] = startDate.format('YYYY-MM-DD');
                for (var i = 1; i <= countShowDay; i++) {
                    nextDate = startDate.add(1, "days");
                    rangeDate[i] = nextDate.format('YYYY-MM-DD');
                }
            }

            return rangeDate;
        },
        /**
         * 
         * @param {type} needle
         * @param {type} haystack
         * @param {type} strict
         * @returns {Boolean}
         */
        inArray: function (needle, haystack, strict) {
            var found = false, key, strict = !!strict;
            for (key in haystack) {
                if ((strict && haystack[key] === needle) || (!strict && haystack[key] === needle)) {
                    found = true;
                    break;
                }
            }

            return found;
        },
        /**
         * 
         * @param {type} loaderBlock
         * @param {type} windowHeight
         * @returns {undefined}
         */
        showPreloader: function (loaderBlock, windowHeight) {
            loaderBlock.height(windowHeight);
            loaderBlock.show();
        },
        /**
         * 
         * @param {type} loaderBlock
         * @returns {undefined}
         */
        hidePreloader: function (loaderBlock) {
            loaderBlock.hide();
        }
    };

    //Init data
    /*Change reservation select*/
    orderInterface.setEventNewSelectReservation(1);
    /* ./Change reservation select*/
    orderInterface.init();    
    var client = $('#client').select2();
    client.on("change", function (event) {
        var option = client.find('option[value="' + event.val + '"]');
        if (option.length > 0) {
            var name = option.data("name"),
                    email = option.data("email"),
                    socialLink = option.data("social"),
                    phone = option.data("phone");
            clientName.val(name);
            clientEmail.val(email);
            clientSocial.val(socialLink);
            clientPhone.val(phone);           
        } else {
            clientName.val("");
            clientEmail.val("");
            clientSocial.val("");
            clientPhone.val("");           
        }
    });


    $('#client_name').on('keyup', function () {
        if(orderDetailForm.length === 0){
            $("#client").select2("val", "");
        }
    });



    /* Add reservation to list*/
    $('#add_new_reservation').on('click', function () {
        var locationSelectItem = '';
        $.each(selectLocationList, function () {
            locationSelectItem += '<option value="' + this.value + '">' + this.text + '</option>';
        });
        var renderNewReservation = '<div class="reservation-item-wrapper" data-reservation-number="' + nextReservationNumber + '">'
                + '<h4 class="reservation-header">Reservation #' + nextReservationNumber + '<button id="remove_reservation_' + nextReservationNumber + '" data-reservation="' + nextReservationNumber + '" class="btn btn-danger btn-xs pull-right">Remove</button></h4>'
                + '<div class="form-group required">'
                + '<label for="reservation_location' + nextReservationNumber + '" class="col-lg-2 control-label">Location</label>'
                + '<div class="col-lg-10">'
                + '<select class="form-control" id="reservation_location' + nextReservationNumber + '">'
                + locationSelectItem
                + '</select>'
                + '</div>'
                + '</div>'
                + '<div class="form-group required">'
                + '<label for="reservation_room' + nextReservationNumber + '" class="col-lg-2 control-label">Room</label>'
                + '<div class="col-lg-10">'
                + '<select class="form-control" id="reservation_room' + nextReservationNumber + '" >'
                + '<option value="">Select after location</option>'
                + '</select>'
                + '<div class="ajax-loader-select"><img src="/assets/images/ajax-loader_select2.gif"></div>'
                + '</div>'
                + '</div>'
                + '<div class="form-group required">'
                + '<label for="reservation_bed' + nextReservationNumber + '" class="col-lg-2 control-label">Bed</label>'
                + '<div class="col-lg-10">'
                + '<select class="form-control" id="reservation_bed' + nextReservationNumber + '">'
                + '<option value="">Select after room</option>'
                + '</select>'
                + '<div class="ajax-loader-select"><img src="/assets/images/ajax-loader_select2.gif"></div>'
                + '</div>'
                + '</div>'
                + '<div class="form-group required">'
                + '<label for="reservation_check_in' + nextReservationNumber + '" class="col-lg-2 control-label">Check In</label>'
                + '<div class="col-lg-10">'
                + '<input class="form-control" id="reservation_check_in' + nextReservationNumber + '" placeholder="Check In" type="text" value="">'
                + '</div>'
                + '</div>'
                + '<div class="form-group required">'
                + '<label for="reservation_check_out' + nextReservationNumber + '" class="col-lg-2 control-label">Check Out</label>'
                + '<div class="col-lg-10">'
                + '<input class="form-control" id="reservation_check_out' + nextReservationNumber + '" placeholder="Check Out" type="text" value="">'
                + '</div>'
                + '</div>'
                + '</div>';
        orderReservationBlock.append(renderNewReservation);
        /*Set event for new select*/
        $('#remove_reservation_' + nextReservationNumber).on("click", function () {
            var el = $(this),
                    wrapper = el.parents('div.reservation-item-wrapper');
            wrapper.empty();
            wrapper.remove();
            mainForm.data("reservation_checked", false);
            return false;
        });
        orderInterface.setEventNewSelectReservation(nextReservationNumber);
        nextReservationNumber++;

        return false;
    });
    /* ./Add reservation to list*/

    /*Submit main form*/
    mainForm.on('submit', function () {
        var clientSelectId = $('#client').select2("val"),
                numberOfPeople = $('#order_number_of_people').val(),
                paymentStatus = $('#order_payment_status').val(),
                paymentStatusText = $('order_payment_status option:selected').text(),
                loaderBlock = $('.order-form-loader'),
                windowHeight = $(window).height(),
                typeInvoice = orderCreateInvoiceSelect.val(),
                errorMessage = "";
        /* If new client */
        if (clientSelectId === "" && clientName === "") {
            errorMessage = "Select client from list or create new";
        }

        /*Show preloader*/
        orderInterface.showPreloader(loaderBlock, windowHeight);

        /* Check and get reservation list*/
        var reservations = orderInterface.getReservationList(),
                reservationCheckedSuccess = mainForm.data("reservation_checked");

        if (reservations.message !== "success") {
            errorMessage = reservations.message;
            /*Show reservation error*/
            orderInterface.showMessage(reservationMessageBlock, errorMessage);
            orderInterface.hidePreloader(loaderBlock);
            return false;
        } else {
            /*Check reservation list client*/
            var notIntersection = orderInterface.checkIntersectionReservation(reservations.list);
            if (true !== notIntersection.state) {
                /*Show reservation error*/
                orderInterface.showMessage(reservationMessageBlock, notIntersection.message + ", correct date ranges");
                orderInterface.hidePreloader(loaderBlock);
                return false;
            }

            /* Check and get reservation list server*/
            if (reservationCheckedSuccess === false || reservationCheckedSuccess === undefined) {
                $.ajax({
                    type: "POST",
                    url: '/reservation/order/checkdates',
                    data: {
                        reservation: JSON.stringify(reservations.list)
                    },
                    success: function (data, status) {
                        if (status === "success") {
                            if (data.status === 'success') {
                                if (true === data.dates_free) {
                                    mainForm.data("reservation_checked", true);
                                    mainForm.submit();
                                } else {
                                    var messageReservationNotFree = 'Reservation number ',
                                            firstNumber = true;
                                    $.each(data.list_status, function () {
                                        if (this.free === false) {
                                            if (true === firstNumber) {
                                                messageReservationNotFree += this.reservation_number;
                                                firstNumber = false;
                                            } else {
                                                messageReservationNotFree += ", " + this.reservation_number;
                                            }
                                        }
                                    });
                                    messageReservationNotFree += ' not free, check date range';
                                    orderInterface.showMessage(reservationMessageBlock, messageReservationNotFree);
                                    orderInterface.hidePreloader(loaderBlock);
                                    mainForm.data("reservation_checked", false);
                                }
                            } else {
                                 window.location.href = data.redirect_error;
                                 return false;
                            }
                        } else {
                            orderInterface.hidePreloader(loaderBlock);
                        }

                    },
                    dataType: 'JSON'
                });
                return false;
            }

            /*Get total price*/
            orderInterface.renderPriceField(reservations.list, true);
            orderInterface.hideMessage(reservationMessageBlock);

            if (typeInvoice === "any_sum") {
                var anySumVal = invoiceCustomSumField.val();
                if (anySumVal === "" || anySumVal === undefined) {
                    errorMessage = "Set Invoice Sum.";
                } else {
                    var orderSum = parseFloat(orderField.val()),
                            anySum = parseFloat(anySumVal);
                    if (anySum > orderSum) {
                        errorMessage = "Invoice Sum must <= Order Sum";
                    }
                }
            }


            if (errorMessage !== "") {
                orderInterface.showMessage(orderMessageBlock, errorMessage);
                orderInterface.hidePreloader(loaderBlock);
                return false;
            } else {
                var reservations = JSON.stringify(reservations.list);
                $('#reservation_list').val(reservations);
                $('#reservation_total').val(mainForm.data("totalprice"));
                taxField.attr("disabled", false);
                totalSumField.attr("disabled", false);
                needToPayField.attr("disabled", false);
                needToPayTotalField.attr("disabled", false);
                $('#order_payment_status').attr("disabled", false);
                
                /*Check ajax send type*/
                var typeSend = mainForm.data("type-send");
                if(typeSend === "ajax"){                                       
                    $.ajax({
                        type: "POST",
                        url: '/reservation/order/create-ajax',
                        data: mainForm.serialize(),
                        success: function (data, status) {                                                    
                            if (status === "success") {
                                if (data.success === 1) {
                                    orderInterface.showMessage(orderMessageBlock, data.message, "success");
                                } else {
                                     orderInterface.showMessage(orderMessageBlock, data.message);
                                }
                            } else {
                                orderInterface.hidePreloader(loaderBlock);
                            }
                            
                            $('#create_reservation_popup_form').modal('hide');
                            orderInterface.hidePreloader(loaderBlock);
                            $('#calendar_reservation').roomCalendar('renderItems');
                        },
                        dataType: 'JSON'
                    });                   
                    return false;
                }
            }
        }
    });
    /* ./Submit main form*/

    /* Prepare submit invoice form*/
    var invoiceSumField = $('#invoice_sum'),
            invoiceTaxField = $('#invoice_tax'),
            invoiceTotalSumField = $('#invoice_total_sum'),
            invoiceWrapperBlock = $('.add-new-invoice-wrapper'),
            helpBlockInvoiceSum = $('.help-block-invoice-sum');

    $('#add_new_invoice').on('click', function () {
        var needToPay = needToPayField.val(),
                tax = parseFloat(needToPay) * (airnbnTaxActive === false ? taxKoef : 0.0),
                summa = parseFloat(needToPay),
                total = tax + summa;
        invoiceSumField.val(summa.toFixed(2));
        invoiceTaxField.val(tax.toFixed(2));
        invoiceTotalSumField.val(total.toFixed(2));
        invoiceWrapperBlock.show();
        $('html, body').animate({scrollTop: invoiceWrapperBlock.offset().top}, 800);
    });
    /* Submit invoice form*/
    $('#invoice_form').submit(function () {
        invoiceSumField.attr('disabled', false);
        invoiceTaxField.attr('disabled', false);
        needToPayField.attr('disabled', false);
        needToPayTotalField.attr('disabled', false);
        invoiceTotalSumField.attr('disabled', false);
    });

    /*Select menu create invoice from order create*/
    orderCreateInvoiceSelect.on('change', function () {
        orderInterface.renderInvoicePaymentMethod();
    });

    $('#order_invoice_type').on('change', function () {
        var val = this.value,
                needToPay = parseFloat(needToPayField.val());
        switch (val) {
            case 'remaining_sum':
                invoiceSumField.attr("disabled", true);
                helpBlockInvoiceSum.hide(500);
                var needToPay = needToPayField.val(),
                        tax = parseFloat(needToPay) * (airnbnTaxActive === false ? taxKoef : 0.0),
                        summa = parseFloat(needToPay),
                        total = tax + summa;
                invoiceSumField.val(summa.toFixed(2));
                invoiceTaxField.val(tax.toFixed(2));
                invoiceTotalSumField.val(total.toFixed(2));
                break;
            case 'any_sum':
                invoiceSumField.attr("disabled", false);
                invoiceSumHelper.text(needToPay.toFixed(2));
                helpBlockInvoiceSum.show();
                break;
        }
    });
    invoiceSumField.on('keyup', function () {
        var val = parseFloat(this.value),
                needToPay = parseFloat(needToPayField.val());

        if (false === isNaN(val)) {
            if (val > needToPay) {
                val = needToPay;
                this.value = needToPay;
            }
            var tax = val * (airnbnTaxActive === false ? taxKoef : 0.0),
                    total = val + tax;
            invoiceTaxField.val(tax.toFixed(2));
            invoiceTotalSumField.val(total.toFixed(2));
        } else {
            invoiceTaxField.val(0.0);
            invoiceTotalSumField.val(0.0);
        }

    });

    /*Selet payment method*/
    orderPaymentMethod.on('change', function () {
        orderInterface.renderCheckBoxSendEmail();
        orderInterface.airbnbPaymentMethod();
    });

    orderField.on('keyup', function () {
        var sum = parseFloat(this.value),
                tax = sum * (airnbnTaxActive === false ? taxKoef : 0.0),
                total = sum + tax;
        if (false === isNaN(sum)) {
            taxField.val(tax.toFixed(2));
            totalSumField.val(total.toFixed(2));
            invoiceCustomSumField.val(sum);
            needToPayField.val(sum.toFixed(2));
            needToPayTotalField.val(total.toFixed(2));
        } else {
            taxField.val(0);
            totalSumField.val(0);
            invoiceCustomSumField.val(0);
             needToPayField.val(0);
            needToPayTotalField.val(0);
        }
    });


});
