<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data=array(
            'description'=>"Selamat datang di ECSECOND, toko online yang menyediakan produk pilihan dengan layanan yang praktis dan terpercaya.",
            'short_des'=>"ECSECOND adalah toko online untuk menemukan produk pilihan dengan pengalaman belanja yang mudah, aman, dan cepat.",
            'photo'=>"image.jpg",
            'logo'=>'ECSECOND',
            'address'=>"NO. 342 - London Oxford Street, 012 United Kingdom",
            'email'=>"ecsecond@gmail.com",
            'phone'=>"+060 (800) 801-582",
        );
        DB::table('settings')->insert($data);
    }
}
