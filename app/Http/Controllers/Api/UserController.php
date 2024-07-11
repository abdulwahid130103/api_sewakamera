<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(){
        $data = DB::table('st_user')->get();

        return response()->json($data);
    }

    public function login(Request $request){
        $data = DB::table('st_user')
                    ->where('email',$request->email)
                    ->where('password',$request->password)
                    ->first();

        if(!is_null($data)){
            if(!is_null($data->image)){
                $namaGambar = $data->image;
                $gambarUrl = asset('storage/user/' . $namaGambar);
                $data->image = $gambarUrl;
            }
        }
        $result = "";

        if(!is_null($data)){
            $result = [
                "success" => "true",
                "message"   => "Berhasil login !",
                "data" => $data
            ];
        }else{
            $result = [
                "success" => "false",
                "message"   => "Data tidak ada !",
            ];
        }
        return response()->json($result);
    }

    public function get_produk(){
        $data = DB::table('product as p')
        ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->limit(3)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_produk_terbaru(){
        $data = DB::table('product as p')
        ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->latest('p.created_date')
        ->limit(3)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $sumRating = DB::table('rating')
            ->where('id_produk', $produk->id)
            ->sum('rating');

            $sumRatingBagi = DB::table('rating')
            ->where('id_produk',$produk->id)
            ->count();

            if($sumRatingBagi != 0){
                $produk->rating = $sumRating / $sumRatingBagi;
            }else{
                $produk->rating = 0;
            }
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_produk_terlaris(){
        $data = DB::table('product as p')
        ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->oldest('p.created_date')
        ->limit(3)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $sumRating = DB::table('rating')
            ->where('id_produk', $produk->id)
            ->sum('rating');

            $sumRatingBagi = DB::table('rating')
            ->where('id_produk',$produk->id)
            ->count();

            if($sumRatingBagi != 0){
                $produk->rating = $sumRating / $sumRatingBagi;
            }else{
                $produk->rating = 0;
            }
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_category(){
        $data = DB::table('category')
        ->select('id','name', 'image_category')
        ->get();

        $modifiedData = [];
        foreach ($data as $category) {
            $namaGambar = $category->image_category;
            $gambarUrl = asset('storage/category/' . $namaGambar);
            $category->gambar_url = $gambarUrl;
            $modifiedData[] = $category;
        }
        return response()->json($modifiedData);
    }

    public function get_detail_produk($id){
        $data = DB::table('product as p')
        ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->join('st_user as s', 's.id', '=', 'p.id_mitra')
        ->where('p.id',$id)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $sumRating = DB::table('rating')
            ->where('id_produk', $id)
            ->sum('rating');

            $sumRatingBagi = DB::table('rating')
            ->where('id_produk', $id)
            ->count();

            if($sumRatingBagi != 0){
                $produk->rating = $sumRating / $sumRatingBagi;
            }else{
                $produk->rating = 0;
            }


            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function insert_keranjang(Request $request){

        $produkId = (int)$request->product_id;
        $userId = (int)$request->user_id;

        $cekData = DB::table('keranjang')
        ->where('product_id', $produkId)
        ->where('user_id', $userId)
        ->first();

        if($cekData){
            DB::table('keranjang')
                ->where('product_id', $produkId)
                ->where('user_id', $userId)
                ->update([
                    'qty' => (int)$cekData->qty + (int)$request->qty,
                    'updated_date' => now(),
                ]);
        } else {
            DB::table('keranjang')->insert([
                'product_id' => (int)$request->product_id,
                'user_id' => (int)$request->user_id,
                'qty' => (int)$request->qty,
                'price' => (int)$request->price,
                'created_date' => now(),
                'updated_date' => now(),
                'is_deleted' => 0
            ]);
        }




        return response()->json([
            "success" => "true",
            'message' => 'Data keranjang berhasil disimpan'
        ]);
    }

    public function get_keranjang($id) {
        $data = DB::table('keranjang as k')
        ->join('product as p', 'k.product_id', '=', 'p.id')
        ->join('st_user as u', 'u.id', '=', 'k.user_id')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->where('k.user_id','=',$id)
        ->select(
            'k.id',
	        'p.id AS product_id',
            'k.user_id',
            'u.name as nama_user',
            'c.name as nama_category',
            'p.id_mitra',
            DB::raw('(SELECT um.name FROM st_user as um WHERE um.id = p.id_mitra) as nama_mitra'),
            DB::raw('(SELECT um.image FROM st_user as um WHERE um.id = p.id_mitra) as image_mitra'),
            'k.qty',
            'p.nama_produk',
            'p.image',
            'p.type',
            'p.harga',
            'p.stok',
            'p.deskripsi'
        )
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }

        $result = [];
        foreach ($modifiedData as $key => $value) {
            $mitraFound = false;
            foreach ($result as &$item) {
                if ($item["id_mitra"] == $value->id_mitra) {
                    $item["produk"][] = [
                        "id" => $value->id,
                        "id_mitra" => $value->id_mitra,
                        "id_product" => $value->product_id,
                        "nama_produk" => $value->nama_produk,
                        "image" => $value->image,
                        "type" => $value->type,
                        "harga" => $value->harga,
                        "qty" => $value->qty,
                        "deskripsi" => $value->deskripsi,
                        "gambar_url" => $value->gambar_url,
                    ];
                    $mitraFound = true;
                    break;
                }
            }
            if (!$mitraFound) {
                $result[] = [
                    "id" => $value->id,
                    "id_mitra" => $value->id_mitra,
                    "nama_mitra" => $value->nama_mitra,
                    "image_mitra" => asset('storage/user/'.$value->image_mitra),
                    "produk" => [
                        [
                            "id" => $value->id,
                            "id_mitra" => $value->id_mitra,
                            "id_product" => $value->product_id,
                            "nama_produk" => $value->nama_produk,
                            "image" => $value->image,
                            "type" => $value->type,
                            "harga" => $value->harga,
                            "qty" => $value->qty,
                            "deskripsi" => $value->deskripsi,
                            "gambar_url" => $value->gambar_url,
                        ]
                    ]
                ];
            }
        }


        return response()->json($result);
    }

    public function get_mitra() {
        $data = DB::table('st_user')
        ->select(
            'id',
            'name',
            'image',
            )
        ->where('id_credential','=','4')
        ->get();

        $modifiedData = [];
        foreach ($data as $mitra) {
            $namaGambar = $mitra->image;
            $gambarUrl = asset('storage/user/' . $namaGambar);
            $mitra->gambar_url = $gambarUrl;
            $modifiedData[] = $mitra;
        }
        return response()->json($modifiedData);
    }

    public function delete_keranjang($id){
        DB::table('keranjang')->where('id',$id)->delete();
        return response()->json([
            "success" => "true",
            'message' => 'Data keranjang berhasil dihapus'
        ]);
    }

    public function update_qty_keranjang($id,Request $request){
        DB::table('keranjang')->where('id',$id)->update([
            "qty" => $request->qty
        ]);
        return response()->json([
            "success" => "true",
            'message' => 'Data keranjang berhasil diupdate'
        ]);
    }

    public function create_transaksi_booking(Request $request){

        $datanya = json_decode($request->input('data'), true);

        try {
            $transaksi = DB::table('transaksi')->insertGetId([
                'id_user' => (int)$request->id_user,
                'status' => $request->status,
                'tgl_pinjam' => $request->tgl_pinjam,
                'tgl_tenggat' => $request->tgl_tenggat,
                'tgl_booking' => $request->tgl_booking,
                'id_mitra' => (int)$request->id_mitra,
                'total_harga' => (int)$request->total_harga
            ]);

            foreach ($datanya as $item) {
                $data = [
                    "id_produk" => (int)$item["id_produk"],
                    "id_transaksi" => $transaksi,
                    "jumlah" => (int)$item["jumlah"],
                    "sub_total" => (int)$item["sub_total"]
                ];
                DB::table('detail_transaksi')->insert($data);
            }

            return response()->json([
                "success" => true,
            'message' => 'Berhasil mengajukan penyewaan barang , Harap Tunggu Mitra Verifikasi !'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'message' => 'Gagal mengajukan penyewaan barang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_booking_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "booking")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_terverifikasi_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "terverifikasi")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            't.id_user',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function charge(Request $request)
    {

        // return response()->json($request->method());
        $server_key = 'SB-Mid-server-d6Y8GDKsSkjqp_0W0kIujYDQ';
        $is_production = false;
        $api_url = $is_production ?
            'https://app.midtrans.com/snap/v1/transactions' :
            'https://app.sandbox.midtrans.com/snap/v1/transactions';

        if ($request->isMethod('post')) {
            $requestBody = json_encode($request->all());
            $chargeResult = $this->chargeAPI($api_url, $server_key, $requestBody);
            return response($chargeResult['body'], $chargeResult['http_code'])
                ->header('Content-Type', 'application/json');
        } else {
            abort(404, "Page not found or wrong HTTP request method is used");
        }
    }

    private function chargeAPI($api_url, $server_key, $request_body)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode($server_key . ':')
        ])->post($api_url, json_decode($request_body, true));

        return [
            'body' => $response->body(),
            'http_code' => $response->status(),
        ];
    }

    public function get_detail_transaksi_bayar(Request $request){

        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.total_harga', 't.id_user','su.name','su.email','su.phone_number','su.ktp')
        ->join('st_user as su', 'su.id', '=', 't.id_user')
        ->where('t.id_user', (int)$request->id_user)
        ->where('t.id', (int)$request->id_transaksi)
        ->where('t.status', "terverifikasi")
        ->get();


        $transactionsDetails = [];

        foreach ($data as $key => $value) {

            $detail = DB::table('detail_transaksi as dt')
            ->join('product as p', 'p.id', '=', 'dt.id_produk')
            ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
            ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
            ->select(
                'dt.id',
                'su.name as nama_mitra',
                'p.nama_produk',
                'dt.id_transaksi',
                'dt.jumlah',
                'p.harga',
                'dt.sub_total as sub_harga')
            ->where('dt.id_transaksi', $value->id)
            ->get();
            $transactionsDetails[] =[
                "id" =>$value->id,
                "name" =>$value->name,
                "email" =>$value->email,
                "phone_number" =>$value->phone_number,
                "ktp" =>$value->ktp,
                "total_harga" =>$value->total_harga,
                "id_user" =>$value->id_user,
                "product" => $detail
            ];
        }


        return response()->json($transactionsDetails);
    }

    public function get_update_transaksi_status(Request $request){
        $id = (int)$request->id_transaksi;
        $transaction_id = $request->transaction_id;
        $transaction_status = $request->transaction_status;
        $transaction_time = $request->transaction_time;
        $payment_type = $request->payment_type;
        $expiry_time = $request->expiry_time;
        $status = $request->status;
        $va_number = $request->va_number;

        DB::table('transaksi')->where("id",$id)->update([
            "transaction_id" => $transaction_id,
            "status_bayar" => $transaction_status,
            "tgl_transaksi" => $transaction_time,
            "metode_pembayaran" => $payment_type,
            "tanggal_expire" => $expiry_time,
            "status" => $status,
            "va_number" => $va_number
        ]);
        return response()->json([
            "success" => "true",
            'message' => 'Data berhasil diupdate'
        ]);
    }

    public function get_update_status_expired(Request $request){
        $id = (int)$request->id_transaksi;
        $status = $request->status;
        $status_bayar = $request->status_bayar;

        DB::table('transaksi')->where("id",$id)->update([
            "status" => $status,
            "status_bayar" => $status_bayar,
        ]);
        return response()->json([
            "success" => "true",
            'message' => 'Data berhasil diupdate'
        ]);
    }

    public function register(Request $request){

        $validasi = Validator::make($request->all(),[
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'ktp' => 'required',
            'phone_number' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'password' => 'required',
            'file_image' => 'required',
            'file_ktp_image' => 'required'
        ]);

        if($validasi->fails()){
            $errors = $validasi->errors()->all();

            return response()->json([
                'status' => 0,
                'message' => 'Harap menyesuaikan emailnya !'
            ]);
        }else{

            $cekEmailFound = DB::table('st_user')->where("email",(string)$request->email)->count();

            if($cekEmailFound > 0){
                return response()->json([
                    'status' => 0,
                    'message' => 'Maaf email sudah terdaftar !'
                ]);
            }else{
                $file_p = $request->input('file_image');
                $file_k = $request->input('file_ktp_image');

                $dt_credential = DB::table('app_credential')->where("name","pelanggan")->first();
                Storage::disk('public')->put('user/'.$file_p,base64_decode($request->image));
                Storage::disk('public')->put('ktp/'.$file_k,base64_decode($request->ktp_image));
                $user = DB::table('st_user')->insert([
                    "id_credential" => $dt_credential->id,
                    "name" => (string)$request->name,
                    "username" => (string)$request->username,
                    "email" => (string)$request->email,
                    "ktp" => (string)$request->ktp,
                    "phone_number" => (string)$request->phone_number,
                    "tempat_lahir" => (string)$request->tempat_lahir,
                    "alamat" => (string)$request->alamat,
                    "password" => (string)$request->password,
                    "image" => (string)$request->file_image,
                    "ktp_image" => (string)$request->file_ktp_image,
                    "konfirmasi_by" => 0,
                ]);
                return response()->json([
                    'status' => 1,
                    'message' => 'Berhasil melakukan registrasi !'
                ]);
            }
        }

    }

    public function get_dibayar_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "bayar")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_cek_expired($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user','t.transaction_id as order_id')
        ->where('t.id_user', $id)
        ->where('t.status', "bayar")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            't.transaction_id as order_id',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_lunas_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "lunas")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_dipinjam_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "dipinjam")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_selesai_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "selesai")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');

                $cekRating = DB::table('rating_transaksi')->where('id_transaksi',$transaction->id)->count();

                if($cekRating > 0){
                    $detail->isRating = "true";
                }else{
                    $detail->isRating = "false";
                }

                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }

        }


        return response()->json($transactionsDetails);
    }

    public function get_tolak_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "tolak")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_expired_transaksi($id){
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select('t.id', 't.status', 't.total_harga', 't.id_user')
        ->where('t.id_user', $id)
        ->where('t.status', "kadaluarsa")
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
                        ->join('product as p', 'p.id', '=', 'dt.id_produk')
                        ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
                        ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
                        ->select(
                            't.id',
                            'su.name as nama_mitra',
                            't.status',
                            'p.image',
                            'p.nama_produk',
                            'dt.id_transaksi',
                            'dt.jumlah',
                            'p.harga',
                            'dt.sub_total as sub_harga',
                            't.total_harga as total_all_produk')
                        ->where('dt.id_transaksi', $transaction->id)
                        ->limit(1)
                        ->first();

                        $jumlah_all = DB::table('detail_transaksi')
                        ->where('id_transaksi', $transaction->id)
                        ->sum('jumlah');


                if ($detail) {
                    $detail->jumlah_all_produk = (int)$jumlah_all;
                    $namaGambar = $detail->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $detail->image = $gambarUrl;

                    $transactionsDetails[] = $detail;
                }
        }

        return response()->json($transactionsDetails);
    }

    public function get_list_produk(Request $request)
    {
        $type = $request->type;
        $message = $request->message;
        if($type == "search"){
            $data = DB::table('product as p')
            ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
            ->join('category as c', 'c.id', '=', 'p.id_category')
            ->join('st_user as s', 's.id', '=', 'p.id_mitra')
            ->latest('p.created_date')
            ->where('p.nama_produk', 'like', "%{$message}%")
            ->orWhere('c.name', 'like', "%{$message}%")
            ->orWhere('s.name', 'like', "%{$message}%")
            ->get();

            $modifiedData = [];
            foreach ($data as $produk) {
                $namaGambar = $produk->image;
                $sumRating = DB::table('rating')
                ->where('id_produk', $produk->id)
                ->sum('rating');

                $sumRatingBagi = DB::table('rating')
                ->where('id_produk',$produk->id)
                ->count();

                if($sumRatingBagi != 0){
                    $produk->rating = $sumRating / $sumRatingBagi;
                }else{
                    $produk->rating = 0;
                }
                $gambarUrl = asset('storage/produk/' . $namaGambar);
                $produk->gambar_url = $gambarUrl;
                $modifiedData[] = $produk;
            }
        }
        elseif($type == "filter"){
            if($message == "terlama"){
                $data = DB::table('product as p')
                ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
                ->join('category as c', 'c.id', '=', 'p.id_category')
                ->oldest('p.created_date')
                ->get();

                $modifiedData = [];
                foreach ($data as $produk) {
                    $namaGambar = $produk->image;
                    $sumRating = DB::table('rating')
                    ->where('id_produk', $produk->id)
                    ->sum('rating');

                    $sumRatingBagi = DB::table('rating')
                    ->where('id_produk',$produk->id)
                    ->count();

                    if($sumRatingBagi != 0){
                        $produk->rating = $sumRating / $sumRatingBagi;
                    }else{
                        $produk->rating = 0;
                    }
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $produk->gambar_url = $gambarUrl;
                    $modifiedData[] = $produk;
                }
            }elseif($message == "terbaru"){
                $data = DB::table('product as p')
                ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
                ->join('category as c', 'c.id', '=', 'p.id_category')
                ->join('st_user as s', 's.id', '=', 'p.id_mitra')
                ->latest('p.created_date')
                ->get();

                $modifiedData = [];
                foreach ($data as $produk) {
                    $namaGambar = $produk->image;
                    $sumRating = DB::table('rating')
                    ->where('id_produk', $produk->id)
                    ->sum('rating');

                    $sumRatingBagi = DB::table('rating')
                    ->where('id_produk',$produk->id)
                    ->count();

                    if($sumRatingBagi != 0){
                        $produk->rating = $sumRating / $sumRatingBagi;
                    }else{
                        $produk->rating = 0;
                    }
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $produk->gambar_url = $gambarUrl;
                    $modifiedData[] = $produk;
                }
            }else{
                $data = DB::table('product as p')
                ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
                ->join('category as c', 'c.id', '=', 'p.id_category')
                ->join('st_user as s', 's.id', '=', 'p.id_mitra')
                ->get();

                $modifiedData = [];
                foreach ($data as $produk) {
                    $namaGambar = $produk->image;
                    $sumRating = DB::table('rating')
                    ->where('id_produk', $produk->id)
                    ->sum('rating');

                    $sumRatingBagi = DB::table('rating')
                    ->where('id_produk',$produk->id)
                    ->count();

                    if($sumRatingBagi != 0){
                        $produk->rating = $sumRating / $sumRatingBagi;
                    }else{
                        $produk->rating = 0;
                    }
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $produk->gambar_url = $gambarUrl;
                    $modifiedData[] = $produk;
                }
            }
        }
        elseif($type == "filter_categori"){
            $data = DB::table('product as p')
            ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
            ->join('category as c', 'c.id', '=', 'p.id_category')
            ->join('st_user as s', 's.id', '=', 'p.id_mitra')
            ->latest('p.created_date')
            ->where('c.name', $message)
            ->get();

            $modifiedData = [];
            foreach ($data as $produk) {
                $namaGambar = $produk->image;
                $sumRating = DB::table('rating')
                ->where('id_produk', $produk->id)
                ->sum('rating');

                $sumRatingBagi = DB::table('rating')
                ->where('id_produk',$produk->id)
                ->count();

                if($sumRatingBagi != 0){
                    $produk->rating = $sumRating / $sumRatingBagi;
                }else{
                    $produk->rating = 0;
                }
                $gambarUrl = asset('storage/produk/' . $namaGambar);
                $produk->gambar_url = $gambarUrl;
                $modifiedData[] = $produk;
            }
        }else{
            $data = DB::table('product as p')
                ->select('p.id', 'p.id_mitra','s.name as nama_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
                ->join('category as c', 'c.id', '=', 'p.id_category')
                ->join('st_user as s', 's.id', '=', 'p.id_mitra')
                ->get();

                $modifiedData = [];
                foreach ($data as $produk) {
                    $namaGambar = $produk->image;
                    $sumRating = DB::table('rating')
                    ->where('id_produk', $produk->id)
                    ->sum('rating');

                    $sumRatingBagi = DB::table('rating')
                    ->where('id_produk',$produk->id)
                    ->count();

                    if($sumRatingBagi != 0){
                        $produk->rating = $sumRating / $sumRatingBagi;
                    }else{
                        $produk->rating = 0;
                    }
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $produk->gambar_url = $gambarUrl;
                    $modifiedData[] = $produk;
                }
        }

        return response()->json($modifiedData);
    }

    public function get_category_spinner(){
        $data = DB::table('category')->get();
        return response()->json($data);
    }

    public function get_detail_mitra($id){
        $data = DB::table('product as p')
        ->select('s.name', DB::raw("COUNT(p.id) as total_products"))
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->join('st_user as s', 's.id', '=', 'p.id_mitra')
        ->where('p.id_mitra', '=', $id)
        ->groupBy('s.name')
        ->first();

        $data2 = DB::table('product as p')
        ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->join('st_user as s', 's.id', '=', 'p.id_mitra')
        ->where('p.id_mitra',$id)
        ->latest('p.created_date')
        ->get();

        $modifiedData = [
            "nama_mitra" => $data->name,
            "total_products" => $data->total_products,
            "produk" => []
        ];
        foreach ($data2 as $produk) {
            $namaGambar = $produk->image;
            $sumRating = DB::table('rating')
            ->where('id_produk', $produk->id)
            ->sum('rating');

            $sumRatingBagi = DB::table('rating')
            ->where('id_produk',$produk->id)
            ->count();

            if($sumRatingBagi != 0){
                $produk->rating = $sumRating / $sumRatingBagi;
            }else{
                $produk->rating = 0;
            }
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData["produk"][] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_detail_history(Request $request){
        $id = (int)$request->id;
        $transaction_id = (int)$request->transaction_id;
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select(
            't.id',
            't.id_user',
            't.transaction_id',
            't.status',
            't.tgl_booking',
            't.tgl_pinjam',
            't.tgl_tenggat',
            't.tgl_terverifikasi',
            't.tgl_terima',
            't.tgl_transaksi',
            't.tgl_selesai',
            't.metode_pembayaran',
            't.status_bayar',
            't.total_harga',
            't.tanggal_expire',
            't.va_number'
        )
        ->where('t.id_user', $id)
        ->where('t.id', $transaction_id)
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
            ->join('product as p', 'p.id', '=', 'dt.id_produk')
            ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
            ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
            ->select(
                't.id',
                'p.image',
                'p.nama_produk',
                'dt.jumlah',
                'dt.sub_total')
            ->where('dt.id_transaksi', $transaction->id)
            ->get();

            foreach ($detail as $key => $value) {
                if ($detail) {
                    $namaGambar = $value->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $value->image = $gambarUrl;
                }
            }
            // dd($detail);

            $transactionsDetails []= [
                "id" => $transaction->id,
                "id_user" => $transaction->id_user,
                "status" => $transaction->status,
                "transaction_id" => $transaction->transaction_id,
                "tgl_booking" => $transaction->tgl_booking,
                "tgl_pinjam" => $transaction->tgl_pinjam,
                "tgl_tenggat" => $transaction->tgl_tenggat,
                "tgl_terverifikasi" => $transaction->tgl_terverifikasi,
                "tgl_terima" => $transaction->tgl_terima,
                "tgl_transaksi" => $transaction->tgl_transaksi,
                "tgl_selesai" => $transaction->tgl_selesai,
                "tgl_selesai" => $transaction->tgl_selesai,
                "metode_pembayaran" => $transaction->metode_pembayaran,
                "status_bayar" => $transaction->status_bayar,
                "total_harga" => $transaction->total_harga,
                "tanggal_expire" => $transaction->tanggal_expire,
                "va_number" => $transaction->va_number,
                "product" => $detail
            ];



        }

        return response()->json($transactionsDetails);
    }

    public function get_produk_rating(Request $request){
        $id = (int)$request->id;
        $transaction_id = (int)$request->transaction_id;
        $data = DB::table('transaksi as t')
        ->join('st_user as s', 's.id', '=', 't.id_mitra')
        ->select(
            't.id',
            't.id_user'
        )
        ->where('t.id_user', $id)
        ->where('t.id', $transaction_id)
        ->get();
        $transactionsDetails = [];

        foreach ($data as $transaction) {
            $detail = DB::table('detail_transaksi as dt')
            ->join('product as p', 'p.id', '=', 'dt.id_produk')
            ->join('transaksi as t', 't.id', '=', 'dt.id_transaksi')
            ->join('st_user as su', 'su.id', '=', 'p.id_mitra')
            ->select(
                't.id',
                'dt.id_produk',
                'p.image',
                'p.nama_produk',
                'dt.jumlah',
                'dt.sub_total')
            ->where('dt.id_transaksi', $transaction->id)
            ->get();

            foreach ($detail as $key => $value) {
                if ($detail) {
                    $namaGambar = $value->image;
                    $gambarUrl = asset('storage/produk/' . $namaGambar);
                    $value->image = $gambarUrl;
                }
            }
            // dd($detail);

            $transactionsDetails []= [
                "id" => $transaction->id,
                "id_user" => $transaction->id_user,
                "product" => $detail
            ];



        }

        return response()->json($transactionsDetails);
    }

    public function create_data_rating(Request $request){
        $datanya = json_decode($request->input('data'), true);

        try {
            $transaksi = DB::table('rating_transaksi')->insertGetId([
                'id_user' => (int)$request->id_user,
                'id_transaksi' => (int)$request->id_transaksi
            ]);

            foreach ($datanya as $item) {
                $data = [
                    "id_rating_transaksi" => $transaksi,
                    "id_user" => (int)$request->id_user,
                    "id_produk" => (int)$item["id_produk"],
                    "rating" => $item["rating"],
                    "deskripsi" => $item["deskripsi"]
                ];
                DB::table('rating')->insert($data);
            }

            return response()->json([
                "success" => true,
                'message' => 'Terima Kasih telah memberikan penilaian !'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'message' => 'Gagal memberikan rating produk: ' . $e->getMessage()
            ], 500);
        }
    }

    public function get_rekomendasi_produk(Request $request){
        $data = DB::table('product as p')
        ->select('p.id', 'p.id_mitra', 'c.name as id_category', 'p.nama_produk', 'p.image', 'p.type', 'p.harga', 'p.stok', 'p.deskripsi')
        ->join('category as c', 'c.id', '=', 'p.id_category')
        ->latest('p.created_date')
        ->limit(3)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $sumRating = DB::table('rating')
            ->where('id_produk', $produk->id)
            ->sum('rating');

            $sumRatingBagi = DB::table('rating')
            ->where('id_produk',$produk->id)
            ->count();

            if($sumRatingBagi != 0){
                $produk->rating = $sumRating / $sumRatingBagi;
            }else{
                $produk->rating = 0;
            }
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_detail_rating($id){
        $data = DB::table('rating as r')
        ->select('r.id', 's.name as nama_user','r.rating', 'r.id_produk', 'r.id_user', 'r.deskripsi','p.nama_produk','p.image')
        ->join('product as p', 'p.id', '=', 'r.id_produk')
        ->join('st_user as s', 's.id', '=', 'r.id_user')
        ->where('r.id_produk',$id)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_detail_ulasan($id){
        $data = DB::table('rating as r')
        ->select('r.id', 's.name as nama_user','r.rating', 'r.id_produk', 'r.id_user', 'r.deskripsi','p.nama_produk','p.image')
        ->join('product as p', 'p.id', '=', 'r.id_produk')
        ->join('st_user as s', 's.id', '=', 'r.id_user')
        ->where('r.id_user',$id)
        ->get();

        $modifiedData = [];
        foreach ($data as $produk) {
            $namaGambar = $produk->image;
            $gambarUrl = asset('storage/produk/' . $namaGambar);
            $produk->gambar_url = $gambarUrl;
            $modifiedData[] = $produk;
        }
        return response()->json($modifiedData);
    }

    public function get_profile($id){
        $data = DB::table('st_user')
        ->select(
            'id',
            'name',
            'email',
            'phone_number',
            'tempat_lahir',
            'image',
            'ktp',
            'username',
            'ktp_image',
            'tanggal_lahir',
            'alamat',
        )
        ->where('id',$id)
        ->get();

        $modifiedData = [];
        foreach ($data as $user) {
            $namaGambar = $user->image;
            if($namaGambar != "" && $namaGambar != null && $namaGambar != "null"){
                $gambarUrl = asset('storage/user/' . $namaGambar);
                $user->image_url = $gambarUrl;
            }else{
                $user->image_url = null;
            }
            $namaGambar2 = $user->ktp_image;
            if($namaGambar2 != "" && $namaGambar2 != null && $namaGambar2 != "null"){
                $gambarUrl2 = asset('storage/ktp/' . $namaGambar2);
                $user->ktp_image_url = $gambarUrl2;
            }else{
                $user->ktp_image_url = null;
            }
            $modifiedData[] = $user;
        }
        return response()->json($modifiedData);
    }

    public function update_profile(Request $request){

        $validasi = Validator::make($request->all(),[
            'name' => 'required',
            'username' => 'required',
            'email' => 'required|email',
            'ktp' => 'required',
            'phone_number' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
        ]);

        if($validasi->fails()){
            $errors = $validasi->errors()->all();

            return response()->json([
                'status' => 0,
                'message' => 'Harap menyesuaikan emailnya !'
            ]);
        }else{

            $cekEmailFound = DB::table('st_user')->whereNot("id",(int)$request->id)->where("email",(string)$request->email)->count();

            if($cekEmailFound > 0){
                return response()->json([
                    'status' => 0,
                    'message' => 'Maaf email sudah terdaftar !'
                ]);
            }else{
                if(!is_null($request->input('file_image'))){
                    $cekGambar = DB::table('st_user')->where("id",(int)$request->id)->first();
                    if($cekGambar->image != null){
                        $gambar_path = public_path("storage/user/{$cekGambar->image}");
                        if (file_exists($gambar_path)) {
                            unlink($gambar_path);
                        }
                    }
                    $file_p = $request->input('file_image');
                    Storage::disk('public')->put('user/'.$file_p,base64_decode($request->image));
                    $user = DB::table('st_user')->where("id",$request->id)->update([
                        "name" => (string)$request->name,
                        "username" => (string)$request->username,
                        "email" => (string)$request->email,
                        "ktp" => (string)$request->ktp,
                        "phone_number" => (string)$request->phone_number,
                        "tempat_lahir" => (string)$request->tempat_lahir,
                        "tanggal_lahir" => (string)$request->tanggal_lahir,
                        "alamat" => (string)$request->alamat,
                        "image" => (string)$request->file_image,
                    ]);
                }elseif(!is_null($request->input('file_ktp_image'))){
                    $cekGambar = DB::table('st_user')->where("id",(int)$request->id)->first();
                    if($cekGambar->ktp_image != null){
                        $gambar_path = public_path("storage/ktp/{$cekGambar->ktp_image}");
                        if (file_exists($gambar_path)) {
                            unlink($gambar_path);
                        }
                    }
                    $file_k = $request->input('file_ktp_image');
                    Storage::disk('public')->put('ktp/'.$file_k,base64_decode($request->ktp_image));
                    $user = DB::table('st_user')->where("id",$request->id)->update([
                        "name" => (string)$request->name,
                        "username" => (string)$request->username,
                        "email" => (string)$request->email,
                        "ktp" => (string)$request->ktp,
                        "phone_number" => (string)$request->phone_number,
                        "tempat_lahir" => (string)$request->tempat_lahir,
                        "tanggal_lahir" => (string)$request->tanggal_lahir,
                        "alamat" => (string)$request->alamat,
                        "ktp_image" => (string)$request->file_ktp_image
                    ]);
                }elseif(!is_null($request->input('file_image')) && !is_null($request->input('file_ktp_image'))){
                    $cekGambar = DB::table('st_user')->where("id",(int)$request->id)->first();
                    if($cekGambar->image != null){
                        $gambar_path = public_path("storage/user/{$cekGambar->image}");
                        if (file_exists($gambar_path)) {
                            unlink($gambar_path);
                        }
                    }
                    if($cekGambar->ktp_image != null){
                        $gambar_path = public_path("storage/ktp/{$cekGambar->ktp_image}");
                        if (file_exists($gambar_path)) {
                            unlink($gambar_path);
                        }
                    }
                    $file_p = $request->input('file_image');
                    $file_k = $request->input('file_ktp_image');
                    Storage::disk('public')->put('user/'.$file_p,base64_decode($request->image));
                    Storage::disk('public')->put('ktp/'.$file_k,base64_decode($request->ktp_image));
                    $user = DB::table('st_user')->where("id",$request->id)->update([
                        "name" => (string)$request->name,
                        "username" => (string)$request->username,
                        "email" => (string)$request->email,
                        "ktp" => (string)$request->ktp,
                        "phone_number" => (string)$request->phone_number,
                        "tempat_lahir" => (string)$request->tempat_lahir,
                        "tanggal_lahir" => (string)$request->tanggal_lahir,
                        "alamat" => (string)$request->alamat,
                        "image" => (string)$request->file_image,
                        "ktp_image" => (string)$request->file_ktp_image
                    ]);
                }else{
                    $user = DB::table('st_user')->where("id",$request->id)->update([
                        "name" => (string)$request->name,
                        "username" => (string)$request->username,
                        "email" => (string)$request->email,
                        "ktp" => (string)$request->ktp,
                        "phone_number" => (string)$request->phone_number,
                        "tempat_lahir" => (string)$request->tempat_lahir,
                        "tanggal_lahir" => (string)$request->tanggal_lahir,
                        "alamat" => (string)$request->alamat,
                    ]);
                }
                return response()->json([
                    'status' => 1,
                    'message' => 'Berhasil melakukan update profile !'
                ]);
            }
        }

    }

    public function update_password(Request $request){
        $id = (int)$request->id;
        $password = $request->password;

        try {
            DB::table('st_user')
            ->where('id', $id)
            ->update(['password' => $password]);

            return response()->json([
                'status' => 1,
                'message' => 'Berhasil melakukan update password!'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 0,
                'message' => 'Gagal melakukan update profile !'
            ]);
        }
    }
}

