<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'user_id' => 1,
                'name' => 'Vans Oldskool',
                'slug' =>  'vans-oldskool',
                'price' => 750000,
                'description' => 'Vans Old Skool adalah simbol abadi gaya skate yang cocok untuk setiap tampilan. Desainnya yang klasik dan orisinal dengan ciri khas garis samping sidestripe yang ikonik langsung dikenali. 
                    Dibuat dengan kombinasi kanvas dan suede yang kuat, sepatu ini menawarkan daya tahan superior untuk pemakaian sehari-hari.',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/vans-oldskool',
            ],
            [
                'user_id' => 1,
                'name' => 'Television Samsung 55 inch',
                'slug' => 'television-samsung-55-inch',
                'price' => 4500000,
                'description' => 'Televisi Samsung 55 inci menghadirkan pengalaman menonton yang memukau. Dengan layar besar dan kualitas gambar superior, setiap detail terlihat jernih dan warna hidup. Teknologi canggih Samsung memastikan hiburan imersif untuk film, game, atau acara favorit Anda, 
                    menjadikannya pusat perhatian di ruang keluarga.',
                'stock' => 100,
                'category_id' => 2,
                'product_url' => 'http://localhost:8000/product/television-samsung-55-inch',
            ],
            [
                'user_id' => 1,
                'name' => 'Kaos Hitam Polos Esensi Gaya Minimalis',
                'slug' => 'kaos-hitam-polos-esensi-gaya-minimalis',
                'price' => 75000,
                'description' => 'Kaos hitam polos adalah item wajib di setiap lemari pakaian. Kesederhanaannya menawarkan fleksibilitas tak terbatas, cocok untuk gaya kasual hingga semi-formal. Bahan berkualitasnya memberikan kenyamanan sepanjang hari, menjadikannya pilihan sempurna untuk melengkapi tampilan apa pun.',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/kaos-hitam-polos-esensi-gaya-minimalis',
            ],
            [
                'user_id' => 1,
                'name' => 'Buku Prasejarah',
                'slug' => 'buku-prasejarah',
                'price' => 1250000,
                'description' => 'Buku prasejarah membuka wawasan tentang era sebelum tulisan. Lewat temuan arkeologi dan antropologi, kita menelusuri kehidupan manusia purba, perkembangan alat, seni gua, dan asal-usul peradaban. Ini adalah kisah tentang evolusi dan adaptasi, 
                    mengajak kita memahami akar kemanusiaan yang membentuk dunia kini.',
                'stock' => 100,
                'category_id' => 5,
                'product_url' => 'http://localhost:8000/product/buku-prasejarah',
            ],
            [
                'user_id' => 1,
                'name' => 'Kaca Mata Hitam Sentuhan Retro yang Stylish',
                'slug' => 'kaca-mata-hitam-sentuhan-retro-yang-stylish',
                'price' => 50000,
                'description' => 'Kacamata hitam bulat adalah aksesori mode yang tak lekang oleh waktu, memancarkan aura retro dan chic. Bingkainya yang melingkar sempurna memberikan karakter unik pada wajah. Cocok untuk berbagai gaya, 
                    kacamata ini tidak hanya melindungi mata dari sinar UV, tetapi juga menambahkan sentuhan artistik dan kepercayaan diri pada penampilan Anda.',
                'stock' => 100,
                'category_id' => 3,
                'product_url' => 'http://localhost:8000/product/kaca-mata-hitam-sentuhan-retro-yang-stylish',
            ],
            [
                'user_id' => 1,
                'name' => 'Palu Alat Serbaguna Pembangun dan Penghancur',
                'slug' => 'palu-alat-serbaguna-pembangun-dan-penghancur',
                'price' => 180000,
                'description' => 'Palu adalah alat dasar namun esensial, digunakan untuk memukul, membentuk, atau menghancurkan. Terdiri dari kepala berat (biasanya logam) dan gagang, fungsinya sangat beragam. Dari memaku kayu, membongkar struktur, hingga pekerjaan seni. 
                    Palu adalah simbol kekuatan dan ketepatan di tangan para pekerja dan seniman di seluruh dunia.',
                'stock' => 100,
                'category_id' => 4,
                'product_url' => 'http://localhost:8000/product/palu-alat-serbaguna-pembangun-dan-penghancur',
            ],
            [
                'user_id' => 1,
                'name' => 'Basket Ball',
                'slug' => 'basket-ball',
                'price' => 250000,
                'description' => 'Bola Basket yang dirancang khusus untuk pemain profesional, dengan bahan yang berkualitas tinggi untuk daya tahan yang optimal.
                    Cocok untuk pemakaian indoor maupun outdoor.',
                'stock' => 100,
                'category_id' => 1,
                'product_url' => 'http://localhost:8000/product/basket-ball',
            ],
            [
                'user_id' => 1,
                'name' => 'Foot Ball',
                'slug' => 'foot-ball',
                'price' => 150000,
                'description' => 'Bola Sepak yang dirancang khusus untuk pemain profesional, dengan bahan yang berkualitas tinggi untuk daya tahan yang optimal.
                    Cocok untuk pemakaian indoor maupun outdoor.',
                'stock' => 100,
                'category_id' => 1,
                'product_url' => 'http://localhost:8000/product/foot-ball',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
