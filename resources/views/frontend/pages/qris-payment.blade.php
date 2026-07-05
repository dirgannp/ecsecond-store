@extends('frontend.layouts.master')

@section('title','GoPay QRIS Payment')

@section('main-content')
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0)">GoPay QRIS Payment</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="shop checkout section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-12">
                    <div class="order-details qris-payment-page">
                        <div class="single-widget">
                            <h2>GoPay QRIS Payment</h2>
                            <div class="content text-center">
                                <p class="mb-2">Order: <strong>{{$order->order_number}}</strong></p>
                                <p class="mb-3">Total payment: <strong>Rp {{number_format($order->qris_final_amount ?: $order->total_amount, 0, ',', '.')}}</strong></p>

                                @if($order->payment_status === 'paid')
                                    <div class="alert alert-success">Payment has been received.</div>
                                @elseif($order->qris_string)
                                    <img
                                        class="qris-code"
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={{urlencode($order->qris_string)}}"
                                        alt="GoPay QRIS {{$order->order_number}}"
                                    >
                                    <p class="mt-3">Scan this QRIS with GoPay to complete payment.</p>
                                    @if($order->qris_expiry_time)
                                        <p>Valid until: <strong>{{$order->qris_expiry_time->format('d M Y H:i')}}</strong></p>
                                    @endif
                                    <div id="qris-status" class="alert alert-info mt-3">Waiting for payment...</div>
                                @if($order->qris_string)
                                    <img
                                        class="qris-code"
                                        src="https://api.qrserver.com/v1/create-qr-code/?size=320x320&data={{urlencode($order->qris_string)}}"
                                        alt="GoPay QRIS {{$order->order_number}}"
                                    >
                                    <p class="mt-3">Scan this QRIS with GoPay to complete payment.</p>
                                    @if($order->qris_expiry_time)
                                        <p>Valid until: <strong>{{$order->qris_expiry_time->format('d M Y H:i')}}</strong></p>
                                    @endif
                                    <div id="qris-status" class="alert alert-info mt-3">Waiting for payment...</div>
                                @else
                                    <img
                                        class="qris-code qris-static"
                                        src="{{asset('frontend/img/gopay-qris.jpeg')}}"
                                        alt="GoPay QRIS {{$order->order_number}}"
                                    >
                                    <p class="mt-3">Scan this QRIS with GoPay to complete payment.</p>
                                    <div class="alert alert-info mt-3">Waiting for manual payment confirmation.</div>
                                @endif

                                <a href="{{route('user.order.index')}}" class="btn mt-3">View Orders</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .qris-payment-page .qris-code {
            width: 320px;
            max-width: 100%;
            height: auto;
            border: 1px solid #eee;
            padding: 12px;
            background: #fff;
        }

        .qris-payment-page .qris-static {
            width: 360px;
            padding: 0;
        }
    </style>

    @if($order->payment_status !== 'paid' && $order->qris_history_id)
        <script>
            (function () {
                var statusBox = document.getElementById('qris-status');
                function checkStatus() {
                    fetch("{{route('order.qris.status', $order->id)}}", {
                        headers: {'Accept': 'application/json'}
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (data) {
                            if (data.payment_status === 'paid') {
                                statusBox.className = 'alert alert-success mt-3';
                                statusBox.textContent = 'Payment has been received.';
                            } else {
                                setTimeout(checkStatus, 10000);
                            }
                        })
                        .catch(function () {
                            setTimeout(checkStatus, 15000);
                        });
                }
                setTimeout(checkStatus, 10000);
            })();
        </script>
    @endif
@endsection
