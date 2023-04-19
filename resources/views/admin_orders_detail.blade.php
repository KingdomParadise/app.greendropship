@extends('layouts.app')

@section('custom_css')
<style>
    .simple-tooltip{
        background-color: #ec6a66;
    }
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
<div class="indexContent" data-page_name="ADMIN ORDER DETAILS">
    <div class="maincontent">
        <div class="wrapinsidecontent">
            <div class="ordertable">
                <div class="otawrap">
                    <div class="twocols2">
                        <div>
                            <div class="box">
                                <div class="cwrap">
                                    <h3>Order Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Shopify Order ID</p>
                                        <p><a href="https://{{$merchant->shopify_url}}/admin/orders/{{$order->id_shopify}}" target="_blank">{{$order->id_shopify}}</a></p>
                                        <p class="font-weight-bold">Customer Order Number</p>
                                        <p>{{ $order->order_number_shopify }}</p>
                                        @if($order->magento_order_id)
                                        <p class="font-weight-bold">GDS Order Number</p>
                                        <p>{{$order->magento_order_id}}</p>
                                        @endif
                                        <p class="font-weight-bold">Date</p>
                                        <p>{{$order->created_at}} {{$order->created_at->tz()}}</p>
                                        <p class="font-weight-bold">Payment Status</p>
                                        <p class="paid text-center" style="background:{{$fs->color}};"><span>{{$fs->name}}</span></p>
                                        <p class="font-weight-bold">Order State</p>
                                        <p class="inprocess text-center" style="background:{{$os->color}};"><span>{{$os->name}}</span></p>

                                        @if($order->fulfillment_status == 9)
                                        <p class="font-weight-bold">Canceld By</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$user_canceled}}</p>
                                        <p class="font-weight-bold">Canceled At</p>
                                        <p class="inprocess" style="background-color: transparent;">{{$order->canceled_at}}</p>
                                        @endif
                                        @if($order->shipping_title)
                                        @if($order->shipping_title)
                                        <p class="font-weight-bold">Shipping</p>
                                        <p>{{$order->shipping_title}}</p>
                                        @if($order->tracking_code)
                                        <p class="font-weight-bold">Tracking Number</p>
                                        <p>{{$order->tracking_code}}</p>
                                        @endif
                                        @endif
                                        @endif

                                    </div>
                                    <h3>Merchant Information</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Name</p>
                                        <p>{{$merchant->name}}</p>
                                        <p class="font-weight-bold">Email</p>
                                        <p>{{$merchant->email}}</p>
                                        <p class="font-weight-bold">Plan</p>
                                        <p>{{$merchant->plan}}</p>

                                    </div>
                                    @if($order->financial_status == 2)
                                    <h3 class="sod-title">Stripe Payment Detail</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Date</p><p>{{$order->created_at}} {{$order->created_at->tz()}}</p>
                                        <p class="font-weight-bold">Id Transaction</p><p><a href="https://dashboard.stripe.com/payments/{{$payment_intent}}" target="_blank">{{$payment_intent}}</a></p>
                                    </div>
                                    @endif
                                    <h3>Customer Address</h3>
                                    <div class="formg">
                                        <p class="font-weight-bold">Name</p><p>{{$osa->first_name}} {{$osa->last_name}}</p>
                                        <p class="font-weight-bold">Address</p><p>{{$osa->address1}} {{$osa->address2}}</p>
                                        <p class="font-weight-bold">Zip Code</p><p>{{$osa->zip}}</p>
                                        <p class="font-weight-bold">City</p><p>{{$osa->city}}</p>
                                        <p class="font-weight-bold">State</p><p>{{$osa->province}}</p>
                                        <p class="font-weight-bold">Country</p><p>{{$osa->country}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="rightSide">
                            <div class="box product">
                                <h3>Product Detail</h3>
                                <table class="greentable order-detail" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>IMAGE</th>
                                            <th>PRODUCT NAME</th>
                                            <th>PRICE</th>
                                            <th>QUANTITY</th>
                                            <th>REQ QTY</th>
                                            <th>SKU</th>
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
                                            <td data-label="PRODUCT NAME" class="product-name">
                                                <span>
                                                    {{$op->name}}
                                                </span>
                                            </td>
                                            <td data-label="PRICE" class="nowrap">
                                                <strong>
                                                    US ${{$op->price}}
                                                </strong>
                                            </td>
                                            <td data-label="QUANTITY">
                                                {{$op->quantity}}
                                            </td>
                                            <td data-label="REQ QTY">
                                            @foreach($ord as $ordp)
                                                @if($ordp->sku == $op->sku)
                                                    {{$ordp->quantity}}
                                                @endif
                                            @endforeach
                                            </td>
                                            <td class="sku" data-label="SKU">{{$op->sku}}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <table class="resumetable resume my-5" cellspacing="0">
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

                                <!-- invisible in case of request refund -->
                                @if($order->financial_status != 14)
                                <div class="notes">
                                    <textarea class="ta{{$order->id}}">{{$order->notes}}</textarea>
                                    <div class="btns">
                                        @if($order->fulfillment_status== 4)
                                        <button class="cancel btn" id="cancel-button" data-toggle="modal" data-target="#confirm-modal" data-id="{{$order->id}}">Cancel Order</button>
                                        @endif
                                        <button id='btnNotes' data-id="{{$order->id}}" class="btn">Update Notes</button>
                                    </div>
                                    <span class="text-right text-green d-none" id="success-note">The notes have updated successfully.</span>
                                </div>
                                @else

                                <div class="notes">
                                    <div class="btns">
                                        <button class="cancel btn" id="reject-refund-button" data-toggle="modal" data-target="#reject-refund-modal" data-id="{{$order->id}}">Reject Refund</button>
                                        <button class="btn-success btn greenbutton" id="approve-refund-button" data-toggle="modal" data-target="#approve-refund-modal" data-id="{{$order->id}}" data-amount="{{$refund->request_amount}}">Approve Refund</button>
                                    </div>
                                </div>

                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="text" value="" id="request_type" hidden>

<!-- Reject Refund Modal -->
<div id="reject-refund-modal" class="modal fade" role="dialog" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" id="reject-refund-modal-header" style="display: block">
                <button type="button" class="close" id="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="reject-refund-modal-title">Reject Refund</h4>
            </div>
            <div class="modal-body font-17" id="reject-refund-modal-body">
                <form class="form" action="{{route('reject_refund')}}" method="post">
                    @csrf
                    <input type="text" name="order_id" id="refund-order-id" style="display: none">
                    <div class="form-group row w-100 justify-content-center" id="refund-note-group">
                        <label for="reject_notes" class="col-sm-2 col-form-label">Note</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <textarea id="reject_notes" name="reject_notes" placeholder="Please note here about reject reason" rows="3" class="w-100 border rounded font-17"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="submit" hidden id="reject-refund-sumbit">Submit</button>
                </form>
            </div>

            <div class="modal-footer" id="reject-refund-modal-footer" style="display:flex">
                <button class="btn btn-secondary btn-lg" id="reject-refund-cancel-button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-lg greenbutton border-0" id="reject-refund-confirm-button" data-dismiss="modal">Reject</button>
            </div>
        </div>
    </div>
</div>

<!-- Approve Refund Modal -->
@if($order->financial_status == 14)
<div id="approve-refund-modal" class="modal fade" role="dialog" data-backdrop="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 900px">
        <div class="modal-content">
            <div class="modal-header" id="approve-refund-modal-header" style="display: block">
                <button type="button" class="close" id="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="approve-refund-modal-title">Approve Refund</h4>
            </div>
            <div class="modal-body font-17" id="approve-refund-modal-body">
                <form class="form" action="{{route('approve_refund')}}" method="post">
                    @csrf
                    <input type="text" name="order_id" id="approve-order-id" style="display: none;">

                    <table class="greentable order-detail" cellspacing="0">
                        <thead>
                            <tr>
                                <th>IMAGE</th>
                                <th>PRODUCT NAME</th>
                                <th>PRICE</th>
                                <th>QUANTITY</th>
                                <th>REQ QTY</th>
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
                                <td data-label="PRODUCT NAME" class="product-name">
                                    <span>
                                        {{$op->name}}
                                    </span>
                                </td>
                                <td data-label="PRICE" class="nowrap" id="price_{{$index}}">
                                    <strong>
                                        US ${{$op->price}}
                                    </strong>
                                </td>
                                <td data-label="QUANTITY">
                                    {{$op->quantity}}
                                </td>
                                <td data-label="REQ QTY">
                                @foreach($ord as $ordp)
                                    @if($ordp->sku == $op->sku)
                                        {{$ordp->quantity}}
                                    @endif
                                @endforeach
                                </td>
                                <td data-label="REFUND QTY" class="nowrap d-flex justify-content-between align-items-center d-lg-tcell" style="min-width: 120px;">
                                    <div class="input-group" style="width: fit-content;">
                                    @foreach($ord as $ordp)
                                        @if($ordp->sku == $op->sku)
                                        <div class="input-group-prepend">
                                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty({{$index}})">-</button>
                                        </div>
                                            <input readonly type="number" value="{{isset($ordp->quantity) ? $ordp->quantity : 0}}" id="qty_{{$index}}" min="0" max="{{$op->quantity}}" step="1" name="qtys[{{$op->sku}}]" style="width: 35px; outline: none; border: none; border: 2px solid green;color: green; text-align:center;"  data-price="{{$op->price}}" class="refund_products">
                                            
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button" onclick="increaseQty({{$index}})">+</button>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="sku" data-label="SKU">{{$op->sku}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="form-group row w-100 justify-content-center">
                        <label for="shipping-cost" class="col-sm-2 col-form-label">
                            Shipping
                            <span class="simple-tooltip" style="background-color: green;" title="The merchant requests refund US ${{$refund->request_shipping_cost}} in shipping cost">?</span>
                        </label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input name="shipping_cost" class="form-control font-17" placeholder="Input Shipping amount to refund" id="shipping-cost" value="{{$refund->request_shipping_cost}}">
                                <div class="input-group-append">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                            <span id="invalid-shipping-amount" style="color: red; padding-top: 10px;">* You can refund shippig cost at most US ${{$order->shipping_price}}</span>
                        </div>
                    </div>

                    <div class="form-group row w-100 justify-content-center">
                        <p class="col-12 col-md-10 col-lg-8 mb-0">Merchant requested refund <span style="color: green"> US ${{$refund->request_amount}} </span>. <br> You are refunding 
                            <span style="color: green">US $</span>
                            <input readonly type="text" id="refund-amount" name="refund_amount" style="width: 50px; outline: none; border: none; border-bottom: 2px dashed green;color: green; text-align:center;">
                            <span class="simple-tooltip" style="background-color: green;" title="You can refund at most US ${{number_format(($order->total + $order->shipping_price), 2)}}">?</span>
                            towards Order {{$order->order_number_shopify}}. Once a refund is applied, it cannot be reversed.
                        </p>
                    </div>

                    <div class="form-check" style="padding-left: 0; padding-bottom: 20px; text-align: center;">
                        <input class="form-check-input" type="checkbox" id="confirm-refund" style="height: 17px; width: 17px;" onchange="handleConfirmRefund()">
                        <label class="form-check-label" for="confirm-refund" style="padding-left: 10px">
                            Are you sure?
                        </label>
                    </div>

                    <button type="submit" hidden id="approve-refund-sumbit">Submit</button>
                </form>
            </div>

            <div class="modal-footer" id="approve-refund-modal-footer" style="display:flex">
                <button class="btn btn-secondary btn-lg" id="approve-refund-cancel-button" data-dismiss="modal">Cancel</button>
                <button class="btn btn-success btn-lg greenbutton border-0" id="approve-refund-confirm-button" data-dismiss="modal" disabled>Approve</button>
            </div>
        </div>
    </div>
</div>
@endif

<script type="text/javascript">
    $(document).ready(function() {
        $("#cancel-button").click(function() {
            $('#confirm-modal-body').html('<h5>Do you really want to cancel the order?</h5>');
            $('#request_type').val('cancel_order');
        });

        $('#reject-refund-button').click(function() {
            $('#refund-order-id').val($('#reject-refund-button').attr('data-id'));
        })

        $('#reject-refund-confirm-button').click(function() {
            $('#reject-refund-sumbit').click();
        })

        $('#approve-refund-button').click(function() {
            $('#approve-order-id').val($('#approve-refund-button').attr('data-id'));
            var refundElem = $('#refund-amount');

            refundElem.val(calculateRefundAmount());
            
        })

        $('#approve-refund-confirm-button').click(function() {
            $('#approve-refund-sumbit').click();
        })

        $('#confirm').click(function() {
            if($('#request_type').val() === 'cancel_order') {
                window.location.href = "{{url('admin/orders/cancel')}}/" + $('#cancel-button').attr('data-id');
            }
        });

        // For Request Shipping cost
        var invalidAmountElem = $('#invalid-shipping-amount');
        var shipping_amount = ({{ $order->shipping_price }}).toFixed(2);
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

            $('#refund-amount').val(calculateRefundAmount());
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
            $('#refund-amount').val(calculateRefundAmount());
        }
    }

    function decreaseQty(index) {
        var qty = parseInt($('#qty_' + index).val());
        if (qty > 0) {
            $('#qty_' + index).val(qty - 1);
            $('#refund-amount').val(calculateRefundAmount());
        }
    }

    function handleConfirmRefund() {
        var confirmRefund = $('#confirm-refund');
        var confirmRefundButton = $('#approve-refund-confirm-button');
        if (confirmRefund.is(':checked')) {
            confirmRefundButton.prop('disabled', false);
        } else {
            confirmRefundButton.prop('disabled', true);
        }
    }
</script>
@endsection
