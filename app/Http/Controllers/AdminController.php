<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Settings;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
class AdminController extends Controller
{
    public function index()
    {
        $bestSellingProducts = $this->bestSellingProducts();

        return view('backend.index')
            ->with('bestSellingProductRows', $bestSellingProducts);
    }

    public function profile()
    {
        $profile = Auth::user();
        return view('backend.users.profile')->with('profile', $profile);
    }

    public function profileUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $id,
            'photo' => 'nullable|string|max:500'
        ]);
        
        try {
            $user = User::findOrFail($id);
            
            // Ensure user can only update their own profile unless admin
            if (Auth::id() != $id && Auth::user()->role != 'admin') {
                return redirect()->back()->with('error', 'Unauthorized action');
            }
            
            // Don't update role from profile page - role should be managed through user management
            $user->update($validated);
            
            return redirect()->back()
                ->with('success', 'Successfully updated your profile');
                
        } catch (\Exception $e) {
            \Log::error('Profile update failed: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Please try again!');
        }
    }

    public function settings()
    {
        $data = Settings::first();
        return view('backend.setting')->with('data', $data);
    }

    public function settingsUpdate(Request $request)
    {
        $validated = $request->validate([
            'short_des' => 'required|string|max:500',
            'description' => 'required|string',
            'photo' => 'required|string|max:500',
            'logo' => 'required|string|max:500',
            'address' => 'required|string|max:500',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
        ]);
        
        try {
            $settings = Settings::first();
            if (!$settings) {
                $settings = Settings::create($validated);
            } else {
                $settings->update($validated);
            }
            
            return redirect()->route('admin')
                ->with('success', 'Setting successfully updated');
                
        } catch (\Exception $e) {
            \Log::error('Settings update failed: ' . $e->getMessage());
            return redirect()->route('admin')
                ->with('error', 'Please try again');
        }
    }

    public function changePassword(){
        return view('backend.layouts.changePassword');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);

        return redirect()->route('admin')->with('success','Password successfully changed');
    }

    // Pie chart
    public function userPieChart(Request $request)
    {
        $bestSellingProducts = $this->bestSellingProducts();

        return view('backend.index')
            ->with('bestSellingProductRows', $bestSellingProducts);
    }

    private function usersByDay()
    {
        $users = User::where('created_at', '>', Carbon::today()->subDays(6))
            ->orderBy('created_at')
            ->get(['created_at']);

        $array = [['Name', 'Number']];
        foreach ($users->groupBy(fn ($user) => $user->created_at->format('Y-m-d')) as $date => $group) {
            $array[] = [Carbon::parse($date)->format('l'), $group->count()];
        }

        return $array;
    }

    private function bestSellingProducts()
    {
        return Cart::query()
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->leftJoin('orders', 'carts.order_id', '=', 'orders.id')
            ->whereNotNull('carts.order_id')
            ->where(function ($query) {
                $query->whereNull('orders.status')
                    ->orWhere('orders.status', '!=', 'cancel');
            })
            ->selectRaw('products.title as product_name, SUM(carts.quantity) as total_sold')
            ->groupBy('products.id', 'products.title')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
    }

    // public function activity(){
    //     return Activity::all();
    //     $activity= Activity::all();
    //     return view('backend.layouts.activity')->with('activities',$activity);
    // }

    public function storageLink(){
        // check if the storage folder already linked;
        if(File::exists(public_path('storage'))){
            // removed the existing symbolic link
            File::delete(public_path('storage'));

            //Regenerate the storage link folder
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
        else{
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
    }
}
