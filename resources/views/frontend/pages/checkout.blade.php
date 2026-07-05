@extends('frontend.layouts.master')

@section('title','Checkout page')

@section('main-content')

    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">Home<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0)">Checkout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
            
    <section class="shop checkout section">
        <div class="container">
                @php
                    $selectedDestinationId = old('shipping_destination_id');
                    $selectedDestinationLabel = old('shipping_destination_label');
                    $selectedDestinationSearch = old('shipping_destination_search', $selectedDestinationLabel);
                    $selectedCourier = old('shipping_courier');
                    $selectedService = old('shipping_service');
                    $selectedShippingDescription = old('shipping_description');
                    $selectedShippingCost = (float) old('shipping_cost', 0);
                    $selectedShippingEtd = old('shipping_etd');
                    $selectedCourierLabel = strtolower((string) $selectedCourier) === 'lion' ? 'Lion Parcel' : strtoupper((string) $selectedCourier);
                @endphp
                <form class="form" method="POST" action="{{route('cart.order')}}">
                    @csrf
                    <div class="row"> 

                        <div class="col-lg-8 col-12">
                            <div class="checkout-form">
                                <h2>Checkout Order</h2>
                                <p>Complete your shipping details before continuing to payment.</p>
                                @if(!$liveOngkirConfigured)
                                    <div class="alert alert-warning">
                                        Live RajaOngkir shipping rates are not active yet. Please set `RAJAONGKIR_API_KEY` and `RAJAONGKIR_ORIGIN_ID` in `.env` first.
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-lg-12 col-md-12 col-12">
                                        <div class="form-group">
                                            <label>Full Name<span>*</span></label>
                                            <input
                                                type="text"
                                                name="first_name"
                                                placeholder="Enter full name"
                                                required
                                                value="{{old('first_name', trim((($lastOrder ? $lastOrder->first_name : null) ?? $firstName ?? '').' '.(($lastOrder ? $lastOrder->last_name : null) ?? $lastName ?? '')))}}"
                                                class="@error('first_name') is-invalid @enderror"
                                                oninvalid="this.setCustomValidity('Nama lengkap harus diisi.')"
                                                oninput="this.setCustomValidity('')"
                                            >
                                            <input type="hidden" name="last_name" value="{{old('last_name', '-')}}">
                                            @error('first_name')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                            @error('last_name')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Email<span>*</span></label>
                                            <input
                                                type="email"
                                                name="email"
                                                placeholder="Enter email"
                                                required
                                                value="{{old('email', ($lastOrder ? $lastOrder->email : null) ?? ($user ? $user->email : ''))}}"
                                                class="@error('email') is-invalid @enderror"
                                                oninvalid="this.setCustomValidity(this.validity.valueMissing ? 'Email harus diisi.' : 'Format email tidak valid.')"
                                                oninput="this.setCustomValidity('')"
                                            >
                                            @error('email')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Phone Number <span>*</span></label>
                                            <input
                                                type="number"
                                                name="phone"
                                                placeholder="Enter phone number"
                                                required
                                                value="{{old('phone', $lastOrder ? $lastOrder->phone : '')}}"
                                                class="@error('phone') is-invalid @enderror"
                                                oninvalid="this.setCustomValidity('Nomor telepon harus diisi.')"
                                                oninput="this.setCustomValidity('')"
                                            >
                                            @error('phone')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Country<span>*</span></label>
                                            <input type="text" class="form-control" value="Indonesia" readonly>
                                            <input type="hidden" name="country" value="Indonesia">
                                            @error('country')
                                                <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Address<span>*</span></label>
                                            <input
                                                type="text"
                                                name="address1"
                                                placeholder="Enter main address"
                                                required
                                                value="{{old('address1', $lastOrder ? $lastOrder->address1 : '')}}"
                                                class="@error('address1') is-invalid @enderror"
                                                oninvalid="this.setCustomValidity('Alamat harus diisi.')"
                                                oninput="this.setCustomValidity('')"
                                            >
                                            @error('address1')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Address Detail</label>
                                            <input type="text" name="address2" placeholder="Example: house number, RT/RW, landmark" value="{{old('address2', $lastOrder ? $lastOrder->address2 : '')}}">
                                            @error('address2')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Postal Code</label>
                                            <input type="text" name="post_code" placeholder="Enter postal code" value="{{old('post_code', $lastOrder ? $lastOrder->post_code : '')}}">
                                            @error('post_code')
                                                <span class='text-danger checkout-error'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Shipping Destination<span>*</span></label>
                                            <input type="text" name="shipping_destination_search" class="form-control shipping-destination-search @error('shipping_destination_search') is-invalid @enderror @error('shipping_destination_id') is-invalid @enderror" placeholder="Search city, district, or postal code" value="{{$selectedDestinationSearch}}" {{!$liveOngkirConfigured ? 'disabled' : ''}}>
                                            <small class="text-muted d-block mt-2">Total cart weight: {{number_format($cartWeight)}} gram</small>
                                            <div class="shipping-destination-results mt-3">
                                                <div class="destination-result-placeholder">{{$selectedDestinationLabel ?: 'Select a destination search result'}}</div>
                                            </div>
                                            <input type="hidden" name="shipping_destination_id" class="shipping-destination-id" value="{{$selectedDestinationId}}">
                                            <input type="hidden" name="shipping_destination_label" class="shipping-destination-label" value="{{$selectedDestinationLabel}}">
                                            <input type="hidden" name="shipping_courier" class="shipping-courier" value="{{$selectedCourier}}">
                                            <input type="hidden" name="shipping_service" class="shipping-service" value="{{$selectedService}}">
                                            <input type="hidden" name="shipping_description" class="shipping-description" value="{{$selectedShippingDescription}}">
                                            <input type="hidden" name="shipping_cost" class="shipping-cost-input" value="{{$selectedShippingCost}}">
                                            <input type="hidden" name="shipping_etd" class="shipping-etd-input" value="{{$selectedShippingEtd}}">
                                            @error('shipping_destination_search')
                                                <span class='text-danger d-block'>{{$message}}</span>
                                            @enderror
                                            @error('shipping_destination_id')
                                                <span class='text-danger d-block'>{{$message}}</span>
                                            @enderror
                                            <span class="text-danger d-block checkout-error shipping-destination-error"></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-12">
                                        <div class="form-group">
                                            <label>Shipping Service<span>*</span></label>
                                            <div class="shipping-rate-select">
                                                <div class="shipping-rate-placeholder">{{$selectedCourier && $selectedService ? $selectedCourierLabel : 'Choose a Lion Parcel service'}}</div>
                                            </div>
                                            <small class="text-muted d-block mt-2 shipping-rate-help">
                                                {{$liveOngkirConfigured ? 'Choose a destination first to load live Lion Parcel rates.' : 'Live shipping rates are not active.'}}
                                            </small>
                                            @error('shipping_rate')
                                                <span class='text-danger d-block'>{{$message}}</span>
                                            @enderror
                                            @error('shipping_courier')
                                                <span class='text-danger d-block'>{{$message}}</span>
                                            @enderror
                                            @error('shipping_service')
                                                <span class='text-danger d-block'>{{$message}}</span>
                                            @enderror
                                            <span class="text-danger d-block checkout-error shipping-rate-error"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-12">
                            <div class="order-details">
                                <div class="single-widget">
                                    <h2>ORDER SUMMARY</h2>
                                    <div class="content">
                                        <ul>
                                            <li class="order_subtotal" data-price="{{Helper::totalCartPrice()}}">Subtotal<span>{{Helper::rupiah(Helper::totalCartPrice())}}</span></li>
                                            <li>Shipping Destination<span id="selected_shipping_destination">{{$selectedDestinationLabel ?: '-'}}</span></li>
                                            <li>Shipping Service<span id="selected_shipping_label">{{$selectedCourier && $selectedService ? $selectedCourierLabel : 'Choose a Lion Parcel service'}}</span></li>
                                            <li>Estimate<span id="selected_shipping_etd">{{$selectedShippingEtd ?: '-'}}</span></li>
                                            <li>Shipping Cost<span id="selected_shipping_cost">{{Helper::rupiah($selectedShippingCost)}}</span></li>
                                            
                                            @if(session('coupon'))
                                            <li class="coupon_price" data-price="{{session('coupon')['value']}}">Discount<span>{{Helper::rupiah(session('coupon')['value'])}}</span></li>
                                            @endif
                                            @php
                                                $total_amount=Helper::totalCartPrice()+$selectedShippingCost;
                                                if(session('coupon')){
                                                    $total_amount=$total_amount-session('coupon')['value'];
                                                }
                                            @endphp
                                            @if(session('coupon'))
                                                <li class="last"  id="order_total_price">Total<span>{{Helper::rupiah($total_amount)}}</span></li>
                                            @else
                                                <li class="last"  id="order_total_price">Total<span>{{Helper::rupiah($total_amount)}}</span></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                <div class="single-widget">
                                    <h2>Payment</h2>
                                    <div class="content">
                                        <div class="payment-options">
                                            <label class="payment-option">
                                                <input name="payment_method" type="radio" value="cod" {{old('payment_method', 'cod') == 'cod' ? 'checked' : ''}}>
                                                <span>Cash on Delivery</span>
                                            </label>
                                            <label class="payment-option">
                                                <input name="payment_method" type="radio" value="qris" {{old('payment_method') == 'qris' ? 'checked' : ''}}>
                                                <span>QRIS</span>
                                            </label>
                                            @error('payment_method')
                                                <span class='text-danger d-block mt-2'>{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="single-widget get-button">
                                    <div class="content">
                                        <div class="button">
                                            <button type="submit" class="btn">Process Checkout</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
        </div>
    </section>

    <div class="qris-checkout-modal" id="qrisCheckoutModal" aria-hidden="true">
        <div class="qris-checkout-dialog" role="dialog" aria-modal="true" aria-labelledby="qrisCheckoutTitle">
            <button type="button" class="qris-modal-close" aria-label="Close QRIS payment popup">&times;</button>
            <h3 id="qrisCheckoutTitle">QRIS Payment</h3>
            <p class="qris-modal-total">Total: <strong id="qris_modal_total">{{Helper::rupiah($total_amount)}}</strong></p>
            <img src="{{asset('frontend/img/gopay-qris.jpeg')}}" alt="QRIS payment code" class="qris-modal-image">
            <p class="qris-modal-note">Scan this QRIS code, complete the payment, then confirm your order.</p>
            <div class="qris-modal-actions">
                <button type="button" class="btn qris-modal-cancel">Cancel</button>
                <button type="button" class="btn qris-modal-confirm">I Have Paid</button>
            </div>
        </div>
    </div>
    
    <section class="shop-services section home">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-rocket"></i>
                        <h4>Lion Parcel Shipping</h4>
                        <p>Live rates from RajaOngkir</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-reload"></i>
                        <h4>Free Return</h4>
                        <p>Within 30 days returns</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-lock"></i>
                        <h4>Sucure Payment</h4>
                        <p>100% secure payment</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="single-service">
                        <i class="ti-tag"></i>
                        <h4>Best Peice</h4>
                        <p>Guaranteed price</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="shop-newsletter section">
        <div class="container">
            <div class="inner-top">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2 col-12">
                        <div class="inner">
                            <h4>Newsletter</h4>
                            <p> Subscribe to our newsletter and get <span>10%</span> off your first purchase</p>
                            <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
                                <input name="EMAIL" placeholder="Your email address" required="" type="email">
                                <button class="btn">Subscribe</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .list li{
        margin-bottom:0 !important;
    }
    .list li:hover{
        background:#F7941D !important;
        color:white !important;
    }
    .order-details .single-widget .content ul li {
        align-items: flex-start;
        display: grid;
        gap: 16px;
        grid-template-columns: minmax(120px, 1fr) minmax(110px, 46%);
        line-height: 1.45;
        min-height: 34px;
    }
    .order-details .single-widget .content ul li span {
        display: block;
        float: none;
        line-height: 1.45;
        overflow-wrap: anywhere;
        text-align: right;
        width: auto;
    }
    .order-details .single-widget .content ul li.last {
        align-items: center;
        grid-template-columns: 1fr auto;
    }
    .shipping-destination-results,
    .shipping-rate-select {
        display: grid;
        gap: 8px;
    }
    .shipping-destination-results .destination-result-item,
    .shipping-destination-results .destination-result-placeholder,
    .shipping-rate-item,
    .shipping-rate-placeholder {
        background: #f6f7fb;
        border: 1px solid transparent;
        color: #555;
        cursor: pointer;
        font-size: 14px;
        line-height: 1.45;
        padding: 12px 14px;
        text-align: left;
        width: 100%;
    }
    .shipping-destination-results .destination-result-item:hover,
    .shipping-destination-results .destination-result-item:focus,
    .shipping-rate-item:hover,
    .shipping-rate-item:focus {
        background: #fff7ed;
        border-color: #F7941D;
        outline: none;
    }
    .shipping-destination-results .destination-result-item.is-selected,
    .shipping-rate-item.is-selected {
        background: #fff7ed;
        border-color: #F7941D;
        color: #222;
        font-weight: 600;
    }
    .shipping-destination-results .destination-result-placeholder,
    .shipping-rate-placeholder {
        cursor: default;
        opacity: .75;
    }
    .shop.checkout .form input.is-invalid,
    .shop.checkout .shipping-rate-select.is-invalid {
        border: 1px solid #dc3545;
    }
    .shop.checkout .checkout-error {
        display: block;
        font-size: 14px;
        margin-top: 6px;
    }
    .shipping-rate-item strong {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .shipping-rate-item span {
        display: block;
        font-size: 13px;
    }
    .payment-options {
        display: grid;
        gap: 12px;
    }
    .payment-option {
        align-items: center;
        background: #f6f7fb;
        border: 1px solid transparent;
        cursor: pointer;
        display: flex;
        gap: 10px;
        margin: 0;
        padding: 12px 14px;
    }
    .payment-option input {
        margin: 0;
    }
    .qris-checkout-modal {
        align-items: center;
        background: rgba(0, 0, 0, .62);
        display: none;
        inset: 0;
        justify-content: center;
        padding: 20px;
        position: fixed;
        z-index: 9999;
    }
    .qris-checkout-modal.is-open {
        display: flex;
    }
    .qris-checkout-dialog {
        background: #fff;
        max-height: 92vh;
        max-width: 430px;
        overflow-y: auto;
        padding: 28px;
        position: relative;
        text-align: center;
        width: 100%;
    }
    .qris-checkout-dialog h3 {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .qris-modal-close {
        background: transparent;
        border: 0;
        color: #333;
        cursor: pointer;
        font-size: 30px;
        line-height: 1;
        position: absolute;
        right: 14px;
        top: 10px;
    }
    .qris-modal-total {
        margin-bottom: 16px;
    }
    .qris-modal-image {
        border: 1px solid #eee;
        height: auto;
        max-width: 100%;
        padding: 8px;
        width: 320px;
    }
    .qris-modal-note {
        color: #666;
        font-size: 14px;
        line-height: 1.5;
        margin: 16px 0 20px;
    }
    .qris-modal-actions {
        display: grid;
        gap: 10px;
        grid-template-columns: 1fr 1fr;
    }
    .qris-modal-actions .btn {
        border: 0;
        margin: 0;
        width: 100%;
    }
    .qris-modal-cancel {
        background: #777;
    }
</style>
@endpush

@push('scripts')
<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
<script src="{{ asset('frontend/js/select2/js/select2.min.js') }}"></script>
<script>
    $(document).ready(function() { $("select.select2").select2(); });
</script>
<script>
    $(document).ready(function(){
        const liveOngkirConfigured = @json($liveOngkirConfigured);
        const destinationEndpoint = "{{ route('checkout.rajaongkir.destinations') }}";
        const ratesEndpoint = "{{ route('checkout.rajaongkir.rates') }}";
        const $destinationSearch = $('.shipping-destination-search');
        const $destinationResults = $('.shipping-destination-results');
        const $rateSelect = $('.shipping-rate-select');
        const $destinationId = $('.shipping-destination-id');
        const $destinationLabel = $('.shipping-destination-label');
        const $shippingCourier = $('.shipping-courier');
        const $shippingService = $('.shipping-service');
        const $shippingDescription = $('.shipping-description');
        const $shippingCostInput = $('.shipping-cost-input');
        const $shippingEtdInput = $('.shipping-etd-input');
        const $rateHelp = $('.shipping-rate-help');
        const $destinationError = $('.shipping-destination-error');
        const $rateError = $('.shipping-rate-error');
        const $checkoutForm = $('.shop.checkout form.form');
        const $qrisModal = $('#qrisCheckoutModal');
        const $qrisModalTotal = $('#qris_modal_total');
        let destinationSearchTimeout = null;
        let qrisConfirmed = false;

        function formatCurrency(value) {
            return 'Rp ' + Math.round(Number(value || 0)).toLocaleString('id-ID');
        }

        function courierLabel(courier) {
            return String(courier || '').toLowerCase() === 'lion' ? 'Lion Parcel' : String(courier || '').toUpperCase();
        }

        function updateOrderSummary() {
            const subtotal = parseFloat($('.order_subtotal').data('price')) || 0;
            const coupon = parseFloat($('.coupon_price').data('price')) || 0;
            const shippingCost = parseFloat($shippingCostInput.val()) || 0;
            const courier = $shippingCourier.val();
            const service = $shippingService.val();
            const destination = $destinationLabel.val();
            const etd = $shippingEtdInput.val();

            $('#selected_shipping_destination').text(destination || '-');
            $('#selected_shipping_label').text(courier && service ? courierLabel(courier) : 'Choose a Lion Parcel service');
            $('#selected_shipping_etd').text(etd || '-');
            $('#selected_shipping_cost').text(formatCurrency(shippingCost));
            $('#order_total_price span').text(formatCurrency(subtotal + shippingCost - coupon));
        }

        function openQrisModal() {
            $qrisModalTotal.text($('#order_total_price span').text());
            $qrisModal.addClass('is-open').attr('aria-hidden', 'false');
            $('body').css('overflow', 'hidden');
        }

        function closeQrisModal() {
            $qrisModal.removeClass('is-open').attr('aria-hidden', 'true');
            $('body').css('overflow', '');
        }

        function clearCheckoutSelectionErrors() {
            $destinationSearch.removeClass('is-invalid');
            $rateSelect.removeClass('is-invalid');
            $destinationError.text('');
            $rateError.text('');
        }

        $checkoutForm.on('submit', function(event) {
            clearCheckoutSelectionErrors();

            if (liveOngkirConfigured && !$destinationId.val()) {
                event.preventDefault();
                $destinationSearch.addClass('is-invalid');
                $destinationError.text('Tujuan pengiriman harus dipilih.');
                $destinationSearch.focus();
                return;
            }

            if (liveOngkirConfigured && (!$shippingCourier.val() || !$shippingService.val())) {
                event.preventDefault();
                $rateSelect.addClass('is-invalid');
                $rateError.text('Layanan pengiriman harus dipilih.');
                $rateSelect[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const paymentMethod = $('input[name="payment_method"]:checked').val();

            if (!paymentMethod) {
                event.preventDefault();
                return;
            }

            if (paymentMethod === 'qris' && !qrisConfirmed) {
                event.preventDefault();
                openQrisModal();
            }
        });

        $('.qris-modal-close, .qris-modal-cancel').on('click', function() {
            closeQrisModal();
        });

        $qrisModal.on('click', function(event) {
            if (event.target === this) {
                closeQrisModal();
            }
        });

        $('.qris-modal-confirm').on('click', function() {
            qrisConfirmed = true;
            closeQrisModal();
            $checkoutForm[0].submit();
        });

        $(document).on('keydown', function(event) {
            if (event.key === 'Escape' && $qrisModal.hasClass('is-open')) {
                closeQrisModal();
            }
        });

        function resetRateSelection(message) {
            $rateSelect.html('<div class="shipping-rate-placeholder">' + (message || 'Choose a Lion Parcel service') + '</div>');
            $rateSelect.removeClass('is-invalid');
            $rateError.text('');
            $shippingCourier.val('');
            $shippingService.val('');
            $shippingDescription.val('');
            $shippingCostInput.val('');
            $shippingEtdInput.val('');
            updateOrderSummary();
        }

        function applyRateSelection($rate) {
            $shippingCourier.val($rate.data('courier') || '');
            $shippingService.val($rate.data('service') || '');
            $shippingDescription.val($rate.data('description') || '');
            $shippingCostInput.val($rate.data('cost') || '');
            $shippingEtdInput.val($rate.data('etd') || '');
            $rateSelect.find('.shipping-rate-item').removeClass('is-selected');
            $rate.addClass('is-selected');
            $rateSelect.removeClass('is-invalid');
            $rateError.text('');
            updateOrderSummary();
        }

        function loadRates(destinationIdValue) {
            resetRateSelection('Loading live Lion Parcel rates...');
            $rateHelp.text('Loading live rates from RajaOngkir...');

            $.get(ratesEndpoint, { destination_id: destinationIdValue })
                .done(function(response) {
                    const rates = Array.isArray(response.data) ? response.data : [];

                    if (!rates.length) {
                        resetRateSelection('Lion Parcel service is unavailable');
                        $rateHelp.text('No Lion Parcel service is available for this destination.');
                        return;
                    }

                    let options = '';
                    $.each(rates, function(_, rate) {
                        const selected = $shippingCourier.val() === rate.courier_code && $shippingService.val() === rate.service ? ' is-selected' : '';
                        options += '<div class="shipping-rate-item' + selected + '" role="button" tabindex="0"' +
                            ' data-courier="' + rate.courier_code + '"' +
                            ' data-service="' + rate.service + '"' +
                            ' data-description="' + (rate.description || '') + '"' +
                            ' data-cost="' + rate.cost + '"' +
                            ' data-etd="' + (rate.etd || '') + '">' +
                            '<strong>' + rate.courier_name + ' ' + rate.service + '</strong>' +
                            '<span>' + formatCurrency(rate.cost) + (rate.etd ? ' - Estimate ' + rate.etd : '') + '</span>' +
                            '</div>';
                    });

                    $rateSelect.html(options);
                    $rateHelp.text('Choose the Lion Parcel service you want to use.');

                    const $selectedRate = $rateSelect.find('.shipping-rate-item.is-selected').first();
                    if ($selectedRate.length) {
                        applyRateSelection($selectedRate);
                    } else {
                        updateOrderSummary();
                    }
                })
                .fail(function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : 'Failed to load live Lion Parcel rates.';
                    resetRateSelection(message);
                    $rateHelp.text(message);
                });
        }

        if (!liveOngkirConfigured) {
            updateOrderSummary();
            return;
        }

        $destinationSearch.on('input', function() {
            const keyword = $(this).val().trim();
            clearTimeout(destinationSearchTimeout);
            $destinationId.val('');
            $destinationLabel.val('');
            resetRateSelection('Choose a Lion Parcel service');

            if (keyword.length < 3) {
                $destinationResults.html('<div class="destination-result-placeholder">Type at least 3 characters to search destinations</div>');
                return;
            }

            destinationSearchTimeout = setTimeout(function() {
                $destinationResults.html('<div class="destination-result-placeholder">Searching destinations...</div>');

                $.get(destinationEndpoint, { search: keyword })
                    .done(function(response) {
                        const results = Array.isArray(response.data) ? response.data : [];
                        let options = '';

                        $.each(results, function(_, item) {
                            options += '<div class="destination-result-item" role="button" tabindex="0" data-id="' + item.id + '" data-label="' + item.label + '">' + item.label + '</div>';
                        });

                        $destinationResults.html(options);

                        if (!results.length) {
                            $destinationResults.html('<div class="destination-result-placeholder">Destination not found</div>');
                        }
                    })
                    .fail(function(xhr) {
                        const message = xhr.responseJSON && xhr.responseJSON.message
                            ? xhr.responseJSON.message
                            : 'Failed to search destinations.';
                        $destinationResults.html('<div class="destination-result-placeholder">' + message + '</div>');
                    });
            }, 400);
        });

        function selectDestinationItem($selected) {
            const selectedId = $selected.data('id');
            const selectedLabel = $selected.data('label') || '';

            $destinationId.val(selectedId);
            $destinationLabel.val(selectedLabel);
            $destinationSearch.val(selectedLabel);
            $destinationSearch.removeClass('is-invalid');
            $destinationError.text('');
            $destinationResults.html('<div class="destination-result-item is-selected" role="button" tabindex="0" data-id="' + selectedId + '" data-label="' + selectedLabel + '">' + selectedLabel + '</div>');
            updateOrderSummary();

            if (selectedId) {
                loadRates(selectedId);
            } else {
                resetRateSelection('Choose a Lion Parcel service');
            }
        }

        $destinationResults.on('click', '.destination-result-item', function() {
            selectDestinationItem($(this));
        });

        $destinationResults.on('keydown', '.destination-result-item', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectDestinationItem($(this));
            }
        });

        $rateSelect.on('click', '.shipping-rate-item', function() {
            applyRateSelection($(this));
        });

        $rateSelect.on('keydown', '.shipping-rate-item', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                applyRateSelection($(this));
            }
        });

        if ($destinationId.val()) {
            const initialDestinationLabel = @json($selectedDestinationLabel);
            $destinationResults.html('<div class="destination-result-item is-selected" role="button" tabindex="0" data-id="' + $destinationId.val() + '" data-label="' + (initialDestinationLabel || '') + '">' + (initialDestinationLabel || 'Selected destination') + '</div>');
            loadRates($destinationId.val());
        } else {
            updateOrderSummary();
        }
    });
</script>
@endpush
