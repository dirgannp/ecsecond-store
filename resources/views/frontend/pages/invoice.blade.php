@extends('frontend.layouts.master')

@section('title','Invoice Pembayaran')

@section('main-content')
    <section class="payment-invoice section">
        <div class="container">
            @php
                $settings = DB::table('settings')->first();
            @endphp
            <div class="invoice-paper">
                <div class="invoice-head">
                    <div class="invoice-brand">
                        <div class="invoice-logo">Eshop<span>.</span></div>
                        <h2>ECSECOND</h2>
                        <p>{{optional($settings)->address}}</p>
                        <p>Phone: {{optional($settings)->phone}}</p>
                        <p>Email: {{optional($settings)->email}}</p>
                    </div>
                    <div class="invoice-meta">
                        <h1>INVOICE</h1>
                        <p><strong>Invoice:</strong> {{$order->order_number}}</p>
                        <p><strong>Date:</strong> {{$order->created_at->format('d M Y H:i')}}</p>
                        <p><strong>Status:</strong> {{ucfirst($order->status)}}</p>
                    </div>
                </div>

                <div class="invoice-grid">
                    <div class="invoice-box">
                        <h3>BILL TO</h3>
                        <p><strong>{{$order->first_name}} {{$order->last_name}}</strong></p>
                        <p>{{$order->email}}</p>
                        <p>{{$order->phone}}</p>
                        <p>{{$order->address1}}{{ $order->address2 ? ', '.$order->address2 : '' }}</p>
                        <p>{{$order->country}}{{ $order->post_code ? ', '.$order->post_code : '' }}</p>
                    </div>
                    <div class="invoice-box">
                        <h3>PAYMENT & SHIPPING</h3>
                        <p><strong>Payment Method:</strong>
                            @if($order->payment_method == 'cod')
                                Cash on Delivery
                            @elseif($order->payment_method == 'qris')
                                QRIS
                            @else
                                {{strtoupper((string) $order->payment_method)}}
                            @endif
                        </p>
                        <p><strong>Payment Status:</strong> {{ucfirst($order->payment_status)}}</p>
                        <p><strong>Shipping Service:</strong> {{$order->resolved_shipping_label}} {{$order->shipping_service}}</p>
                        <p><strong>Destination:</strong> {{$order->resolved_shipping_destination ?: '-'}}</p>
                        <p><strong>ETA:</strong> {{$order->shipping_etd ?: '-'}}</p>
                    </div>
                </div>

                <div class="invoice-products">
                    <h3>ORDER DETAILS</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->cart_info as $cart)
                                    <tr>
                                        <td>{{optional($cart->product)->title ?? 'Product unavailable'}}</td>
                                        <td class="text-center">{{$cart->quantity}}</td>
                                        <td class="text-right">{{Helper::rupiah($cart->price)}}</td>
                                        <td class="text-right">{{Helper::rupiah($cart->amount)}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="invoice-total">
                    <div><span>Subtotal</span><strong>{{Helper::rupiah($order->sub_total)}}</strong></div>
                    <div><span>Shipping Cost</span><strong>{{Helper::rupiah($order->resolved_shipping_cost)}}</strong></div>
                    @if((float) $order->coupon > 0)
                        <div><span>Discount</span><strong>-{{Helper::rupiah($order->coupon)}}</strong></div>
                    @endif
                    <div class="grand-total"><span>Total</span><strong>{{Helper::rupiah($order->total_amount)}}</strong></div>
                </div>

                <div class="invoice-thanks">Thank you for your business.</div>

                <div class="invoice-actions">
                    <a href="{{route('product-grids')}}" class="btn invoice-secondary">Belanja Lagi</a>
                    <a href="{{route('order.pdf', $order->id)}}" class="btn" target="_blank">Download Invoice</a>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .payment-invoice {
        background: #f3f3f3;
        padding-top: 64px;
    }
    .invoice-paper {
        background: #fff;
        border: 0;
        color: #333;
        margin: 0 auto;
        max-width: 992px;
        padding: 56px 58px 44px;
    }
    .invoice-head,
    .invoice-grid,
    .invoice-actions {
        display: grid;
        gap: 20px;
    }
    .invoice-head {
        align-items: start;
        border-bottom: 2px solid #111;
        grid-template-columns: 1fr auto;
        margin-bottom: 28px;
        padding-bottom: 22px;
    }
    .invoice-logo {
        color: #333;
        font-size: 44px;
        font-weight: 700;
        line-height: 1;
        margin-bottom: 24px;
    }
    .invoice-logo span {
        color: #f7941d;
    }
    .invoice-brand h2 {
        color: #333;
        font-size: 24px;
        font-weight: 700;
        letter-spacing: 1px;
        margin: 0 0 6px;
    }
    .invoice-brand p,
    .invoice-meta p,
    .invoice-box p {
        font-size: 16px;
        line-height: 1.45;
        margin: 0 0 6px;
    }
    .invoice-meta {
        text-align: right;
    }
    .invoice-meta h1 {
        color: #111;
        font-size: 34px;
        font-weight: 800;
        letter-spacing: 1px;
        margin: 18px 0 8px;
    }
    .invoice-grid {
        gap: 0;
        grid-template-columns: repeat(2, 1fr);
        margin-bottom: 32px;
    }
    .invoice-box {
        border: 1px solid #ddd;
        min-height: 224px;
        padding: 18px 16px;
    }
    .invoice-box h3,
    .invoice-products h3 {
        border-bottom: 1px solid #ddd;
        color: #111;
        font-size: 19px;
        font-weight: 700;
        letter-spacing: .4px;
        margin: 0 0 18px;
        padding-bottom: 10px;
        text-transform: uppercase;
    }
    .invoice-products h3 {
        border-bottom: 0;
        margin-bottom: 8px;
        padding-bottom: 0;
    }
    .invoice-products .table {
        margin-bottom: 22px;
    }
    .invoice-products .table th,
    .invoice-products .table td {
        border: 1px solid #ddd;
        font-size: 16px;
        padding: 14px 10px;
        vertical-align: top;
    }
    .invoice-products .table th {
        background: #111;
        color: #fff;
        border-color: #111;
        font-weight: 700;
    }
    .invoice-total {
        margin-left: auto;
        max-width: 395px;
        width: 100%;
    }
    .invoice-total div {
        align-items: center;
        border-bottom: 1px solid #e5e5e5;
        display: flex;
        justify-content: space-between;
        padding: 12px 10px;
    }
    .invoice-total span,
    .invoice-total strong {
        font-size: 16px;
    }
    .invoice-total .grand-total {
        border-bottom: 0;
        font-weight: 700;
    }
    .invoice-total .grand-total span,
    .invoice-total .grand-total strong {
        color: #f7941d;
        font-size: 20px;
    }
    .invoice-thanks {
        margin-top: 30px;
        text-align: right;
    }
    .invoice-actions {
        grid-template-columns: repeat(2, minmax(160px, 220px));
        justify-content: end;
        margin-top: 22px;
    }
    .invoice-actions .btn {
        background: #111;
        border: 2px solid #f7941d;
        color: #fff;
        margin: 0;
        text-align: center;
    }
    .invoice-actions .invoice-secondary {
        background: #555;
        border-color: #555;
    }
    @media (max-width: 767px) {
        .payment-invoice {
            padding-top: 32px;
        }
        .invoice-paper {
            padding: 28px 18px;
        }
        .invoice-head,
        .invoice-grid,
        .invoice-actions {
            grid-template-columns: 1fr;
        }
        .invoice-meta {
            text-align: left;
        }
        .invoice-meta h1 {
            margin-top: 0;
        }
        .invoice-actions {
            justify-content: stretch;
        }
    }
</style>
@endpush
