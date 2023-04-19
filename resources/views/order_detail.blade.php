@extends('layouts.app')

@section('custom_css')
<style>
    .font-17{
        font-size: 17px;
    }

    @media screen and (min-width: 1200px) {
        .d-lg-tcell{
            display: table-cell !important;
        }
    }

</style>
@endsection

@section('content')
<div class="indexContent orderDetailContent" data-page_name="ORDER DETAILS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            @if(Auth::user()->plan == 'free')
            <div class="alertan">
                <div class="agrid">
                    <img src="/img/infogray.png" srcset="/img/infogray@2x.png 2x,/img/infogray@3x.png 3x">
                    <p>You have a free plan. <a href="/plans">Click here to upgrade your plan.</a></p>
                </div>
            </div>
            @endif

            @if($order->fulfillment_status == 12)
            <div class="alertan level2">
                <div class="agrid">
                    <p><strong> Orders are submitted to GreenDropShip for processing once the grand total for each transaction is paid, which includes the wholesale price + shipping.</strong></p>
                </div>
            </div>
            @endif

            <div class="screen-order-detail">
                <div class="ordertable">
                    <div class="otawrap">
                        <div class="twocols2">
                            <div class="box">
                                <div class="cwrap">
                                    <h3>Order Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Shopify Order Number</p>
                                        <p><a href="https://{{$shopify_url}}/admin/orders/{{$order->id_shopify}}"
                                              target="_blank">{{ $order->order_number_shopify }}</a></p>
                                        @if($order->magento_order_id)
                                        <p class="font-weight-bold">GDS Order</p>
                                        <p>{{$order->magento_order_id}}</p>
                                        @endif
                                        <p class="font-weight-bold">Date</p>
                                        <p>{{$order->created_at}}</p>
                                        <p class="font-weight-bold">Order Status</p>
                                        <p class="paid" style="background-color: transparent;">{{$os->name}}</p>
                                        <p class="font-weight-bold">Payment Status</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$fs->name}}</p>
                                        @if($order->fulfillment_status == 9)
                                        <p class="font-weight-bold">Canceld By</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$user_canceled}}</p>
                                        <p class="font-weight-bold">Canceled At</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$order->canceled_at}}</p>
                                        @endif
                                        @if($order->shipping_title)
                                        <p class="font-weight-bold">Shipping Method</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$order->shipping_title == null ? 'N/A' : $order->shipping_title}}</p>
                                        @endif
                                        @if($order->tracking_code)
                                        <p class="font-weight-bold">Tracking Number</p>
                                        <p>{{$order->tracking_code}}</p>
                                        @endif
                                        <p class="font-weight-bold btn-link">
                                            <a href="https://greendropship.com/shipping-rates/" target="_blank">
                                                Shipping Information
                                            </a>
                                        </p>
                                    </div>
                                    <h3>Customer Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Name</p>
                                        <p>{{$osa->first_name}} {{$osa->last_name}}</p>
                                        <p class="font-weight-bold">Email</p>
                                        <p>{{$osa->email}}</p>
                                    </div>
                                    <div class="addres">
                                        <form action="{{url('/save-address')}}" method="post">
                                            @csrf
                                            <h3>Customer Address</h3>
                                            <div class="formg address">
                                                <label for="">Address1</label>
                                                <input type="text" name="address1" value="{{$osa->address1}}">

                                                <label for="">Address2</label>
                                                <input type="text" name="address2" value="{{$osa->address2}}">

                                                <label for="">Zip Code</label>
                                                <input type="text" name="zip" value="{{$osa->zip}}">

                                                <label for="">City</label>
                                                <input name="city" type="text" value="{{$osa->city}}">

                                                <label for="">State</label>
                                                <select name="state">
                                                    @foreach ($states as $key=>$value)

                                                    @if($key==$state_key)
                                                    <option value="{{$key}}" selected>{{$value}}</option>
                                                    @else
                                                    <option value="{{$key}}">{{$value}}</option>

                                                    @endif
                                                    @endforeach
                                                </select>
                                                <label>Country</label>
                                                <span>{{$osa->country}}</span>
                                            </div>

                                            @if($order->fulfillment_status== 4)
                                            <button class="btn btn-sm" id="save-address">Save</button>
                                            @endif

                                            <!-- If address was updated -->
                                            @if($osa->update_merchant_id > 0)
                                            <div class="formg">
                                                <p class="updated font-weight-bold">Updated Date </p>
                                                <p> {{$osa->update_date}}</p>
                                            </div>
                                            @endif
                                            <input name="order_id" type="hidden" value="{{$order->id}}">
                                            <input name="id_shopify" type="hidden" value="{{$order->id_shopify}}">
                                        </form>
                                    </div>

                                    @if($order->financial_status == 2)
                                    <h3 class="sod-title">Stripe Payment Detail</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Date</p>
                                        <p>{{$order->created_at}}</p>
                                        <p class="font-weight-bold">Id Transaction</p>
                                        <p>{{$payment_intent}}</p>
                                        <p class="font-weight-bold">Card Number</p>
                                        <p>xxxx xxxx xxxx {{$payment_card_number}}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="rightSide">
                                <div class="box product">
                                    <h3>Product Detail</h3>
                                    <table class="greentable" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>
                                                    IMAGE
                                                </th>
                                                <th>
                                                    PRODUCT NAME
                                                </th>
                                                <th>
                                                    PRICE
                                                </th>
                                                <th>
                                                    QUANTITY
                                                </th>
                                                <th>
                                                    SKU
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order_products as $op)
                                            <tr class="order-detail">
                                                <td>
                                                    <div class="productphoto">
                                                        <img src="{{$op->image_url}}">
                                                    </div>
                                                </td>
                                                <td data-label="PRODUCT NAME"><span class="product-name">{{$op->name}}</span></td>
                                                <td data-label="PRICE" class="nowrap">US ${{$op->price}}</td>
                                                <td data-label="QUANTITY">{{$op->quantity}}</td>
                                                <td class="sku" data-label="SKU">{{$op->sku}}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    <h3 class="mt-5">Cost Summary</h3>
                                    <table class="resumetable resume" cellspacing="0">
                                        <tbody>
                                            <tr>
                                                <td><strong>SUB TOTAL</strong></td>
                                                <td>US ${{number_format($order->total, 2)}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>SHIPPING & HANDLING</strong></td>
                                                <td>US ${{number_format($order->shipping_price, 2)}}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>STORE CREDIT</strong></td>
                                                <td>US ${{number_format(0, 2)}}</td>
                                            </tr>
                                            <!-- Pending refund -->
                                            @if($order->financial_status == 14)
                                            <tr>
                                                <td style="color: #ec6a66" class="refund_amount">
                                                    <strong>REQUEST REFUND AMOUNT</strong>
                                                    <span class="simple-tooltip" title="{{$refund->refund_reason}}" subtitle="{{$refund->refund_notes}}">?</span>
                                                </td>
                                                <td style="color: #ec6a66">US ${{number_format($refund->request_amount, 2)}}</td>
                                            </tr>
                                            <!-- Reject refund -->
                                            @elseif($order->financial_status == 15)
                                            <tr style="color: red">
                                                <td style="color: #ec6a66" class="refund_amount">
                                                    <strong>REJECTED REFUND AMOUNT</strong>
                                                    <span class="simple-tooltip" title="{{$order->notes}}">?</span>
                                                </td>
                                                <td style="color: #ec6a66">US ${{number_format($refund->request_amount, 2)}}</td>
                                            </tr>
                                            <!-- Refunded -->
                                            @elseif($order->financial_status == 3)
                                            <tr>
                                            <td style="color: #ec6a66" class="refund_amount">
                                                <strong>REQUEST REFUND AMOUNT</strong>
                                                <span class="simple-tooltip" title="{{$refund->refund_reason}}" subtitle="{{$refund->refund_notes}}">?</span>
                                            </td>
                                            <td style="color: #ec6a66">
                                                US ${{number_format($refund->request_amount, 2)}}
                                            </td>
                                            </tr>
                                            <tr>
                                            <td style="color: green;">
                                                <strong>REFUNDED AMOUNT</strong>
                                                <span class="simple-tooltip" title="{{$order->notes}}" style="background-color: green;">?</span>
                                            </td>
                                            <td style="color: green">
                                                US ${{number_format($refund->refunded_amount, 2)}}
                                            </td>
                                            </tr>
                                            @endif

                                            <!-- Grand Total -->
                                            @if($order->financial_status == 3)
                                            <tr class="border-top">
                                                <td class="font-weight-bold">GRAND TOTAL</td>
                                                <td>US ${{number_format($order->total + $order->shipping_price - $refund->refunded_amount, 2)}}</td>
                                            </tr>
                                            @else
                                            <tr class="border-top">
                                                <td class="font-weight-bold">GRAND TOTAL</td>
                                                <!-- <td>US ${{$mg_order ? number_format($mg_order->grand_total, 2) : $order->total + $order->shipping_price}}</td> -->
                                                <td>US ${{number_format($order->total + $order->shipping_price , 2)}}</td>
                                            </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <div class="productbtn">
                                    @if($order->fulfillment_status== 4)
                                    <button class="btn btn-sm cancel my-1" id="cancel-button" data-toggle="modal" data-target="#confirm-modal" data-id="{{$order->id}}">Cancel Order</button>
                                    @endif
                                    @if($order->financial_status== 2 && $order->fulfillment_status== 5)
                                    <button class="btn btn-sm cancel my-1" id="cancel-req-button" data-toggle="modal" data-target="#confirm-modal" data-id="{{$order->id}}">Cancel Request</button>
                                    @endif
                                    @if($order->financial_status== App\Libraries\OrderStatus::Outstanding && ($order->fulfillment_status != 9 && $order->fulfillment_status != 12))
                                    <button class="btn btn-sm payments my-1" id="checkout-button" data-id="{{$order->id}}">Pay Order</button>
                                    @endif
                                    @if($order->financial_status== 2 && $order->fulfillment_status== 6)
                                    <button class="btn btn-sm cancel my-1" id="req-refund-button" data-toggle="modal" data-target="#request-refund-modal" data-id="{{$order->id}}">Request Refund</button>
                                    @endif
                                </div>
                            </div>

                            @if($order->fulfillment_status == 12)
                            <br>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                You have completed all 100 orders for the current month.
                                If you want to process an order, you must do it manually
                                at the following link:
                                <a href="https://members.greendropship.com/customer/account/login/" target="_blank"> https://members.greendropship.com/customer/account/login/</a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="text" value="" id="request_type" hidden>

<!-- Request Refund Modal -->
<div id="request-refund-modal" class="modal fade" role="dialog" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 800px">
        <div class="modal-content">
            <div class="modal-header" id="request-refund-modal-header" style="display: block">
                <button type="button" class="close" id="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="request-refund-modal-title">Request Refund</h4>
            </div>
            <div class="modal-body font-17" id="request-refund-modal-body">
                <p>Refunds take 5-10 days to appear on a customer's statement. Stripe's fees for the original payment won't be returned, but there are no additional fees for the refund.</p>
                <!-- <p>So you can refund at most <span style="color: #63c06c;">US ${{ number_format(($order->total + $order->shipping_price) * 0.95, 2)}}</p> -->
                <form class="form" action="{{route('request_refund')}}" method="post">
                    @csrf
                    <table class="greentable" cellspacing="0">
                        <thead>
                            <tr>
                                <th>IMAGE</th>
                                <th>PRODUCT NAME</th>
                                <th>PRICE</th>
                                <th>QUANTITY</th>
                                <th>REFUND QTY</th>
                                <th>SKU</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order_products as $index => $op)
                            <tr class="order-detail">
                                <td>
                                    <div class="productphoto">
                                        <img src="{{$op->image_url}}">
                                    </div>
                                </td>
                                <td data-label="PRODUCT NAME"><span class="product-name">{{$op->name}}</span></td>
                                <td data-label="PRICE" class="nowrap" id="price_{{$index}}">US ${{$op->price}}</td>
                                <td data-label="QUANTITY">{{$op->quantity}}</td>
                                <td data-label="REFUND QTY" class="nowrap d-flex justify-content-between align-items-center d-lg-tcell" style="min-width: 120px;">
                                    <div class="input-group" style="width: fit-content;">
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty({{$index}})">-</button>
                                        </div>
                                        <input readonly class="refund_products" type="number" value="{{$op->quantity}}" id="qty_{{$index}}" min="0" max="{{$op->quantity}}" step="1" name="qtys[{{$op->sku}}]" style="width: 35px; outline: none; border: none; border: 2px solid green;color: green; text-align:center;" data-price="{{$op->price}}">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty({{$index}})">+</button>
                                        </div>
                                    </div>
                                </td>
                                <td class="sku" data-label="SKU">{{$op->sku}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <input type="text" name="order_id" id="refund-order-id" style="display: none">
                    <div class="form-group row w-100 justify-content-center">
                        <label for="shipping-cost" class="col-sm-2 col-form-label">Shipping</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input name="shipping_cost" class="form-control font-17" placeholder="Input Shipping amount to refund" id="shipping-cost" value="{{$order->shipping_price}}" data-cost="{{$order->shipping_price}}">
                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                            <span id="invalid-shipping-amount" style="color: red; padding-top: 10px;">* You can request shippig cost at most US ${{$order->shipping_price}}</span>
                        </div>
                    </div>
                    <div class="form-group row w-100 justify-content-center">
                        <label for="request-amount" class="col-sm-2 col-form-label">Refund</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input name="request_amount" readonly class="form-control font-17" placeholder="Input amount of refund" id="request-amount">
                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group row w-100 justify-content-center">
                        <label for="refund-reason" class="col-sm-2 col-form-label">Reason</label>
                        <div class="col-sm-8" style="margin-top: -22px;">
                            <select id="refund-reason" class="wide font-17" name="refund_reason">
                                <option value="1">Requested by Merchant</option>
                                <option value="2">Exceeds Lead-time</option>
                                <option value="3">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row w-100 justify-content-center" id="refund-note-group">
                        <label for="refund-notes" class="col-sm-2 col-form-label">Notes</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <textarea name="refund_notes" id="refund-notes" placeholder="Please note here about the reason" rows="3" class="w-100 border rounded font-17"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" hidden id="request-refund-sumbit">Submit</button>
                </form>
            </div>

            <div class="modal-footer" id="request-refund-modal-footer" style="display:flex">
                <button class="btn btn-secondary btn-lg" id="request-refund-cancel-button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-lg greenbutton border-0" id="request-refund-confirm-button" data-dismiss="modal">Request</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">

    $(document).ready(function() {

        $("#cancel-button").click(function() {
            $('#confirm-modal-body').html('<h5>Do you really want to cancel the order?</h5>');
            $('#confirm').text('Cancel Order');
            $('#cancel').text('Do Not Cancel');
            $('#request_type').val('cancel');
        });

        $("#cancel-req-button").click(function() {
            $('#confirm-modal-body').html('<h5>Do you really want to cancel the order?</h5>');
            $('#confirm').text('Cancel Order');
            $('#cancel').text('Do Not Cancel');
            $('#request_type').val('cancel_req');
        });

        $('#confirm').click(function() {
            if ($('#request_type').val() === 'cancel') {
                window.location.href = "{{url('orders/cancel/')}}/" + $('#cancel-button').attr('data-id');
            } else if ($('#request_type').val() === 'cancel_req') {
                window.location.href = "{{url('orders/cancel-request/')}}/" + $('#cancel-req-button').attr('data-id');
            } else if ($('#request_type').val() === 'req_refund') {
                window.location.href = "{{url('orders/request-refund/')}}/" + $('#req-refund-button').attr('data-id');
            }
        })

        var checkoutButton = document.getElementById('checkout-button');
        if (checkoutButton) {
            checkoutButton.addEventListener('click', function() {
                var stripe = Stripe('{{env("STRIPE_API_KEY")}}');
                let orders = [$(this).attr('data-id')];
                console.log('ordenes... ' + orders);

                fetch('/create-checkout-session', {
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json, text-plain, */*",
                            "X-Requested-With": "XMLHttpRequest",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        },
                        method: 'POST',
                        body: JSON.stringify({
                            orders: orders
                            //shipping: $('input[name=s_method]:checked', '#shipping-methods').val(),
                        }),
                    })
                    .then(function(response) {
                        if (response.status == 406) {
                            $('#order-limit-modal').modal('show')
                        }
                        return response.json();
                    })
                    .then(function(session) {
                        return stripe.redirectToCheckout({
                            sessionId: session.id
                        }).then(function(result) {
                            console.log('res', result);
                        });
                    })
                    .then(function(result) {
                        // If `redirectToCheckout` fails due to a browser or network
                        // error, you should display the localized error message to your
                        // customer using `error.message`.
                        if (result.error) {
                            alert(result.error.message);
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                    });
            });
        }

        var amountElem = $('#request-amount');
        // var max_amount = (({{ $order->total }} + {{ $order->shipping_price}})* 0.95).toFixed(2);
        var max_amount = (({{ $order->total }} + {{ $order->shipping_price}})).toFixed(2);
        var shipping_amount = ({{ $order->shipping_price }}).toFixed(2);

        $('#req-refund-button').click(function() {
            // set init values
            invalidAmountElem.hide();
            amountElem.val(max_amount);
            $('#refund-reason').val(1);
            $('#request-amount').val(calculateRefundAmount());
        })

        // For Request Refund
        $('#request-refund-confirm-button').click(function() {
            // submit
            $('#refund-order-id').val($('#req-refund-button').attr('data-id'));
            $('#request-refund-sumbit').click();
        })

        // nice-select2
        var options = {placeholder: 'Select the reason'};
        NiceSelect.bind(document.getElementById("refund-reason"), options);


        // For Request Shipping cost
        var invalidAmountElem = $('#invalid-shipping-amount');
        invalidAmountElem.hide();

        $('#shipping-cost').keyup(function () {

            if(parseFloat($(this).val()) > parseFloat(shipping_amount)) {
                $(this).val(shipping_amount);
                invalidAmountElem.show();
            } else if(parseFloat($(this).val()) < 0) {
                $(this).val(0.00)
                invalidAmountElem.show();
            } else {
                invalidAmountElem.hide();
            }

            $('#request-amount').val(calculateRefundAmount());
        })
    });

    // calculate total amount of REFUND
    function calculateRefundAmount() {
        var refund_total = 0;
        var refund_shipping_cost = $('#shipping-cost').val() === "" ? 0 : parseFloat($('#shipping-cost').val());
        $('.refund_products').each(function() {
            var price = parseFloat($(this).attr('data-price'));
            var qty = parseInt($(this).val());
            refund_total += price * qty;
        });
        refund_total = (refund_total + refund_shipping_cost).toFixed(2);
        return refund_total;
    }
    
    function increaseQty(index) {
        var qty = parseInt($('#qty_' + index).val());
        var max = parseInt($('#qty_' + index).attr('max'));
        if (qty < max) {
            $('#qty_' + index).val(qty + 1);
            $('#request-amount').val(calculateRefundAmount());
        }
    }

    function decreaseQty(index) {
        var qty = parseInt($('#qty_' + index).val());
        if (qty > 0) {
            $('#qty_' + index).val(qty - 1);
            $('#request-amount').val(calculateRefundAmount());
        }
    }

</script>


@endsection
