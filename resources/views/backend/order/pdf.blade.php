<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Invoice @if($order) {{$order->order_number}} @endif</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      color: #222;
      font-family: DejaVu Sans, sans-serif;
      font-size: 12px;
      line-height: 1.5;
      margin: 0;
    }
    .header {
      border-bottom: 2px solid #111;
      margin-bottom: 24px;
      padding-bottom: 18px;
    }
    .brand {
      float: left;
      width: 48%;
    }
    .brand-logo {
      color: #111;
      font-size: 30px;
      font-weight: bold;
      line-height: 1;
      margin-bottom: 18px;
    }
    .brand-logo span {
      color: #f7941d;
    }
    .brand h2 {
      font-size: 17px;
      letter-spacing: 1px;
      margin: 0 0 4px;
    }
    .invoice-title h1 {
      margin: 0;
    }
    .invoice-title {
      float: right;
      text-align: right;
      width: 48%;
    }
    .invoice-title h1 {
      color: #111;
      font-size: 26px;
      letter-spacing: 1px;
      margin-bottom: 8px;
    }
    .clearfix {
      clear: both;
    }
    .section {
      margin-bottom: 22px;
    }
    .info-table {
      border-collapse: collapse;
      table-layout: fixed;
      width: 100%;
    }
    .info-table td {
      border: 1px solid #ddd;
      padding: 12px 14px;
      vertical-align: top;
      width: 50%;
    }
    .section-title {
      border-bottom: 1px solid #ddd;
      color: #111;
      font-size: 14px;
      font-weight: bold;
      margin: 0 0 10px;
      padding-bottom: 7px;
      text-transform: uppercase;
    }
    table {
      border-collapse: collapse;
      width: 100%;
    }
    th {
      background: #111;
      color: #fff;
      font-weight: bold;
      text-align: left;
    }
    th,
    td {
      border: 1px solid #ddd;
      padding: 9px 8px;
      vertical-align: top;
    }
    .text-right {
      text-align: right;
    }
    .text-center {
      text-align: center;
    }
    .totals {
      margin-left: auto;
      margin-top: 14px;
      width: 45%;
    }
    .totals td {
      border: 0;
      border-bottom: 1px solid #ddd;
    }
    .totals .grand-total td {
      color: #f7941d;
      font-size: 14px;
      font-weight: bold;
    }
    .footer {
      margin-top: 28px;
      text-align: right;
    }
    .signature {
      margin-top: 36px;
    }
  </style>
</head>
<body>
@if($order)
  <div class="header">
    <div class="brand">
      <div class="brand-logo">Eshop<span>.</span></div>
      <h2>{{ config('app.name', 'ECSECOND') }}</h2>
      <div>{{ env('APP_ADDRESS') }}</div>
      <div>Phone: {{ env('APP_PHONE') }}</div>
      <div>Email: {{ env('APP_EMAIL') }}</div>
    </div>
    <div class="invoice-title">
      <h1>INVOICE</h1>
      <div><strong>Invoice:</strong> {{$order->order_number}}</div>
      <div><strong>Date:</strong> {{$order->created_at->format('d M Y H:i')}}</div>
      <div><strong>Status:</strong> {{ucfirst($order->status)}}</div>
    </div>
    <div class="clearfix"></div>
  </div>

  <div class="section">
    <table class="info-table">
      <tr>
        <td>
          <h3 class="section-title">Bill To</h3>
          <div><strong>{{$order->first_name}} {{$order->last_name}}</strong></div>
          <div>{{$order->email}}</div>
          <div>{{$order->phone}}</div>
          <div>{{$order->address1}}{{ $order->address2 ? ', '.$order->address2 : '' }}</div>
          <div>{{$order->country}}{{ $order->post_code ? ', '.$order->post_code : '' }}</div>
        </td>
        <td>
          <h3 class="section-title">Payment & Shipping</h3>
          <div><strong>Payment Method:</strong>
            @if($order->payment_method == 'cod')
              Cash on Delivery
            @elseif($order->payment_method == 'qris')
              QRIS
            @elseif($order->payment_method == 'paymentku')
              QRIS
            @else
              {{ strtoupper((string) $order->payment_method) }}
            @endif
          </div>
          <div><strong>Payment Status:</strong> {{ucfirst($order->payment_status)}}</div>
          <div><strong>Shipping Service:</strong> {{$order->resolved_shipping_label}} {{$order->shipping_service}}</div>
          <div><strong>Destination:</strong> {{$order->resolved_shipping_destination}}</div>
          <div><strong>ETA:</strong> {{$order->shipping_etd ?: '-'}}</div>
        </td>
      </tr>
    </table>
  </div>

  <div class="section">
    <h3 class="section-title">Order Details</h3>
    <table>
      <thead>
        <tr>
          <th style="width: 42%;">Product</th>
          <th class="text-center" style="width: 16%;">Quantity</th>
          <th class="text-right" style="width: 21%;">Unit Price</th>
          <th class="text-right" style="width: 21%;">Line Total</th>
        </tr>
      </thead>
      <tbody>
        @foreach($order->cart_info as $cart)
          <tr>
            <td>{{ optional($cart->product)->title ?? 'Product unavailable' }}</td>
            <td class="text-center">{{$cart->quantity}}</td>
            <td class="text-right">{{Helper::rupiah($cart->price)}}</td>
            <td class="text-right">{{Helper::rupiah($cart->amount)}}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <table class="totals">
      <tr>
        <td>Subtotal</td>
        <td class="text-right">{{Helper::rupiah($order->sub_total)}}</td>
      </tr>
      <tr>
        <td>Shipping Cost</td>
        <td class="text-right">{{Helper::rupiah($order->resolved_shipping_cost)}}</td>
      </tr>
      @if((float) $order->coupon > 0)
        <tr>
          <td>Discount</td>
          <td class="text-right">-{{Helper::rupiah($order->coupon)}}</td>
        </tr>
      @endif
      <tr class="grand-total">
        <td>Total</td>
        <td class="text-right">{{Helper::rupiah($order->total_amount)}}</td>
      </tr>
    </table>
  </div>

  <div class="footer">
    <div>Thank you for your business.</div>
    <div class="signature">
      <div>________________________________</div>
      <strong>Authorized Signature</strong>
    </div>
  </div>
@else
  <h3>Invalid order</h3>
@endif
</body>
</html>
