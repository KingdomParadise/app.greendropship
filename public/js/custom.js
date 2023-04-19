var ajax_link = '/ajax'
let pathname = window.location.pathname;

$(document).ready(function () {
    //Page Name
    $('#pageName').html($('.indexContent').data('page_name'))

    //Left Menu
    $('.row-menu ul li').removeClass('active')
    $('.row-menu ul li[data-name="' + $('.indexContent').data('page_name') + '"]').addClass('active')

    /* DASHBOARD */
    $('.checklist-item input').click(function () {
        if ($(this).prop('checked')) {
            var value = 1
        } else {
            var value = 0
        }

        var parameters = {
            action: 'add_check',
            id_user: $('#inputId').val(),
            step: $(this).data('id'),
            value: value
        }

        $.getJSON(ajax_link, parameters, function (data) {})
    })

    /* SEARCH PAGE */
    $('.btn_import_list').click(function () {
        var parameters = {
            action: 'add_import_list',
            id_product: $(this).data('id')
        }

        $.getJSON(ajax_link, parameters, function (data) {
            $('.id_' + JSON.parse(data) + ' button.add').hide()
            $('.id_' + JSON.parse(data) + ' button.edit').show()
        })
    })

    /* PRODUCT DETAIL */
    $('.imgThumb').mouseover(function () {
        $('.maingreenproducimage')[0].src = $(this)[0].src.replace('dc09e1c71e492175f875827bcbf6a37c', 'e793809b0880f758cc547e70c93ae203');
        $('.maingreenproducimagelink')[0].href = $(this)[0].parentElement.href;
    })

    /* IMPORT LIST */
    $('.import-tab').click(function () {
        var id_product = $(this)
            .parent()
            .parent()
            .data('id')
        var tabName = $(this).data('name')
        $('#product' + id_product)
            .find('.import-tab')
            .removeClass('active')
        $('#product' + id_product)
            .find('.import-tab label')
            .css('color', '#000000')
        $('#product' + id_product)
            .find('.import-content')
            .hide()
        $(this).addClass('active')
        $(this)
            .find('label')
            .css('color', '#89B73D')
        $('#product' + id_product)
            .find('.import-' + tabName)
            .show()
    })

    /* MY PRODUCTS */
    $('.mp-table-view button').click(function () {
        var id_product = $(this).data('id')
        $('.mp-product-detail').hide()
        $('.row' + id_product)
            .find('.mp-product-detail')
            .show()
    })

    $('.buttonDisabled').mouseover(function () {
        $('.answerBD' + $(this).data('id')).show()
    })

    $('.buttonDisabled').mouseout(function () {
        $('.answerBD' + $(this).data('id')).hide()
    })

    /* ADMIN ORDER DETAIL */
    $('#btnNotes').click(function (e) {
        var texto = $(`textarea.ta${e.target.dataset.id}`).val()
        var parameters = {
            action: 'update_notes',
            id_order: e.target.dataset.id,
            notes: texto
        }
        $.getJSON(ajax_link, parameters, function (data) {
            $(`textarea.ta${e.target.dataset.id}`).val(data.notes);
            $('#success-note').removeClass('d-none');
            setTimeout(() => {
                $('#success-note').addClass('d-none');
            }, 2000);
        })
    })

    /* ADMIN USERS */
    $('.account').click(function () {
        if ($('.items').css('display') == 'none') {
            $('.items').show();
        } else {
            $('.items').hide();
        }
    });

    $('.fa-eye').click(function (event) {
        $(this).toggleClass('fa-eye fa-eye-slash');
        let input = $($(this).data("id"));
        if (input.attr("type") == "password") {
            input.attr("type", "text");
        } else {
            input.attr("type", "password");
        }
    })

    $('#fail-close, #fail-confirm').click(function() {
        $('#product-fail').removeClass('show');
        setTimeout(() => {
            $('#product-fail').css('display', 'none');
            $('#fade-background').remove();
        }, 150);
    });

    $('#view-plan').click(function() {
        window.location.href = '/plans#planBottom';
    });
    
    function passProgress(value) {
        let flag = false;
         if (value.indexOf(' ') > -1) {
            $('#password-error').text('Whitespace is not allowed.');
            $('#password-error').show();
        } else {
            if (value.length < 6) {
                if (value.length == 0) {
                    $('#password-error').text(empty_msg);
                    $('#password-error').show();
                } else {
                    $('#password-error').text(pass_msg);
                    $('#password-error').show();
                }
            } else {
                let array = [];
                array.push(value.match(/[A-Z]/));
                array.push(value.match(/[a-z]/));
                array.push(value.match(/\d/));
                array.push(value.match(/[!#@$&_.-]/));
                let sum = 0;
                array.forEach(element => {
                    sum += element ? 1 : 0;
                });
                if (sum == 4) {
                    flag = true;
                    $('#password-error').hide();
                }
                else {
                    $('#password-error').text(pass_msg);
                    $('#password-error').show();
                }
            }
        }
        return flag;
    }

    var empty_msg = '*Please fill out this field.';
    var pass_msg = '*more than 6 characters, lowercase, uppercase, number, symbol';
    $('#btn-save-user').click(function (event) {
        //validation
        var flag = true;
        var reg = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9]))\.){3}(?:(2(5[0-5]|[0-4][0-9])|1[0-9][0-9]|[1-9]?[0-9])|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/;
        var name = $('#txt-user-name').val().trim();
        var email = $('#txt-email').val().trim();
        var password = $('#txt-password').val();
        var confirm_password = $('#txt-confirm-password').val();
        if (name.length < 3) {
            flag = false;
            if (name.length == 0) {
                $('#name-error').text(empty_msg);
            } else {
                $('#name-error').text(`*Plase lengthen this text to 3 characters or more (you are currently using ${name.length} characters).`);
            }
            $('#name-error').show();
        } else {
            $('#name-error').hide();
        }

        if (!email.match(reg)) {
            flag = false;
            if (email.length == 0) {
                $('#email-error').text(empty_msg);
            } else {
                $('#email-error').text('*Please input valid email.');
            }
            $('#email-error').show();
        } else {
            $('#email-error').hide();
        }

        flag = passProgress(password);

        if (confirm_password.length == 0) {
            flag = false;
            $('#confirm-error').text(empty_msg);
            $('#confirm-error').show();
        } else {
            if ($('#txt-password').val() != $('#txt-confirm-password').val()) {
                flag = false;
                $('#confirm-error').text("*Confirm Password doesn't match");
                $('#confirm-error').show();
            } else {
                $('#confirm-error').hide();
            }
        }
        
        if (flag) {
            action = 'create-user';
            if (window.location.pathname == '/admin/profile') {
                action = 'update-user';
            }
            var parameters = {
                action: action,
                name: name,
                email: email.match(reg)[0],
                password: password,
            }
            $.getJSON(ajax_link, parameters, function (data) {
                $('#txt-password').val('');
                $('#txt-confirm-password').val('');
                if (data.id) {
                    $('#user_data').append(`<tr class="userdatarow">
                        <td data-label="USER NAME">
                            ${data.name}
                        </td>
                        <td data-label="EMAIL">
                            ${data.email}
                        </td>
                        <td data-label="ACTIVE">
                            <input type="checkbox" name="switch-button" id="switch-label${data.id}" class="switch-button__checkbox" checked>
                        </td>
                        <td>
                            <a href="/admin/merchants/show/${data.id}"><button class="view greenbutton">View</button></a>
                        </td>
                    </tr>`)
                    $('#txt-user-name').val('');
                    $('#txt-email').val('');
                    $('#success-user').show();
                    setTimeout(() => {
                        $('#success-user').hide();
                    }, 3000);
                } else if (data.result) {
                    $('#success-user').show();
                    setTimeout(() => {
                        $('#success-user').hide();
                    }, 3000);
                } else {
                    $('#fail-user').show();
                    setTimeout(() => {
                        $('#fail-user').hide();
                    }, 3000);
                }
            })
        }
    });

    /* ADMIN PASSWORD */
    $('#btn-save-password').click(function (event) {
        var flag = true;
        var old_password = $('#old-password').val();
        var new_password = $('#new-password').val();
        var confirm_password = $('#confirm-password').val();
        
        if (old_password.length == 0) {
            flag = false;
            $('#old-error').text(empty_msg);
            $('#old-error').show();
        } else {
            $('#old-error').hide();
        }

        flag = passProgress(new_password);

        if (confirm_password.length == 0) {
            flag = false;
            $('#confirm-error').text(empty_msg);
            $('#confirm-error').show();
        } else {
            if ($('#new-password').val() != $('#confirm-password').val()) {
                flag = false;
                $('#confirm-error').text("*Confirm Password doesn't match");
                $('#confirm-error').show();
            } else {
                $('#confirm-error').hide();
            }
        }

        if (flag) {
            if (old_password == new_password) {
                $('#password-error').text('New password is same as current password.');
                $('#password-error').show();
            } else {
                $('#password-error').hide();
                var parameters = {
                    action: 'admin-change-password',
                    old_password: JSON.stringify(old_password),
                    new_password: JSON.stringify(new_password)
                }
                $.getJSON(ajax_link, parameters, function (data) {
                    if (data.result) {
                        $('#old-password').val('');
                        $('#new-password').val('');
                        $('#confirm-password').val('');
                        $('#success-password').show();
                        setTimeout(() => {
                            $('#success-password').hide();
                        }, 3000);
                    } else {
                        $('#fail-password').show();
                        setTimeout(() => {
                            $('#fail-password').hide();
                        }, 3000);
                    }
                })
            }
        }
    });

    $('#mainlogo').click(function () {
        if($('#role').val() == 'admin') {
            window.location.href = '/admin/dashboard';
        } else {
            window.location.href = '/';
        }
    });

    $('.fa-close').click(function(e) {
        e.target.parentElement.remove();
    });

    function getParams() {
        let parameters = {
            action: getAction(),
            page_size: 10,
            page_number: 1
        }
        return parameters;
    }

    if (pathname == '/my-products' || pathname == '/import-list' || pathname == '/merge-inventory' || pathname == '/admin/orders' || pathname == '/admin/merchants' || pathname == '/admin/users'|| pathname == '/orders') {
        $('#pagination').html(`<div class="pagination">
            <ul class="pagination" role="navigation">
                <button class="page-item cel-icon-angle-double-left" id="first_page" disabled></button>
                <button class="page-item cel-icon-angle-left" id="prev" disabled></button>
                <li class="page-item active" id="pages" aria-current="page">0</li>
                <button class="page-item cel-icon-angle-right" id="next" disabled></button>
                <button class="page-item cel-icon-angle-double-right" id="last_page" disabled></button>
            </ul>
        </div>`);
        let params = getParams();
        getData(params);
    }

    $('#page_size').change(function (event) {
        uncheckAllProducts();
        var parameters = {
            action: getAction(),
            page_size: event.target.value,
            page_number: 1
        }
        getData(parameters);
    })

    $('#first_page').click(function () {
        uncheckAllProducts();
        var total_count = $('#total_count').text();
        var page_size = $('#page_size').val();
        var page_number = 1;
        var parameters = {
            action: getAction(),
            page_size: page_size,
            page_number: page_number
        }
        if (total_count > page_size * page_number) {
            getData(parameters);
        }
    })

    $('#prev').click(function () {
        uncheckAllProducts();
        var parameters = {
            action: getAction(),
            page_size: $('#page_size').val(),
            page_number: $('.page_number.selected').text() - 1
        }
        if ($('.page_number.selected').text() > 1) {
            getData(parameters);
        }
    })

    $('#next').click(function () {
        uncheckAllProducts();
        var total_count = $('#total_count').text();
        var page_size = $('#page_size').val();
        var page_number = $('.page_number.selected').text();
        var parameters = {
            action: getAction(),
            page_size: page_size,
            page_number: page_number * 1 + 1
        }
        if (total_count > page_size * page_number) {
            getData(parameters);
        }
    })

    $('#last_page').click(function () {
        uncheckAllProducts();
        var parameters = {
            action: getAction(),
            page_size: $('#page_size').val(),
            page_number: Math.ceil($('#total_count').text() / $('#page_size').val())
        }
        if (Math.ceil($('#total_count').text() / $('#page_size').val()) > 1) {
            getData(parameters);
        }
    })

    $('.migration').click(function () {
        $('body').append('<div class="modal-backdrop fade show"></div>');
        $('#migrate-products-modal').css('display', 'block');
        setTimeout(() => {
            $('#migrate-products-modal').addClass('show');
        }, 150);
        $('#migration-body').html('<progress id="migrating-progress" max="100" value="0" style="width:100%;">0%</progress><p id="percentage"></p>');
        $.getJSON(ajax_link, {action: 'migration-count'}, function (res) {
            if (res.error) {
                $('#migration-body').html(`<h5 style="line-height:1.5" class="my-0">${res.error}</h5>`);
                setTimeout(() => {
                    $('#migrate-products-modal').removeClass('show');
                    $('.bodyFront').removeClass('modal-open');
                    $('.bodyFront').css('padding-right', 0);
                }, 4000);
                setTimeout(() => {
                    $('#migrate-products-modal').css('display', 'none');
                    $('.modal-backdrop.fade.show').remove();
                }, 4150);
            } else {
                bringProducts(res);
            }
        })
    })

    $('.admin-order-reset').click(function() {
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#idOrder').val('');
        $('#merchant').val('');
        $('#paymentstatus').val('');
        $('#orderstate').val('');
        getOrderData();
    });

    $("#search").click(function(e) {
        var flag = adminOrderSearchPermission();
        if (flag) {
            getOrderData();
        }
    });

    $('#merchant_name').keydown(function(e) {
        let length = e.target.value.length;
        let merchant_name = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                merchant_name += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    merchant_name = merchant_name.slice(0, -1);
                }
            }
            if (length > 2 && (e.key.length == 1 || e.key == 'Backspace')) {
                $.getJSON(ajax_link, {
                    action: 'admin-merchant-name',
                    name: merchant_name
                }, function(res) {
                    var str = '<div id="name_data">';
                    res.names.forEach(name => {
                        str += `<option value="${name}">`;
                    });
                    str += '</div>';
                    $('#name_data').remove();
                    $('#names').html(str);
                })
            } else {
                $('#name_data').remove();
            }
        }
    });

    $('#merchant_email').keydown(function(e) {
        let length = e.target.value.length;
        let merchant_email = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                merchant_email += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    merchant_email = merchant_email.slice(0, -1);
                }
            }
            if (length > 2 && (e.key.length == 1 || e.key == 'Backspace')) {
                $.getJSON(ajax_link, {
                    action: 'admin-merchant-email',
                    email: merchant_email
                }, function(res) {
                    var str = '<div id="email_data">';
                    res.emails.forEach(email => {
                        str += `<option value="${email}">`;
                    });
                    str += '</div>';
                    $('#email_data').remove();
                    $('#emails').html(str);
                })
            } else {
                $('#email_data').remove();
            }
        }
    });
    
    $('#merchant_url').keydown(function(e) {
        let length = e.target.value.length;
        let merchant_url = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                merchant_url += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    merchant_url = merchant_url.slice(0, -1);
                }
            }
            if (length > 2 && (e.key.length == 1 || e.key == 'Backspace')) {
                $.getJSON(ajax_link, {
                    action: 'admin-merchant-url',
                    url: merchant_url
                }, function(res) {
                    var str = '<div id="url_data">';
                    res.urls.forEach(url => {
                        str += `<option value="${url}">`;
                    });
                    str += '</div>';
                    $('#url_data').remove();
                    $('#urls').html(str);
                })
            } else {
                $('#url_data').remove();
            }
        }
    });
    
    $('#merchant_name').change(function(e) {
        getMerchantData();
    })
    
    $('#merchant_email').change(function(e) {
        getMerchantData();
    })
    
    $('#merchant_url').change(function(e) {
        getMerchantData();
    })
    
    $('#merchant_plan').change(function(e) {
        getMerchantData();
    })
    
    $('#merchant_active').change(function(e) {
        getMerchantData();
    })

    $('.merchant-reset').click(function() {
        $('#merchant_name').val('');
        $('#merchant_email').val('');
        $('#merchant_url').val('');
        $('#merchant_plan').val('');
        $('#merchant_active').val('');
        getMerchantData();
    });

    $('#user_name').keydown(function(e) {
        let length = e.target.value.length;
        let name = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                name += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    name = name.slice(0, -1);
                }
            }
            if (length > 2 && (e.key.length == 1 || e.key == 'Backspace')) {
                $.getJSON(ajax_link, {
                    action: 'admin-user-name',
                    name: name
                }, function(res) {
                    var str = '<div id="name_data">';
                    res.names.forEach(name => {
                        str += `<option value="${name}">`;
                    });
                    str += '</div>';
                    $('#name_data').remove();
                    $('#names').html(str);
                })
            } else {
                $('#name_data').remove();
            }
        }
    });

    $('#user_email').keydown(function(e) {
        let length = e.target.value.length;
        let user_email = e.target.value;
        if (e.key) {
            if (e.key.length == 1) {
                length += 1;
                user_email += e.key;
            } else {
                if (e.code == 'Backspace') {
                    length -= 1;
                    user_email = user_email.slice(0, -1);
                }
            }
            if (length > 2 && (e.key.length == 1 || e.key == 'Backspace')) {
                $.getJSON(ajax_link, {
                    action: 'admin-user-email',
                    email: user_email
                }, function(res) {
                    var str = '<div id="email_data">';
                    res.emails.forEach(email => {
                        str += `<option value="${email}">`;
                    });
                    str += '</div>';
                    $('#email_data').remove();
                    $('#emails').html(str);
                })
            } else {
                $('#email_data').remove();
            }
        }
    });

    $('#user_name').change(function(e) {
        if ($('#user_name').val().length == 0) {
            $('name_data').remove();
        }
        getMerchantData();
    });

    $('#user_email').change(function(e) {
        if ($('#user_email').val().length == 0) {
            $('email_data').remove();
        }
        getMerchantData();
    });

    $('#user_active').change(function(e) {
        getMerchantData();
    });

    $('.order-reset').click(function() {
        $('#date_from').val('');
        $('#date_to').val('');
        $('#order_id').val('');
        $('#payment_status').val('0');
        $('#order_state').val('0');
        getUserOrderData();
    });

    $('.user-reset').click(function() {
        $('#user_name').val('');
        $('#user_email').val('');
        $('#user_active').val('');
        getMerchantData();
    });

    $(".btn-order-search").click(function() {
        var flag = orderSearchPermission();
        if (flag) {
           getUserOrderData(); 
        }
    });

    $('#merge-close').click(function() {
        $('#migrate-products-modal').removeClass('show');
        $('.bodyFront').removeClass('modal-open');
        $('.bodyFront').css('padding-right', 0);
        setTimeout(() => {
            $('#migrate-products-modal').css('display', 'none');
            $('.modal-backdrop.fade.show').remove();
        }, 150);
    })

    $(window).scroll(function () {
        if ($(this).scrollTop() > 1000) {
            $('.back-to-top').fadeIn();
        } else {
            $('.back-to-top').fadeOut();
        }
    });
    // scroll body to 0px on click
    $('.back-to-top').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 1000);
        return false;
    });

    window.addEventListener('resize', function () {
        if (pathname == '/search-products' || pathname == '/amdin/orders') {
            if (window.outerWidth > 768) {
                document.querySelector('#loading').style.right = `${document.querySelector('.maincontent').scrollWidth / 2}px`;
            } else {
                document.querySelector('#loading').style.right = `${document.querySelector('.maincontent').scrollWidth / 2}px`;
            }
        }
        if (pathname == '/introduction') {
            if (document.documentElement.offsetWidth < 640) {
                $('#products-page-size').val(2);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            } else if (document.documentElement.offsetWidth < 900) {
                $('#products-page-size').val(3);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            } else if (document.documentElement.offsetWidth < 1024) {
                $('#products-page-size').val(4);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            } else if (document.documentElement.offsetWidth < 1360) {
                $('#products-page-size').val(3);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            } else if (document.documentElement.offsetWidth < 1680) {
                $('#products-page-size').val(4);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            } else {
                $('#products-page-size').val(5);
                $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
                if (!is_loading) {
                    $('.product-item').remove();
                    $('.right-arrow').hide();
                    $('.left-arrow').hide();
                    introductionProducts();
                }
            }
        }
    })

    if (pathname == '/introduction') {
        setLoading();
        if (document.documentElement.offsetWidth < 640) {
            $('#products-page-size').val(2);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        } else if (document.documentElement.offsetWidth < 900) {
            $('#products-page-size').val(3);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        } else if (document.documentElement.offsetWidth < 1024) {
            $('#products-page-size').val(4);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        } else if (document.documentElement.offsetWidth < 1360) {
            $('#products-page-size').val(3);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        } else if (document.documentElement.offsetWidth < 1680) {
            $('#products-page-size').val(4);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        } else {
            $('#products-page-size').val(5);
            $('.introduction-products').css('grid-template-columns', 'minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr) minmax(220px, 1fr)');
            introductionProducts();
        }
        document.addEventListener('scroll', function(e) {
            if (e.target.scrollingElement.scrollHeight - e.target.scrollingElement.clientHeight - e.target.scrollingElement.scrollTop < 1) {
                if (!is_loading) {
                    introductionAllProducts($('#all-products-page-number').val());
                }
            }
        })
    }

    if (pathname == '/new-products') {
        $('.introduction-products').css('grid-template-columns', 'repeat(auto-fill, minmax(220px, 1fr))');
        $('.introduction-products').css('background-color', 'transparent');
        setLoading();
        newProducts(0);
        document.addEventListener('scroll', function(e) {
            if (e.target.scrollingElement.scrollHeight - e.target.scrollingElement.clientHeight - e.target.scrollingElement.scrollTop < 1) {
                if (!is_loading) {
                    newProducts($('#products-page-number').val());
                }
            }
        })
    }

    if (pathname == '/discount-products') {
        $('.introduction-products').css('grid-template-columns', 'repeat(auto-fill, minmax(220px, 1fr))');
        $('.introduction-products').css('background-color', 'transparent');
        setLoading();
        discountProducts(0);
        document.addEventListener('scroll', function(e) {
            if (e.target.scrollingElement.scrollHeight - e.target.scrollingElement.clientHeight - e.target.scrollingElement.scrollTop < 1) {
                if (!is_loading) {
                    discountProducts($('#products-page-number').val());
                }
            }
        })
    }
})

function getAction() {
    var action = '';
    if (pathname == '/my-products') {
        action = 'my-products';
    }
    if (pathname == '/import-list') {
        action = 'import-list';
    }
    if (pathname == '/merge-inventory') {
        action = 'migrate-products';
    }
    if (pathname == '/admin/orders') {
        action = 'admin-orders';
    }
    if (pathname == '/admin/merchants') {
        action = 'admin-merchants';
    }
    if (pathname == '/admin/users') {
        action = 'admin-users';
    }
    if (pathname == '/orders') {
        action = 'my-orders';
    }
    return action;
}

function orderSearchPermission() {
    var from = $('#date_from').val();
    var to = $('#date_to').val();
    var flag = true;
    if (from != '' && to != '') {
        if (moment(from).isAfter(moment(to).format('YYYY-MM-DD'))) {
            flag = false;
            $('#confirm-modal-body').html(`<h5 class="my-0">Invalid date range</h5>`);
            $('#cancel').hide();
            $('#confirm').text('Confirm');
            $('.btn-order-search').attr('data-toggle', 'modal');
            $('.btn-order-search').attr('data-target', '#confirm-modal');
        } else {
            $('.btn-order-search').attr('data-toggle', '');
        }
    } else {
        $('.btn-order-search').attr('data-toggle', '');
    }
    return flag;
}

function getUserOrderData () {
    var urlParams = new URLSearchParams(window.location.search);
    var notifications = urlParams.getAll('notifications');
    var parameters = {
        action: getAction(),
        page_size: $('#page_size').val(),
        page_number: 1,
        from: $('#date_from').val(),
        to: $('#date_to').val(),
        order_number: $('#order_id').val().trim(),
        payment_status: $('#payment_status').val(),
        order_state: $('#order_state').val(),
        notifications: notifications[0]
    }
    $.getJSON(ajax_link, parameters, function(res) {
        pagination(res);
        showMyOrders(res.my_orders);
    });
}

function showMyOrders (data) {
    if (data.orders.length) {
        $('.no-order').hide();
        $('.order-content').show();
        let str = '';
        if (data.from && data.to) {
            $('#period').text(`${data.from} - ${data.to}`);
        } else {
            $('#period').text(data.basic_period);
        }

        $('#total_period_orders').text(data.total_period_orders);
        if (data.total_period_orders >= data.limit_orders) {
            $('#total_period_orders').css('background-color', 'red');
            $('#total_period_orders').css('color', 'white');
            $('#total_period_orders').css('border-radius', '50%');
            if (data.total_period_orders < 10) {
                $('#total_period_orders').css('padding', '5px 8px');
            } else {
                $('#total_period_orders').css('padding', '5px 3px');
            }
        }
        $('#total_orders').text(data.total_count);
        data.orders.forEach(order => {
            let button_str = '';
            if (order.financial_status == 1 && order.fulfillment_status != 9 && order.fulfillment_status != 12) {
                button_str = `<button class="payorder pay-button checkout-button" data-id="${order.id}">PAY ORDER</button>`;
            } else if (order.fulfillment_status == 9) {
                button_str = `<div class="text-center"><span style="color: #929292">Canceled</span></div>`;
            } else {
                button_str = `<div class="text-center"><span class="font-weight-bold" style="color: #1A6E33">Paid</span></div>`;
            }

            str += `<tr class="productdatarow">
                <td data-label="ORDER #">
                    ${order.order_number_shopify}
                </td>
                <td data-label="DATE">
                    ${order.created_at}
                </td>
                <td data-label="CUSTOMER NAME">
                    ${order.first_name} ${order.last_name}
                </td>
                <td data-label="TOTAL TO PAY">
                    $${parseFloat(order.total + order.shipping_price).toFixed(2)}
                </td>
                <td data-label="PAYMENT STATUS">
                    ${order.status1}
                </td>
                <td data-label="ORDER STATE">
                    ${order.status2}
                </td>
                <td>
                    ${button_str}
                </td>
                <td>
                    <a href="/orders/${order.id_shopify}" target="_blank"><button class="view greenbutton">View</button></a>
                </td>
            </tr>`;
        });
        $('.productdatarow').remove();
        if (data.is_notification && !data.notifications) {
            $('.orders').html(`<div class="alertan level2">
                <div class="agrid">
                    <p><strong>No orders pending payment. Good job!</strong></p>
                </div>
            </div>`);
        } else {
            $('#order_data').html(str);
            setTimeout(() => {
                window.scrollTo(0,0);
            }, 500);
        }
        $('#notifications').text(data.notifications);
        if ($('#notifications').text() > 9) {
            $('#notifications').addClass('circle');
        } else {
            $('#notifications').removeClass('circle');
        }

    } else {
        $('.order-content').hide();
        $('.no-order').show();
    }
}

function getMerchantData() {
    var parameters = {
        action: getAction(),
        page_number: 1,
        page_size: $('#page_size').val()
    }
    getData(parameters);
}

function showUsers(users) {
    var str = '';
    users.forEach(user => {
        str += `<tr class="userdatarow">
            <td data-label="USER NAME">
                ${user.name}
            </td>
            <td data-label="EMAIL">
                ${user.email}
            </td>
            <td data-label="ACTIVE">
                <input type="checkbox" name="switch-button" id="switch-label${user.id}" data-userid="${user.id}" data-toggle="modal" data-target="#confirm-modal" class="switch-button__checkbox change-status" ${user.active && 'checked'}>
            </td>
            <td class="d-flex">
                <button class="view mx-2 greenbutton admin-user-view" data-userid="${user.id}">View</button>
            </td>
        </tr>`;
    });
    $('.userdatarow').remove();
    $('#user_data').html(str);
    setTimeout(() => {
        window.scrollTo(0,0);
    }, 500);
}

function getOrderData() {
    var parameters = {
        action: getAction(),
        page_size: $('#page_size').val(),
        page_number: 1,
        from: $('#dateFrom').val(),
        to: $('#dateTo').val(),
        order_number: $('#idOrder').val().trim(),
        merchant_name: $('#merchant').val().trim(),
        payment_status: $('#paymentstatus').val(),
        order_state: $('#orderstate').val()
    }
    $.getJSON(ajax_link, parameters, function(res) {
        pagination(res);
        showAdminOrders(res.order_list);
        $('#loading').hide();
    });
}

function adminOrderSearchPermission() {
    var from = $('#dateFrom').val();
    var to = $('#dateTo').val();
    var flag = true;
    if (from != '' && to != '') {
        if (moment(from).isAfter(moment(to).format('YYYY-MM-DD'))) {
            flag = false;
            $('#confirm-modal-body').html(`<h5 class="my-0">Invalid date range</h5>`);
            $('#cancel').hide();
            $('#confirm').text('Confirm');
            $('#search').attr('data-toggle', 'modal');
            $('#search').attr('data-target', '#confirm-modal');
        } else {
            $('#search').attr('data-toggle', '');
        }
    } else {
        $('#search').attr('data-toggle', '');
    }
    return flag;
}

function showAdminOrders (orders) {
    var str = '';
    orders.order_list.forEach(order => {
        str += `<tr class="orderrow">
            <td data-label="SHOPIFY ORDER ID">${order.id_shopify ? order.id_shopify : ''}</td>
            <td data-label="CUSTOMER ORDER NUMBER">${order.order_number_shopify ? order.order_number_shopify : ''}</td>
            <td data-label="GDS ORDER NUMBER">${order.magento_order_id ? order.magento_order_id : ''}</td>
            <td data-label="DATE">${order.created_at} ${orders.timezone}</td>
            <td data-label="TOTAL TO PAY" class="nowrap">US $${parseFloat(order.total).toFixed(2)}</td>
            <td data-label="MERCHANT">${order.merchant_name}</td>
            <td data-label="PAYMENT STATUS"><label class="buttonge" style="background-color: ${order.color1}">${order.status1}</label></td>
            <td data-label="ORDER STATE"><label class="buttonge nowrap" style="background-color: ${order.color2}">${order.status2}</label></td>
            <td><a href="/admin/orders/${order.id_shopify}" target="_blank"><button class="view greenbutton">View</button></a></td>
        </tr>`;
    });
    $('.orderrow').remove();
    $('#order_data').html(str);
    setTimeout(() => {
        window.scrollTo(0,0);
    }, 500);
}

function showMerchants (merchants) {
    var str = '';
    merchants.forEach(merchant => {
        str += `<tr class="merchantrow">
            <td data-label="MERCHANT NAME">
                ${merchant.name}
            </td>
            <td data-label="EMAIL">
                ${merchant.email}
            </td>
            <td data-label="SHOPIFY URL">
                ${merchant.shopify_url ? merchant.shopify_url : '' }
            </td>
            <td data-label="PLAN">
                ${merchant.plan ? merchant.plan : ''}
            </td>
            <td data-label="ACTIVE">
                <input type="checkbox" name="switch-button" id="switch-label${merchant.id}" data-merchantid="${merchant.id}" data-toggle="modal" data-target="#confirm-modal" class="switch-button__checkbox change-status" ${merchant.active && 'checked'}>
            </td>
            <td>
                <div class="btngroup">
                    <button class="view greenbutton detail-merchants mx-2" data-merchantid="${merchant.id}">View</button>
                    <button class="vieworder mx-2 ${merchant.order_count ? 'orders-customers' : 'simple-tooltip'}" title="${merchant.order_count ? '' : 'No Orders'}" data-merchantid="${merchant.id}">Orders</button>
                </div>
            </td>
        </tr>`;
    });
    $('.merchantrow').remove();
    $('#merchant_data').html(str);
    Tipped.create('.simple-tooltip');
    setTimeout(() => {
        window.scrollTo(0,0);
    }, 500);
}

function bringProducts (data) {
    $.getJSON(ajax_link, data, function (res) {
        if (res.mig_products && res.mig_products.length == 0) {
            $('#migration-body').html('<h5 style="line-height:1.5" class="my-0">There is no product to merge. <br> You can only merge products you previously imported into your store with our data feeds instead of the app.</h5>');
            setTimeout(() => {
                $('#migrate-products-modal').removeClass('show');
                $('.bodyFront').removeClass('modal-open');
                $('.bodyFront').css('padding-right', 0);
            }, 4000);
            setTimeout(() => {
                $('#migrate-products-modal').css('display', 'none');
                $('.modal-backdrop.fade.show').remove();
            }, 4150);
        } else {
            var params = {
                action: 'migration',
                index: res.index,
                location_id: res.location_id,
                Total_count: res.Total_count,
                count: res.count,
            }
            $('#migrating-progress').val(res.count/res.Total_count*100);
            $('#percentage').text(`${parseInt(res.count/res.Total_count*100)}%`);
            $('#percentage').addClass('text-center h5');
            if (res.count == res.Total_count) {
                $('#pagination').show();
                pagination(res);
                showMigrateProducts(res.mig_products);
                setTimeout(() => {
                    $('#migrate-products-modal').removeClass('show');
                    $('.bodyFront').removeClass('modal-open');
                    $('.bodyFront').css('padding-right', 0);
                }, 1000);
                setTimeout(() => {
                    $('#migrate-products-modal').css('display', 'none');
                    $('.modal-backdrop.fade.show').remove();
                }, 1150);
            } else {
                bringProducts(params);
            }
        }
    })
}

function showMigrateProducts (data) {
    $('.productdatarow').remove();
    var str = migrateProducts(data);
    if (data.length) {
        $('.btn-migration').hide();
        $('#product-top-menu').show();
        $('#migration-top-text p').text('You can edit the profit margins for these products and update the price in your Shopify store.');
        $('#migration-table').show();
        $('#product_data').html(str);
        Tipped.create('.simple-tooltip');
        setTimeout(() => {
            window.scrollTo(0,0);
        }, 500);
    } else {
        $('#migration-top-text p').text('If you previously imported products using our data feeds, you can merge those products into the GreenDropShip App here.');
        $('#product-top-menu').hide();
        $('#migration-top-tet').hide();
        $('#migration-table').hide();
        $('.btn-migration').show();
    }
    $('#migration-top-text').show();
}

function migrateProducts (products) {
    var str = '';
    products.forEach(product => {
        var payload = JSON.parse(product.payload);
        var button_str = '', profit_str = '', price_str = '';
        if (product.type == 'migration'){
            button_str = `<button class="btn-confirm-product greenbutton mx-0" data-toggle="modal" data-target="#confirm-modal" data-id="${product.id_shopify}" id="confirm-${product.id_shopify}">Update price</button>
                <button class="greenbutton mx-0" id="confirmed-${product.id_shopify}" style="display: none;">Updated</button>
                <button class="btn-mp-delete redbutton" data-toggle="modal" data-target="#confirm-modal" id="delete-${product.id_shopify}" data-id="${product.id_shopify}" style="display: none;">Delete</button>
                <img src="/img/loading_1.gif" id="loading-${product.id_shopify}" style="display:none; height: 50px;">`;
            profit_str = `<div id="profit">
                <input type="number" min="0" class="box-profit text-center border" id="profit-${product.id_shopify}" data-id="${product.id_shopify}" data-sku="${product.sku}" value="${product.cost ? Math.round((product.price - product.cost) / product.cost * 100) : ''}">
                %</div>`;
            price_str = `<div id="price" class="nowrap">
                US $<input type="number" min="0" class="box-price text-left border" id="price-${product.id_shopify}" data-id="${product.id_shopify}" data-sku="${product.sku}" value="${parseFloat(product.price).toFixed(2)}">
                </div>`;
        } else {
            button_str = `<button class="btn-mp-delete redbutton" data-toggle="modal" data-target="#confirm-modal" id="delete-${product.id_shopify}" data-id="${product.id_shopify}">Delete</button>
                <img src="/img/loading_1.gif" id="loading-${product.id_shopify}" style="display:none; height: 50px;">`;
            profit_str = `<div id="profit">
                <span>${product.cost ? Math.round((product.price - product.cost) / product.cost * 100) : ''}</span>
                %</div>`;
            price_str = `<span id="price-${product.id_shopify}" class="nowrap">US $${parseFloat(product.price).toFixed(2)}</span>`;
        }

        str += `<tr class="productdatarow">
            <td class="check">
                <input type="checkbox" id="check-${ product.id_shopify }" data-id="${ product.id_shopify }" value="" class="checkbox">
            </td>
            <td class="pimage">
                <div class="productphoto">
                    <img src="${payload.image_url}">
                </div>
            </td>
            <td data-label="PRODUCT NAME" class="product-name text-left">
                <span>${ payload.name }</span>
            </td>
            <td data-label="COST GDS">
                <span id="cost-${product.id_shopify}" class="nowrap">US $${product.cost ? parseFloat(product.cost).toFixed(2) : ''}</span>
            </td>
            <td data-label="PROFIT">
                ${profit_str}
            </td>
            <td data-label="RETAIL PRICE">
                ${price_str}
            </td>
            <td data-label="SKU">
                ${product.sku}
            </td>
            <td id="action">
                ${button_str}
            </td>
        </tr>`;
    });
    return str;
}

function getData(parameters) {
    var flag = true;
    var action = getAction();
    if (action == 'admin-orders') {
        flag = adminOrderSearchPermission();
        if (flag) {
            var urlParams = new URLSearchParams(window.location.search);
            var merchantid = urlParams.getAll('merchantid');
            Object.assign(parameters, {
                from: $('#dateFrom').val(),
                to: $('#dateTo').val(),
                order_number: $('#idOrder').val().trim(),
                merchant_name: $('#merchant').val().trim(),
                payment_status: $('#paymentstatus').val(),
                order_state: $('#orderstate').val(),
                id_customer: merchantid[0] 
            });
        }
    }
    if (action == 'my-orders') {
        flag = orderSearchPermission();
        if (flag) {
            var urlParams = new URLSearchParams(window.location.search);
            var notifications = urlParams.getAll('notifications');
            Object.assign(parameters, {
                from: $('#date_from').val(),
                to: $('#date_to').val(),
                order_number: $('#order_id').val().trim(),
                payment_status: $('#payment_status').val(),
                order_state: $('#order_state').val(),
                notifications: notifications[0]
            });
        }
    }
    if (action == 'admin-merchants') {
        Object.assign(parameters, {
            name: $('#merchant_name').val(),
            email: $('#merchant_email').val(),
            url: $('#merchant_url').val(),
            plan: $('#merchant_plan').val(),
            active: $('#merchant_active').val()
        })
    }
    if (action == 'admin-users') {
        Object.assign(parameters, {
            name: $('#user_name').val(),
            email: $('#user_email').val(),
            active: $('#user_active').val()
        })
    }
    if (flag) {
        $.getJSON(ajax_link, parameters, function (res) {
            pagination(res);
            if (res.improds) {
                showImportProducts(res.improds);
            } else if(res.prods) {
                showMyProducts(res.prods);
            } else if(res.mig_products) {
                showMigrateProducts(res.mig_products);
            } else if(res.order_list) {
                showAdminOrders(res.order_list);
            } else if(res.merchants) {
                showMerchants(res.merchants);
            } else if(res.users) {
                showUsers(res.users);
            } else if(res.my_orders) {
                showMyOrders(res.my_orders);
            }
        })
    }
}

function pagination (data) {
    if (data.page_number == '1') {
        $('#prev').prop('disabled', true);
        $('#first_page').prop('disabled', true);
    } else {
        $('#prev').prop('disabled', false);
        $('#first_page').prop('disabled', false);
    }
    if (data.page_number * data.page_size >= data.total_count) {
        $('#next').prop('disabled', true);
        $('#last_page').prop('disabled', true);
    } else {
        $('#next').prop('disabled', false);
        $('#last_page').prop('disabled', false);
    }
    let str = '';
    if (Math.ceil(data.total_count / data.page_size) != 0) {
        if (Math.ceil(data.total_count / data.page_size) == 1) {
            $('#pagination').hide();
        } else {
            let start_page = parseInt(data.page_number) - 2;
            let end_page = parseInt(data.page_number) + 2;
            if (start_page <= 0) {
                start_page = 1;
                end_page = 5;
            }
            if (Math.ceil(data.total_count / data.page_size) <= end_page) {
                end_page = Math.ceil(data.total_count / data.page_size);
                start_page = end_page - 4
                if (start_page <= 0) {
                    start_page = 1;
                }
            }
            for(let i = start_page; i <= end_page; i++) {
                if (i == data.page_number) {
                    str += `<span class="page_number selected">${i}</span>`;
                } else {
                    str += `<span class="page_number" onclick="pageNumberClick(${i})">${i}</span>`;
                }
            }
            $('#pagination').show();
        }
    } else {
        $('#pagination').hide();
    }
    $('#pages').html(str);
    $('#total_count').text(data.total_count);
}

function showMyProducts (products) {
    $('.productdatarow').remove();
    $('.shoproductrow').remove();
    var str = '';
    products.forEach(product => {
        str += `<tr class="productdatarow">
            <td class="check">
                <input type="checkbox" id="check-${product.id_shopify}" data-id="${product.id_my_products}" value="" class="checkbox">
            </td>
            <td class="pimage">
                <div class="productphoto">
                    <img src="${product.image_url_75}">
                </div>
            </td>
            <td data-label="PRODUCT NAME" class="product-name text-left">
                <a href="search-products/${product.sku}" target="_blank" id="name-${product.id_shopify}">${product.name }</a>
            </td>
            <td data-label="COST GDS" class="nowrap">
                US $${parseFloat(product.price).toFixed(2)}
            </td>
            <td data-label="PROFIT">
                ${product.profit ? product.profit : 0}%
            </td>
            <td data-label="RETAIL PRICE" class="nowrap">
                US $${parseFloat(product.price * (100 + product.profit) / 100).toFixed(2)}
            </td>
            <td data-label="SKU">
                ${product.sku}
            </td>
            <td class="action-buttons">
                <button class="btn-mp-view my-1 mx-1 viewbutton vplist" data-id="${product.id}" id="view-${product.id_shopify}" data-view="#product${product.id}">View</button>
                <button class="btn-mp-delete my-1 mx-1 deletebutton redbutton" data-toggle="modal" data-target="#confirm-modal" id="delete-${product.id_shopify}" data-myproductid="${product.id_shopify}"  data-name="${product.name}" data-sku="${product.sku}" data-img="${product.image_url_75}">Delete</button>
                <span id="deleted-msg-${product.id_shopify}" class="text-secondary h5 mb-0" style="display:none; cursor: text">Deleted Product</span>
                <img src="/img/loading_1.gif" id="deleting-${product.id_shopify}" style="display:none;">
            </td>
        </tr>
        <tr class="shoproductrow" id="product${product.id}">
            <td></td>
            <td colspan="7">
                <div class="productlisthow">
                    <div class="productimage">
                        <img src="${product.image_url_285}">
                    </div>
                    <div class="productdata">
                        <h3>${product.name}</h3>
                        <p class="price"><strong>Price:</strong> US $${parseFloat(product.price * (100 + product.profit) / 100).toFixed(2)}</p>
                        <p>
                            <strong>Stock:</strong> ${product.stock ? product.stock : '<span class="text-danger">OUT OF STOCK</span>'}
                        </p>
                        <p>
                            <strong>Cost:</strong> US $${parseFloat(product.price).toFixed(2)}
                        </p>
                        <p>
                            <strong>Profit:</strong> ${product.profit ? product.profit : 0}%
                        </p>
                        <p>
                            <strong>Brand:</strong> ${product.brand}
                        </p>

                        <div class="pbuttons">
                            <button class="edit edit-product" id="edit-${product.id_shopify}" data-shopifyid="${product.id_shopify}">Edit in Store</button>
                        </div>
                    </div>
                </div>
            </td>
        </tr>`
    });
    
    if (products.length) {
        $('#product_data').html(str)
        setTimeout(() => {
            window.scrollTo(0,0);
        }, 500);
    } else {
        $('#product-top-menu').hide();
        $('.greentable.my-products').hide();
        $('#pagination').hide();
        $('.empty-product').show();
    }
}

function showImportProducts (data) {
    $('.empty-product').hide();
    $('.productboxelement').remove()
    var str = '';
    data.products.forEach(product => {
        var image_str = '';
        var button_str = '';
        if (data.plan == null) {
            button_str += `<button data-toggle="modal" data-target="#membership-modal" class="delete" id="delete-${product.id_import_list}" data-id="${product.id_import_list}">Delete</button>
                <button data-toggle="modal" data-target="#membership-modal" class="sendto greenbutton btn-import-list-send-${product.id_import_list}" data-id="${product.id_import_list}">Add to Store</button>`;
        } else if (data.plan == 'free') {
            button_str += `<button data-toggle="modal" data-target="#upgrade-plans-modal" class="delete" id="delete-${product.id_import_list}" data-id="${product.id_import_list}">Delete</button>
                <button data-toggle="modal" data-target="#upgrade-plans-modal" class="sendto greenbutton btn-import-list-send-${product.id_import_list}" data-id="${product.id_import_list}">Add to Store</button>`;
        } else {
            button_str += `<button class="delete btn-import-list-delete" id="delete-${product.id_import_list}" data-id="${product.id_import_list}" data-name="${product.name}" data-sku="${product.sku}" data-img="${product.delete_image_url}">Delete</button>
                <button class="sendto greenbutton btn-import-list-send btn-import-list-send-${product.id_import_list}" data-id="${product.id_import_list}" data-name="${product.name}" data-sku="${product.sku}" data-img="${product.delete_image_url}">Add to Store</button>
                <img src="/img/loading_1.gif" class="import-list-loading-${product.id_import_list}" style="display:none; ">
                <span id="sent-msg-${product.id_import_list}" class="text-secondary h5 mb-0" style="display:none; cursor: text">Product Added to Store</span>
                <button class="sendto edit-in-shopify btn-import-list-sent-${product.id_import_list}" data-shopifyid="0">Edit in Store</button>`;
        }
        product.images.forEach((image, i) => {
            image_str += `<div class="selectimage">
                <div class="imagewrap">
                    <img class="img${product.id_import_list}-${i}" src="${image}">
                </div>
                <div class="checkim">
                    <input type="checkbox" class="chk-img${product.id_import_list}" data-index="${i}" value="" checked="checked">
                </div>
            </div>`;
        });
        var collection_str = `<div id="collection_data${product.id_import_list}">`;
        product.collections.forEach(collection => {
            collection_str += `<option value="${collection}">`;
        });
        collection_str += '</div>';
        var type_str = `<div id="type_data${product.id_import_list}">`;
        product.types.forEach(type => {
            type_str += `<option value="${type}">`;
        });
        type_str += '</div>';
        var tag_str = '';
        product.tags.split(',').forEach(tag => {
            tag_str += `<input type="checkbox" class="w-auto h-auto h6 m-0 px-1" /><span class="h6 m-0 px-1">${tag}</span>`;
        })
        str += `<div class="productboxelement import-product" id="product${product.id_import_list}" data-id="${product.id_import_list}">
            <div class="producttabs">
                <div class="headertabs">
                    <div class="checkt">
                        <input type="checkbox" id="check-${product.id_import_list}" class="checkbox" style="display: block; width: 20px; height: 20px;">
                    </div>
                    <div class="tabs">
                        <a href=".tab-1" class="thetab active"> Product </a>
                        <a href=".tab-2" class="thetab"> Description </a>
                        <a href=".tab-3" class="thetab"> Pricing </a>
                        <a href=".tab-4" class="thetab"> Images </a>
                    </div>
                    <div class="buttons import-actions">
                        ${button_str}
                    </div>
                </div>
                <div class="contenttabs">
                    <div class="tab-1 wpadding tabcontent active">
                        <h3 class="py-2 productname">${product.name}</h3>
                        <div class="productgrid">
                            <div>
                                <div class="imagewrap">
                                    <img src='${product.image_url}'>
                                </div>
                            </div>
                            <div>
                                <div class="editform">
                                    <div class="full">
                                        <label for="">Change product name</label>
                                        <input type="text" id="name${product.id_import_list}" value="${product.name}">
                                    </div>
                                    <div>
                                        <label for="">Collection <span class="simple-tooltip" title="You can assign the product to a Collection in your Shopify store.">?</span></label>
                                        <input type="text" list="collection${product.id_import_list}" id="collections${product.id_import_list}" class="collection" data-id="${product.id_import_list}" value="${product.collection}">
                                        <datalist id="collection${product.id_import_list}">
                                            ${collection_str}
                                        </datalist>
                                        <span id="collection_error${product.id_import_list}" style="color:red; display:none;">One product can have only one collection.</span>
                                    </div>
                                    <div>
                                        <label for="">Type <span class="simple-tooltip" title="You can give this product a classification that will be saved in the 'Product Type' field in Shopify.">?</span></label>
                                        <input type="text" list="type${product.id_import_list}" id="types${product.id_import_list}" class="type" data-id="${product.id_import_list}" value="${product.type}">
                                        <datalist id="type${product.id_import_list}">
                                            ${type_str}
                                        </datalist>
                                        <span id="type_error${product.id_import_list}" style="color:red; display:none;">One product can have only one type.</span>
                                    </div>
                                    <div class="full">
                                        <label>Tags <span class="simple-tooltip" title="You can create your own tags separated by commas.">?</span></label>
                                        <div class="d-flex align-items-center flex-wrap py-1 defaulttags">
                                            ${tag_str}
                                        </div>
                                        <div id="tags">
                                            <input type="text" list="tag${product.id_import_list}" id="tags${product.id_import_list}" data-id="${product.id_import_list}">
                                            <datalist id="tag${product.id_import_list}">
                                                <div id="tag_data${product.id_import_list}"></div>
                                            </datalist>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-2 tabcontent wpadding import-content import-description">
                        <textarea class="texteditor editor" name="" id="description${product.id_import_list}" cols="30" rows="10">${product.description}</textarea>
                    </div>
                    <div class="tab-3 tabcontent wpaddingtop">
                        <table class="greentable" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>
                                        SKU <span class="simple-tooltip" title="Do not change this SKU in your Shopify store.">?</span>
                                    </th>
                                    <th>
                                        HEIGHT
                                    </th>
                                    <th>
                                        WIDTH
                                    </th>
                                    <th>
                                        LENGTH
                                    </th>
                                    <th>
                                        WEIGHT
                                    </th>
                                    <th>
                                        COST
                                    </th>
                                    <th>
                                        PROFIT (%) <span class="simple-tooltip" title="Profit excludes shipping charges.">?</span>
                                    </th>
                                    <th>
                                        PRICE
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="productdatarow">
                                    <td data-label="SKU" class="skutd">
                                        <input type="text" id="sku${product.id_import_list}" data-id="${product.id_import_list}" value="${product.sku}" disabled="disabled">
                                        <input type="hidden" id="upc${product.id_import_list}" value="${product.upc}" />
                                    </td>
                                    <td data-label="HEIGHT">
                                        ${product.ship_height}
                                    </td>
                                    <td data-label="WIDTH">
                                        ${product.ship_width}
                                    </td>
                                    <td data-label="LENGTH">
                                        ${product.ship_length}
                                    </td>
                                    <td data-label="WEIGHT" id="weight${product.id_import_list}">
                                        ${product.weight}
                                    </td>
                                    <td data-label="COST" class="w100">
                                        <div class="nowrap">
                                            US $<span id="cost${product.id_import_list}" data-id="${product.id_import_list}">${parseFloat(product.price).toFixed(2)}</span>
                                        </div>
                                    </td>
                                    <td data-label="PROFIT (%) " class="w100">
                                        <span class="simple-tooltip" title="First tooltip">?</span>
                                        <div class="inpupercent">
                                            <input type="number" min="0" style="width: 60px; text-align:right; padding: 0px 3px;" class="box-profit" id="profit${product.id_import_list}" data-id="${product.id_import_list}" value="${data.profit}">%
                                        </div>
                                    </td>
                                    <td data-label="PRICE" class="w100">
                                        <div class="inputprice nowrap">
                                            US $<input type="number" min="0" style="width: 60px; text-align:left; padding: 0px 3px;" class="box-price" id="price${product.id_import_list}" data-price="${product.price}" data-id="${product.id_import_list}" value="${parseFloat(product.price * (100 + data.profit) / 100).toFixed(2)}">
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-4 wpadding tabcontent">
                        <div class="imagesgrid">
                        ${image_str}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="import-list-delete-banner" id="import-list-delete-banner-${product.id_import_list}">
            <span>"${product.name}" has been removed from import list. <a href="#" class="import-list-undo" data-id="#import-list-delete-banner-${product.id_import_list}" id="${product.id}">Undo</a></span>
            <button type="button" class="close import-delete-banner">&times;</button>
        </div>`;
    })
    if (data.products.length == 0) {
        $('#pagination').remove();
        $('#import-products').remove();
        $('#product-top-menu').remove();
        $('.empty-product').show();
    } else {
        $('#import-products').html(str);
        $(".editor").each(function(index, ele) {
            CKEDITOR.replace($(ele).attr('id'), {});
        });
        Tipped.create('.simple-tooltip');
        setTimeout(() => {
            window.scrollTo(0,0);
        }, 500);
    }
}

function introductionNewProducts(page_number) {
    is_loading = true;
    $.getJSON('/ajax', {
        action: 'introduction-new-products',
        page_number: page_number,
        page_size: $('#products-page-size').val(),
    }, function(res) {
        $('#new-products-page-number').val(page_number);
        let str = '';
        res.new_products.forEach(new_product => {
            let images = JSON.parse(new_product.images);
            let img_str = `<img src="/img/default_image_165.png" class="main-image">`;
            if (images != null) {
                if (images.length > 0) {
                    if (images[0].file != '') {
                        img_str = `<img src="https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4/${images[0].file}" class="main-image">`;
                    }
                }
            }
            str += `<li id="" class="product-item">
                <div class="product-container">
                    <a id="${new_product.sku}" class="product-details" href="search-products/${new_product.sku}" target="_blank">
                        <div class="product-image">
                            ${img_str}
                            <span class="new-sticker">New Item</span>
                        </div>
                        <div class="product-info">
                            <div class="product-title" title="${new_product.name}">
                                <p class="simple-tooltip product-name" title="${new_product.name}">${new_product.name}</p>
                            </div>
                            <div class="product-sku">
                                <strong>SKU:</strong> ${new_product.sku}
                            </div>
                            <div class="product-price">
                                <span>US $${parseFloat(new_product.price).toFixed(2)}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>`;
        });
        $('.introduction-products.new-products').html(str);
        Tipped.create('.simple-tooltip');
        if (page_number == 0) {
            $('#new-products .left-arrow').removeClass('d-none');
            $('#new-products .right-arrow').removeClass('d-none');
            setTimeout(() => {
                removeLoading();
            }, 500);
        }
        is_loading = false;
    })
}

function introductionDiscountProducts(page_number) {
    is_loading = true;
    $.getJSON('/ajax', {
        action: 'introduction-discount-products',
        page_number: page_number,
        page_size: $('#products-page-size').val(),
    }, function(res) {
        $('#discount-products-page-number').val(page_number);
        let str = '';
        let today = new Date();
        let date = `${getEndDate(today.getMonth() + 1)}/${today.getMonth() + 1}/${today.getFullYear()}`;
        res.discount_products.forEach(discount_product => {
            let images = JSON.parse(discount_product.images);
            let img_str = `<img src="/img/default_image_165.png" class="main-image">`;
            if (images != null) {
                if (images.length > 0) {
                    if (images[0].file != '') {
                        img_str = `<img src="https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4/${images[0].file}" class="main-image">`;
                    }
                }
            }
            str += `<li id="" class="product-item">
                <div class="product-container">
                    <a id="${discount_product.sku}" class="product-details" href="search-products/${discount_product.sku}" target="_blank">
                        <div class="product-image">
                            ${img_str}
                            <span class="new-sticker">-${parseFloat(discount_product.discount).toFixed(2)}%</span>
                        </div>
                        <div class="product-info">
                            <div class="product-title" title="${discount_product.name}">
                                <p class="simple-tooltip product-name" title="${discount_product.name}">${discount_product.name}</p>
                            </div>
                            <div class="product-sku">
                                <strong>SKU:</strong> ${discount_product.sku}
                            </div>
                            <div class="product-sku">
                                <span><strong>SRP:</strong> US $${parseFloat(discount_product.suggested_retail).toFixed(2)}</span>
                            </div>
                            <div class="product-sku">
                                <span style="text-decoration: line-through; color: #a2a2a2">US $${parseFloat(discount_product.price).toFixed(2)}</span>
                                <span class="product-price"> US $${discount_product.monthly_special < discount_product.price ? parseFloat(discount_product.monthly_special).toFixed(2) : parseFloat(discount_product.price).toFixed(2)}</span>
                                <span class="simple-tooltip text-white" style="font-size:1rem; padding: 1px 7px;" title="price valid until ${date}"><i class="fa fa-info" aria-hidden="true"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>`;
        });
        $('.introduction-products.discount-products').html(str);
        Tipped.create('.simple-tooltip');
        if (page_number == 0) {
            $('#discount-products .left-arrow').removeClass('d-none');
            $('#discount-products .right-arrow').removeClass('d-none');
            setTimeout(() => {
                removeLoading();
            }, 500);
        }
        is_loading = false;
    })
}

function newProducts(page_number) {
    if (page_number > 0) {
        $('#infinite_loading').remove();
        is_loading = true;
        $('.introduction-content').append(`<div class="d-flex justify-content-center" id="infinite_loading"><img src="/img/infinite_loading.gif"></div>`);
    }
    $.getJSON('/ajax', {
        action: 'new-products',
        page_number: page_number,
        search_key: $('#search-key').val().trim(),
        sort_key: $('#sort-key').val()
    }, function(res) {
        $('#products-page-number').val(parseInt(page_number) + 1);
        let str = '';
        res.new_products.forEach(new_product => {
            let images = JSON.parse(new_product.images);
            let img_str = `<img src="/img/default_image_165.png" class="main-image" style="opacity: ${new_product.stock == 0 ? 0.5 : 1}">`;
            if (images != null) {
                if (images.length > 0) {
                    if (images[0].file != '') {
                        img_str = `<img src="https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4/${images[0].file}" class="main-image" style="opacity: ${new_product.stock == 0 ? 0.5 : 1}">`;
                    }
                }
            }
            let button_str = `<button id="add-${new_product.sku}" data-sku="${new_product.sku}" type="submit" class="add-product cel-icon-plus">Add to Import List</button>`;
            let check_str = `<input type="checkbox" id="check-${new_product.sku}" data-sku="${new_product.sku}" class="check-product">`;
            res.imported_products.forEach(sku => {
                if (new_product.sku == sku) {
                    button_str = `<button id="import-${new_product.sku}" data-sku="${new_product.sku}" class="import-product">Edit in Import List</button>`;
                    check_str = `<input type="checkbox" id="check-${new_product.sku}" disabled>`;
                }
            });
            str += `<li id="" class="product-item py-0">
                ${check_str}
                <div class="product-container">
                    <a id="${new_product.sku}" class="product-details" href="search-products/${new_product.sku}" target="_blank">
                        <div class="product-image">
                            <div>
                                ${img_str}
                                <span class="new-sticker">New Item</span>
                                <span class="out-of-stock ${new_product.stock == 0 ? 'd-block' : 'd-none'}">Out of Stock</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-title" title="${new_product.name}">
                                <p class="simple-tooltip product-name" title="${new_product.name}">${new_product.name}</p>
                            </div>
                            <div class="product-sku">
                                <strong>SKU:</strong> ${new_product.sku}
                            </div>
                            <div class="product-price">
                                <span>US $${parseFloat(new_product.price).toFixed(2)}</span>
                            </div>
                        </div>
                    </a>
                    <div class="product-control">
                        ${button_str}
                    </div>
                </div>
            </li>`;
        });
        Tipped.create('.simple-tooltip');
        setTimeout(() => {
            $('#infinite_loading').remove();
            if (page_number == 0) {
                $('.introduction-products').html(str);
                Tipped.create('.simple-tooltip');
            } else {
                $('.introduction-products').append(str);
                Tipped.create('.simple-tooltip');
            }
            if (res.new_products.length == 60) {
                is_loading = false;
            } else {
                is_loading = true;
            }
        }, 1000);
        if (page_number == 0) {
            setTimeout(() => {
                removeLoading();
            }, 1000);
        }
    })
}

function discountProducts(page_number) {
    if (page_number > 0) {
        $('#infinite_loading').remove();
        is_loading = true;
        $('.introduction-content').append(`<div class="d-flex justify-content-center" id="infinite_loading"><img src="/img/infinite_loading.gif"></div>`);
    }
    $.getJSON('/ajax', {
        action: 'discount-products',
        page_number: page_number,
        search_key: $('#search-key').val().trim(),
        sort_key: $('#sort-key').val()
    }, function(res) {
        $('#products-page-number').val(parseInt(page_number) + 1);
        let str = '';
        res.discount_products.forEach(discount_product => {
            let images = JSON.parse(discount_product.images);
            let img_str = `<img src="/img/default_image_165.png" class="main-image" style="opacity: ${discount_product.stock == 0 ? 0.5 : 1}">`;
            if (images != null) {
                if (images.length > 0) {
                    if (images[0].file != '') {
                        img_str = `<img src="https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4/${images[0].file}" class="main-image" style="opacity: ${discount_product.stock == 0 ? 0.5 : 1}">`;
                    }
                }
            }
            let button_str = `<button id="add-${discount_product.sku}" data-sku="${discount_product.sku}" type="submit" class="add-product cel-icon-plus">Add to Import List</button>`;
            let check_str = `<input type="checkbox" id="check-${discount_product.sku}" data-sku="${discount_product.sku}" class="check-product">`;
            res.imported_products.forEach(sku => {
                if (discount_product.sku == sku) {
                    button_str = `<button id="import-${discount_product.sku}" data-sku="${discount_product.sku}" class="import-product">Edit in Import List</button>`;
                    check_str = `<input type="checkbox" id="check-${discount_product.sku}" disabled>`;
                }
            });
            let today = new Date();
            let date = `${getEndDate(today.getMonth() + 1)}/${today.getMonth() + 1}/${today.getFullYear()}`;
            str += `<li id="" class="product-item py-0">
                ${check_str}
                <div class="product-container">
                    <a id="${discount_product.sku}" class="product-details" href="search-products/${discount_product.sku}" target="_blank">
                        <div class="product-image">
                            <div>
                                ${img_str}
                                <span class="new-sticker">-${parseFloat(discount_product.discount).toFixed(2)}%</span>
                                <span class="out-of-stock ${discount_product.stock == 0 ? 'd-block' : 'd-none'}">Out of Stock</span>
                            </div>
                        </div>
                        <div class="product-info">
                            <div class="product-title" title="${discount_product.name}">
                                <p class="simple-tooltip product-name" title="${discount_product.name}">${discount_product.name}</p>
                            </div>
                            <div class="product-sku">
                                <strong>SKU:</strong> ${discount_product.sku}
                            </div>
                            <div class="product-sku">
                                <span><strong>SRP:</strong> US $${parseFloat(discount_product.suggested_retail).toFixed(2)}</span>
                            </div>
                            <div class="product-sku">
                                <span style="text-decoration: line-through; color: #a2a2a2">US $${parseFloat(discount_product.price).toFixed(2)}</span>
                                <span class="product-price"> US $${discount_product.monthly_special < discount_product.price ? parseFloat(discount_product.monthly_special).toFixed(2) : parseFloat(discount_product.price).toFixed(2)}</span>
                                <span class="simple-tooltip text-white" style="font-size:1rem; padding: 1px 7px;" title="price valid until ${date}"><i class="fa fa-info" aria-hidden="true"></i></span>
                            </div>
                        </div>
                    </a>
                    <div class="product-control">
                        ${button_str}
                    </div>
                </div>
            </li>`;
        });
        setTimeout(() => {
            $('#infinite_loading').remove();
            if (page_number == 0) {
                $('.introduction-products').html(str);
                Tipped.create('.simple-tooltip');
            } else {
                $('.introduction-products').append(str);
                Tipped.create('.simple-tooltip');
            }
            if (res.discount_products.length == 60) {
                is_loading = false;
            } else {
                is_loading = true;
            }
        }, 1000);
        if (page_number == 0) {
            setTimeout(() => {
                removeLoading();
            }, 1000);
        }
    })
}

function introductionAllProducts(page_number) {
    is_loading = true;
    if (page_number > 0) {
        $('#infinite_loading').remove();
        is_loading = true;
        $('#all-products').append(`<div class="d-flex justify-content-center" id="infinite_loading"><img src="/img/infinite_loading.gif"></div>`);
    }
    $.getJSON('/ajax', {
        action: 'introduction-all-products',
        page_number: page_number,
        page_size: $('#products-page-size').val()*2,
    }, function(res) {
        $('#all-products-page-number').val(parseInt(page_number) + 1);
        let str = '';
        res.new_products.forEach(new_product => {
            let images = JSON.parse(new_product.images);
            let img_str = `<img src="/img/default_image_165.png" class="main-image">`;
            if (images != null) {
                if (images.length > 0) {
                    if (images[0].file != '') {
                        img_str = `<img src="https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4/${images[0].file}" class="main-image">`;
                    }
                }
            }
            str += `<li id="" class="product-item">
                <div class="product-container">
                    <a id="${new_product.sku}" class="product-details" href="search-products/${new_product.sku}" target="_blank">
                        <div class="product-image">
                            ${img_str}
                        </div>
                        <div class="product-info">
                            <div class="product-title" title="${new_product.name}">
                                <p class="simple-tooltip product-name" title="${new_product.name}">${new_product.name}</p>
                            </div>
                            <div class="product-sku">
                                <strong>SKU:</strong> ${new_product.sku}
                            </div>
                            <div class="product-price">
                                <span>US $${parseFloat(new_product.price).toFixed(2)}</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>`;
        });
        setTimeout(() => {
            $('#infinite_loading').remove();
            if (page_number == 0) {
                $('.introduction-products.all-products').html(str);
                Tipped.create('.simple-tooltip');
            } else {
                $('.introduction-products.all-products').append(str);
                Tipped.create('.simple-tooltip');
            }
            if (res.new_products.length == $('#products-page-size').val() * 2) {
                is_loading = false;
            } else {
                is_loading = true;
            }
        }, 500);
        if (page_number == 0) {
            setTimeout(() => {
                removeLoading();
            }, 500);
        }
    })
}

function introductionProducts() {
    introductionAllProducts(0);
    introductionNewProducts(0);
    introductionDiscountProducts(0);
}

function getEndDate(month) {
    let today = new Date();
    let year = today.getFullYear();
    if (month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) {
        return 31;
    } else if (month == 2) {
        if (year%4 == 0 && year%400 != 0) {
            return 29;
        } else {
            return 28;
        }
    } else {
        return 30;
    }
}

function pageNumberClick (page_number) {
    var parameters = {
        action: getAction(),
        page_size: $('#page_size').val(),
        page_number: page_number
    }
    getData(parameters);
}

function uncheckAllProducts () {
    $('#check-all-products').prop('checked', false);
    $('#check-all-products').prop('disabled', false);
    $('#select-all').show();
    $('#selected-products').text(0);
    $('#selected-products').hide();
}

function showBulkActionButtons () {
    let count = 0;
    $("input.checkbox:checked").each(function(index, ele) {
        count++;
    });
    $('#selected-products').text(count);
    if ($('#selected-products').text() <= 0) {
        $('#check-all-products').prop('checked', false);
        $('#select-all').css('display', 'block');
        $('#selected-products').css('display', 'none');
    } else {
        if (!$('#check-all-products').is(':disabled')) {
            $('#check-all-products').prop('checked', true);
            if ($('#selected-products').text() < 10) {
                $('#selected-products').css('padding', '0px 10px');
            } else {
                $('#selected-products').css('padding', '0px 5px');
            }
            $('#select-all').css('display', 'none');
            $('#selected-products').css('display', 'block');
        }
    }
}

function popupFailMsg(msg) {
    $('body').append('<div id="fade-background"></div>');
    $('#product-fail-text').html(msg);
    $('#product-fail').css('display', 'block');
    setTimeout(() => {
        $('#product-fail').addClass('show');
    }, 150);
}

function setLoading() {
    let div = document.createElement('div');
    div.className = 'modal-backdrop fade show';
    document.querySelector('.maincontent').append(div);
    document.querySelector('.modal-backdrop').style.zIndex = 1;
    if (window.outerWidth > 768) {
        document.querySelector('#loading').style.right = `${document.querySelector('.maincontent').scrollWidth / 2 - 10}px`;
    } else {
        document.querySelector('#loading').style.right = `${document.querySelector('.maincontent').scrollWidth / 2 - 30}px`;
    }
    document.querySelector('#loading').style.display = 'grid';
}

function removeLoading() {
    if (document.querySelector('.modal-backdrop.fade.show')) {
        document.querySelector('.modal-backdrop.fade.show').remove();
    }
    document.querySelector('#loading').style.display = 'none';
}
