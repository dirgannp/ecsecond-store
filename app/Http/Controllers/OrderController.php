<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\StatusNotification;
use App\Services\KomerceQrisService;
use App\Services\PaymentKuService;
use App\Services\RajaOngkirService;

class OrderController extends Controller
{
    public function __construct(
        protected RajaOngkirService $rajaOngkir,
        protected KomerceQrisService $komerceQris,
        protected PaymentKuService $paymentKu
    )
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('backend.order.index')->with('orders',$orders);
    }

    public function exportSaw()
    {
        $criteria = [
            'quantity_sold' => ['label' => 'Quantity Sold', 'type' => 'benefit'],
            'sales_amount' => ['label' => 'Sales Amount', 'type' => 'benefit'],
            'order_count' => ['label' => 'Order Count', 'type' => 'benefit'],
        ];

        $weights = $this->sawCriteriaWeights();
        $rows = $this->sawMonthlyFavoriteProductRows($criteria, $weights);
        $fileName = 'data-saw-barang-terfavorit-perbulan-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($rows, $weights) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Month',
                'Ranking',
                'Product ID',
                'Product Name',
                'Category',
                'Product Price',
                'Stock',
                'Quantity Sold',
                'Weight Quantity Sold',
                'Normalized Quantity Sold',
                'Sales Amount',
                'Weight Sales Amount',
                'Normalized Sales Amount',
                'Order Count',
                'Weight Order Count',
                'Normalized Order Count',
                'SAW Score',
            ]);

            foreach ($rows as $row) {
                fputcsv($file, [
                    $row['month'],
                    $row['ranking'],
                    $row['product_id'],
                    $row['product_name'],
                    $row['category_name'],
                    $row['price'],
                    $row['stock'],
                    $row['quantity_sold'],
                    $weights['quantity_sold'],
                    $row['normalized']['quantity_sold'],
                    $row['sales_amount'],
                    $weights['sales_amount'],
                    $row['normalized']['sales_amount'],
                    $row['order_count'],
                    $weights['order_count'],
                    $row['normalized']['order_count'],
                    $row['saw_score'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['Notes']);
            fputcsv($file, ['SAW ranking is calculated per month.']);
            fputcsv($file, ['Benefit criteria: higher values are better: Quantity Sold, Sales Amount, Order Count.']);
            fputcsv($file, ['Sales Amount is calculated from total cart amount for non-cancelled orders.']);
            fputcsv($file, ['Weights are fixed in the system for favorite product ranking.']);

            fclose($file);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address1' => 'required|string|max:500',
            'address2' => 'nullable|string|max:500',
            'country' => 'required|string|max:100',
            'coupon' => 'nullable|numeric',
            'phone' => 'required|numeric|digits_between:10,15',
            'post_code' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'shipping_destination_id' => 'nullable|integer',
            'shipping_destination_label' => 'nullable|string|max:255',
            'shipping_courier' => 'nullable|string|max:50',
            'shipping_service' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cod,qris'
        ], [
            'first_name.required' => 'Nama lengkap harus diisi.',
            'address1.required' => 'Alamat harus diisi.',
            'country.required' => 'Negara harus diisi.',
            'phone.required' => 'Nomor telepon harus diisi.',
            'phone.numeric' => 'Nomor telepon harus berupa angka.',
            'phone.digits_between' => 'Nomor telepon harus 10 sampai 15 digit.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'payment_method.required' => 'Metode pembayaran harus dipilih.',
        ]);

        $cartItems = Cart::where('user_id', auth()->user()->id)
            ->where('order_id', null)
            ->get();
            
        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Cart is Empty!');
        }

        if ($this->rajaOngkir->isConfigured() && !$request->filled('shipping_destination_id')) {
            throw ValidationException::withMessages([
                'shipping_destination_search' => 'Tujuan pengiriman harus dipilih.',
            ]);
        }

        if ($this->rajaOngkir->isConfigured() && (!$request->filled('shipping_courier') || !$request->filled('shipping_service'))) {
            throw ValidationException::withMessages([
                'shipping_rate' => 'Layanan pengiriman harus dipilih.',
            ]);
        }

        if ($this->rajaOngkir->isConfigured() && !in_array(strtolower((string) $validated['shipping_courier']), $this->rajaOngkir->allowedCourierCodes(), true)) {
            throw ValidationException::withMessages([
                'shipping_rate' => 'Silakan pilih Lion Parcel.',
            ]);
        }

        try {
            $order = new Order();
            $order->order_number = 'ORD-' . strtoupper(Str::random(10));
            $order->user_id = auth()->user()->id;
            $order->first_name = $validated['first_name'];
            $order->last_name = $validated['last_name'];
            $order->email = $validated['email'];
            $order->phone = $validated['phone'];
            $order->country = $validated['country'];
            $order->address1 = $validated['address1'];
            $order->address2 = $validated['address2'] ?? null;
            $order->post_code = $validated['post_code'] ?? null;
            $order->sub_total = Helper::totalCartPrice();
            $order->quantity = Helper::cartCount();
            $order->status = 'new';
            $order->payment_method = $validated['payment_method'];
            $shippingPrice = 0;

            if ($this->rajaOngkir->isConfigured()) {
                $weight = Helper::cartWeight();
                $rates = $this->rajaOngkir->calculateDomesticCost(
                    (int) $validated['shipping_destination_id'],
                    $weight,
                    $validated['shipping_courier']
                );

                $selectedRate = collect($rates)->first(function ($rate) use ($validated) {
                    return strtolower((string) ($rate['code'] ?? '')) === strtolower($validated['shipping_courier'])
                        && $this->rajaOngkir->serviceCode($rate['service'] ?? '') === $this->rajaOngkir->serviceCode($validated['shipping_service']);
                });

                if (!$selectedRate) {
                    throw ValidationException::withMessages([
                        'shipping_rate' => 'Layanan pengiriman sudah tidak tersedia. Silakan pilih lagi.',
                    ]);
                }

                $order->shipping_destination_id = (int) $validated['shipping_destination_id'];
                $order->shipping_destination_label = $validated['shipping_destination_label'] ?? null;
                $order->shipping_courier = strtoupper((string) ($selectedRate['code'] ?? $validated['shipping_courier']));
                $order->shipping_service = $selectedRate['service'] ?? $validated['shipping_service'];
                $order->shipping_description = $selectedRate['description'] ?? null;
                $order->shipping_etd = $selectedRate['etd'] ?? null;
                $order->shipping_cost = (float) ($selectedRate['cost'] ?? 0);
                $shippingPrice = (float) $order->shipping_cost;
            }
            
            // Calculate coupon discount
            $couponDiscount = 0;
            if (session('coupon')) {
                $couponDiscount = (float)session('coupon')['value'];
                $order->coupon = $couponDiscount;
            }
            
            // Calculate total
            $order->total_amount = $order->sub_total + $shippingPrice - $couponDiscount;
            
            $order->payment_status = $validated['payment_method'] === 'qris' ? 'paid' : 'unpaid';
            
            $order->save();
            
            Cart::where('user_id', auth()->user()->id)
                ->where('order_id', null)
                ->update(['order_id' => $order->id]);
            
            session()->forget('cart');
            session()->forget('coupon');
            
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                try {
                    $details = [
                        'title' => 'New order created',
                        'actionURL' => route('order.show', $order->id),
                        'fas' => 'fa-file-alt'
                    ];
                    Notification::send($admin, new StatusNotification($details));
                } catch (\Exception $e) {
                    \Log::warning('Order notification failed: ' . $e->getMessage());
                }
            }
            
            $successMessage = $order->payment_method === 'qris'
                ? 'Pembayaran berhasil. Invoice pembayaran Anda sudah dibuat.'
                : 'Pesanan berhasil dibuat. Invoice pesanan Anda sudah dibuat.';

            return redirect()->route('order.invoice', $order->id)
                ->with('success', $successMessage);
                
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Order creation failed: ' . $e->getMessage());
            return back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::with(['cart_info.product', 'user'])->findOrFail($id);
        return view('backend.order.show')->with('order', $order);
    }

    public function invoice($id)
    {
        $order = Order::with(['cart_info.product', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('frontend.pages.invoice', compact('order'));
    }

    public function qrisPayment($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->payment_method !== 'qris') {
            return redirect()->route('home');
        }

        return view('frontend.pages.qris-payment', compact('order'));
    }

    public function qrisStatus($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->qris_history_id && $order->payment_status !== 'paid') {
            $status = $this->komerceQris->paymentStatus($order->qris_history_id);

            if (($status['payment_status'] ?? null) === 'paid') {
                $order->payment_status = 'paid';
                $order->save();
            }
        }

        return response()->json([
            'payment_status' => $order->payment_status,
        ]);
    }

    public function qrisWebhook(Request $request)
    {
        $data = $request->input('data', []);
        $historyId = $data['history_id'] ?? null;

        if ($historyId) {
            $order = Order::where('qris_history_id', $historyId)->first();
            if ($order && (($data['status'] ?? null) === 'paid' || $request->input('event') === 'payment.success')) {
                $order->payment_status = 'paid';
                $order->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed',
        ]);
    }

    public function paymentKuPayment($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->payment_method !== 'paymentku') {
            return redirect()->route('home');
        }

        return view('frontend.pages.paymentku-payment', compact('order'));
    }

    public function paymentKuStatus($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->paymentku_transaction_id && $order->payment_status !== 'paid') {
            $status = $this->paymentKu->paymentStatus($order->paymentku_transaction_id);

            if (($status['payment_status'] ?? null) === 'paid' || ($status['status'] ?? null) === 'success') {
                $order->payment_status = 'paid';
                $order->save();
            }
        }

        return response()->json([
            'payment_status' => $order->payment_status,
        ]);
    }

    public function paymentKuWebhook(Request $request)
    {
        $data = $request->input('data', []) ?: $request->all();
        $transactionId = $data['transaction_id'] ?? $data['id'] ?? null;

        if ($transactionId) {
            $order = Order::where('paymentku_transaction_id', $transactionId)->first();
            if ($order) {
                $status = $data['status'] ?? $data['payment_status'] ?? null;
                if ($status === 'paid' || $status === 'success' || $status === 'settlement') {
                    $order->payment_status = 'paid';
                    $order->save();
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received and processed',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order=Order::find($id);
        return view('backend.order.edit')->with('order',$order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:new,process,delivered,cancel'
        ]);
        
        try {
            $order = Order::with('cart.product')->findOrFail($id);
            
            // Update stock when order is delivered
            if ($validated['status'] == 'delivered' && $order->status != 'delivered') {
                foreach ($order->cart as $cart) {
                    $product = $cart->product;
                    if ($product) {
                        $product->stock -= $cart->quantity;
                        if ($product->stock < 0) {
                            $product->stock = 0;
                        }
                        $product->save();
                    }
                }
            }
            
            $order->status = $validated['status'];
            $order->save();
            
            return redirect()->route('order.index')
                ->with('success', 'Successfully updated order');
                
        } catch (\Exception $e) {
            \Log::error('Order update failed: ' . $e->getMessage());
            return redirect()->route('order.index')
                ->with('error', 'Error while updating order');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            $order->delete();
            
            return redirect()->route('order.index')
                ->with('success', 'Order successfully deleted');
                
        } catch (\Exception $e) {
            \Log::error('Order deletion failed: ' . $e->getMessage());
            return redirect()->route('order.index')
                ->with('error', 'Order could not be deleted');
        }
    }

    public function orderTrack(){
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:255'
        ]);
        
        $order = Order::where('user_id', auth()->user()->id)
            ->where('order_number', $validated['order_number'])
            ->first();
            
        if (!$order) {
            return back()->with('error', 'Invalid order number. Please try again.');
        }
        
        $messages = [
            'new' => 'Your order has been placed. Please wait.',
            'process' => 'Your order is under processing. Please wait.',
            'delivered' => 'Your order is successfully delivered.',
            'cancel' => 'Your order has been canceled. Please try again.'
        ];
        
        $message = $messages[$order->status] ?? 'Order status unknown.';
        $type = ($order->status == 'cancel') ? 'error' : 'success';
        
        return redirect()->route('home')->with($type, $message);
    }

    // PDF generate
    public function pdf(Request $request){
        $order = Order::with(['cart_info.product', 'user'])->findOrFail($request->id);

        if (!auth()->check() || (auth()->user()->role !== 'admin' && (int) $order->user_id !== (int) auth()->id())) {
            abort(403);
        }

        $file_name = $order->order_number . '-' . Str::slug($order->first_name ?: 'customer') . '.pdf';

        $previousErrorReporting = error_reporting();
        error_reporting($previousErrorReporting & ~E_DEPRECATED & ~E_USER_DEPRECATED);
        $pdf = PDF::loadView('backend.order.pdf', compact('order'));

        try {
            $pdf->render();
        } finally {
            error_reporting($previousErrorReporting);
        }

        return $pdf->download($file_name);
    }
    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart_info'])->whereYear('created_at',$year)->where('status','delivered')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart_info->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }

    private function sawCriteriaWeights()
    {
        return [
            'quantity_sold' => 0.5,
            'sales_amount' => 0.3,
            'order_count' => 0.2,
        ];
    }

    private function sawMonthlyFavoriteProductRows(array $criteria, array $weights)
    {
        $monthExpression = $this->sawMonthExpression();

        $products = Cart::query()
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->join('orders', 'carts.order_id', '=', 'orders.id')
            ->leftJoin('categories', 'products.cat_id', '=', 'categories.id')
            ->where('orders.status', '!=', 'cancel')
            ->selectRaw("
                {$monthExpression} as month,
                products.id as product_id,
                products.title as product_name,
                COALESCE(categories.title, '-') as category_name,
                products.price,
                products.stock,
                SUM(carts.quantity) as quantity_sold,
                SUM(carts.amount) as sales_amount,
                COUNT(DISTINCT carts.order_id) as order_count
            ")
            ->groupBy(
                DB::raw($monthExpression),
                'products.id',
                'products.title',
                'categories.title',
                'products.price',
                'products.stock'
            )
            ->get();

        return $products
            ->groupBy('month')
            ->sortKeysDesc()
            ->flatMap(function ($monthlyProducts) use ($criteria, $weights) {
                $normalizers = [];

                foreach ($criteria as $key => $criterion) {
                    $values = $monthlyProducts->pluck($key)
                        ->map(fn ($value) => (float) $value)
                        ->filter(fn ($value) => $value > 0);

                    $normalizers[$key] = $criterion['type'] === 'cost'
                        ? $values->min()
                        : $values->max();
                }

                return $monthlyProducts
                    ->map(function ($product) use ($criteria, $weights, $normalizers) {
                        $normalized = [];
                        $score = 0;

                        foreach ($criteria as $key => $criterion) {
                            $value = (float) $product->{$key};
                            $normalizer = (float) ($normalizers[$key] ?? 0);

                            if ($value <= 0 || $normalizer <= 0) {
                                $normalized[$key] = 0;
                            } elseif ($criterion['type'] === 'cost') {
                                $normalized[$key] = round($normalizer / $value, 6);
                            } else {
                                $normalized[$key] = round($value / $normalizer, 6);
                            }

                            $score += $normalized[$key] * ($weights[$key] ?? 0);
                        }

                        return [
                            'month' => $product->month,
                            'product_id' => $product->product_id,
                            'product_name' => $product->product_name,
                            'category_name' => $product->category_name,
                            'price' => round((float) $product->price, 2),
                            'stock' => (int) $product->stock,
                            'quantity_sold' => (int) $product->quantity_sold,
                            'sales_amount' => round((float) $product->sales_amount, 2),
                            'order_count' => (int) $product->order_count,
                            'normalized' => $normalized,
                            'saw_score' => round($score, 6),
                        ];
                    })
                    ->sortByDesc('saw_score')
                    ->values()
                    ->map(function ($row, $index) {
                        $row['ranking'] = $index + 1;

                        return $row;
                    });
            })
            ->values();
    }

    private function sawMonthExpression()
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', orders.created_at)",
            'pgsql' => "to_char(orders.created_at, 'YYYY-MM')",
            'sqlsrv' => "FORMAT(orders.created_at, 'yyyy-MM')",
            default => 'DATE_FORMAT(orders.created_at, "%Y-%m")',
        };
    }
}
