<?php

namespace App\Http\Controllers\Admin\Utilitas;

use App\Http\Controllers\Controller;
use App\Models\LogModel;
use App\Models\LogWhatsappsModel;
use App\Models\master_data\mst_kelas;
use App\Models\master_data\mst_post;
use App\Models\master_data\mst_siswa;
use App\Models\master_data\mst_thn_aka;
use App\Models\MstKelasModel;
use App\Models\MstThnAkaModel;
use App\Models\no;
use App\Models\scctbill;
use App\Models\ScctBillDetailModel;
use App\Models\ScctBillModel;
use App\Models\UAkunModel;
use App\Models\ValidationMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\select;

class NotifikasiWhatsappTagihanController extends Controller
{

    public $mainTitle;

    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitle = 'Tagihan Siswa';
        $this->dataTitle = 'Data Tagihan';
        $this->showTitle = 'Detail Data Tagihan';
    }

    public function getColumn()
    {
        return [
            ['data' => 'no', 'name' => 'no'],
            ['data' => 'nis', 'name' => 'NIS', 'searchable' => true, 'orderable' => true],
            ['data' => 'nama', 'name' => 'NAMA', 'searchable' => true, 'orderable' => true],
            ['data' => 'NO_WA', 'name' => 'Nomor WhatsApp', 'searchable' => true, 'orderable' => true],
            ['data' => 'PAIDST', 'name' => 'Status', 'orderable' => true, 'columnType' => 'boolean', 'trueVal' => 'Dibayar', 'falseVal' => 'Belum Dibayar'],
            ['data' => 'BTA', 'name' => 'Tahun AKA', 'searchable' => true, 'orderable' => true],

            // ['data' => 'nama_post', 'name' => 'Nama POST', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLNM', 'name' => 'Nama Tagihan', 'searchable' => true, 'orderable' => true],
            ['data' => 'BILLAM', 'name' => 'Tagihan', 'searchable' => true, 'orderable' => true, 'columnType' => 'currency', 'className' => 'text-end'],
            ['data' => 'FUrutan', 'name' => 'Urutan', 'searchable' => true, 'orderable' => true],
        ];
    }

    public function getKelas(Request $request)
    {
        $jenjang = $request->input('jenjang');
        $unit = $request->input('unit');

        $kelas = MstKelasModel::where('jenjang', $jenjang)
            ->where('unit', $unit)
            ->get();
        return response()->json($kelas);
    }

    public function getData(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnName_arr = $request->get('columns');
        $search_arr = $request->get('search');

        $defaultColumn = 'scctbill.AA';
        $defaultOrder = 'desc';

        if ($request->has('order')) {
            $columnIndex_arr = $request->get('order');
            $columnIndex = $columnIndex_arr[0]['column'];
            $columnSortOrder = $columnIndex_arr[0]['dir'];
        } else {
            $columnIndex = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        $columnName = $columnName_arr[$columnIndex]['data'];
        $searchValue = $search_arr['value'];

        if (!$columnName || $columnName == 'no') {
            $columnName = $defaultColumn;
            $columnSortOrder = $defaultOrder;
        }

        $filters = [];
        $filterQuery = null;

        $filter = $request->input('filter');

        if ($request->status_tagihan == 1) {
            $groupBy = 'scctbill.AA';
        } else {
            $groupBy = 'scctcust.NOCUST';
            $defaultColumn = 'scctbill.FUrutan';
            $defaultOrder = 'asc';
        }
        if ($filter) {
            // dd($filter);
            foreach ($filter as $key => $val) {
                if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        'tahun_akademik' => 'scctbill.BTA',
                        'cari_siswa' => is_numeric($val) ? 'scctcust.NOCUST' : 'scctcust.NMCUST',
                        default => null
                    };

                    ($colName) && $filters[] =  $colName == 'scctcust.NMCUST' || 'mst_siswas.nis' ?  [$colName, 'LIKE', "%" . $val . "%"] : [$colName, '=', $val];
                }
            };
        }
        if (isset($request->unit)) {
            $waduh = explode('-', $request->unit);
            $jenjang = $waduh[0];
            $unit = $waduh[1];
            $kelas = $request->kelas;
            $filters[] = ['scctcust.DESC02', '=', $jenjang];
            $filters[] = ['scctcust.CODE02', '=', $unit];
            $filters[] = ['scctcust.DESC03', '=', $kelas];
        }

        if (!empty($filters)) {
            $filterQuery = function ($query) use ($filters) {
                foreach ($filters as $filter) {
                    if (count($filter) === 3) {
                        $query->where($filter[0], $filter[1], $filter[2]);
                    } elseif (count($filter) === 4) {
                        $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                    }
                }
            };
        }

        if (count($filters) < 1) {
            $totalRecords = 0;
            $totalRecordswithFilter = 0;
            $records = [];
        } else {
            $totalRecords = ScctBillModel::select('count(*) as allcount')->where('scctbill.PAIDST', 0)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->count();
            $totalRecordswithFilter = ScctBillModel::select('count(*) as allcount')
                ->leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.PAIDST', 0)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->whereAny([
                    'scctcust.NMCUST',
                    'scctcust.NOCUST',
                    'scctcust.DESC02',
                    'scctcust.DESC03',
                    'scctcust.CODE02',
                    'scctcust.NO_WA'
                ], 'like', '%' . $searchValue . '%')
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })
                ->count();

            $records =
                ScctBillModel::orderBy($columnName, $columnSortOrder)
                ->leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.PAIDST', 0)
                ->where('scctbill.FSTSBolehBayar', 1)
                ->select([
                    'scctbill.AA',
                    'scctbill.BILLNM',
                    'scctbill.BILLAM',
                    'scctbill.PAIDST',
                    'scctbill.BTA',
                    'scctbill.FIDBANK',
                    'scctbill.FUrutan',
                    // 'scctbill.KodePost',
                    'scctcust.NMCUST AS nama',
                    'scctcust.NOCUST AS nis',
                    'scctcust.NO_WA'
                ])
                ->whereAny([
                    'scctcust.NMCUST',
                    'scctcust.NOCUST',
                ], 'like', '%' . $searchValue . '%')
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })
                ->groupBy($groupBy)
                ->skip($start)
                ->take($rowperpage)
                ->get()
                ->map(function ($item, $index) {
                    $item->no = $index + 1;
                    $item->item_id = Crypt::encrypt($item->id);
                    unset($item->id);
                    return $item;
                })->toArray();
        }


        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $totalRecordswithFilter,
            "data" => $records,
        );
        return response()->json($response);
    }

    public function index()
    {
        $post = UAkunModel::all();
        $thn_aka = MstThnAkaModel::where('thn_aka', '!=', null)->orderBy('thn_aka', 'desc')->get();
        // $kelas = MstKelasModel::get();

        $kelas = MstKelasModel::select('jenjang', 'unit')
            ->distinct()
            ->orderBy('unit')
            ->get();

        $data['post'] = $post;
        $data['thn_aka'] = $thn_aka;
        $data['kelas'] = $kelas;
        $data['title'] = $this->title;
        $data['mainTitle'] = $this->mainTitle;
        $data['dataTitle'] = $this->dataTitle;
        $data['showTitle'] = $this->showTitle;
        $data['columnsUrl'] = route('admin.notifikasi-whatsapp-tagihan.get-column');
        $data['datasUrl'] = route('admin.notifikasi-whatsapp-tagihan.get-data');
        // $data['modalLink'] = view('admin.utilitas.notifikasi_whatsapp_tagihan.modal', compact('post'));

        return view('admin.utilitas.notifikasi_whatsapp_tagihan.index', $data);
    }
    public function sendWhatsapp(Request $request)
    {
        // dd($request);
        set_time_limit(500);
        $filters = [];
        $filterQuery = null;
        // $search_arr = $request->get('search');
        // $searchValue = $search_arr['value'];
        $filter = $request->input('filter');

        if ($filter) {
            // dd($filter);
            foreach ($filter as $key => $val) {
                if (strtolower($val) != 'all' && $val !== null && $val !== '') {
                    $colName = match ($key) {
                        'tahun_akademik' => 'scctbill.BTA',
                        'cari_siswa' => is_numeric($val) ? 'scctcust.NOCUST' : 'scctcust.NMCUST',
                        default => null
                    };

                    ($colName) && $filters[] =  $colName == 'scctcust.NMCUST' || 'mst_siswas.nis' ?  [$colName, 'LIKE', "%" . $val . "%"] : [$colName, '=', $val];
                }
            };
        }
        if (isset($request->unit)) {
            $waduh = explode('-', $request->unit);
            $jenjang = $waduh[0];
            $unit = $waduh[1];
            $kelas = $request->kelas;
            $filters[] = ['scctcust.DESC02', '=', $jenjang];
            $filters[] = ['scctcust.CODE02', '=', $unit];
            $filters[] = ['scctcust.DESC03', '=', $kelas];
        }

        if (!empty($filters)) {
            $filterQuery = function ($query) use ($filters) {
                foreach ($filters as $filter) {
                    if (count($filter) === 3) {
                        $query->where($filter[0], $filter[1], $filter[2]);
                    } elseif (count($filter) === 4) {
                        $query->{$filter[3]}($filter[0], $filter[1], $filter[2]);
                    }
                }
            };
        }


        if ($request->status_tagihan == 1) {
            $records = ScctBillModel::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.PAIDST', 0)
                ->whereNotNull('scctcust.NO_WA')
                ->where('scctbill.FSTSBolehBayar', 1)
                ->select([
                    'scctbill.AA',
                    'scctbill.BILLNM',
                    // 'scctbill.BILLAM',
                    DB::raw('SUM(scctbill.BILLAM) as BILLAM'),
                    'scctbill.PAIDST',
                    'scctbill.BTA',
                    'scctbill.FIDBANK',
                    'scctbill.FUrutan',
                    'scctcust.NMCUST',
                    'scctcust.NOCUST',
                    'scctcust.CUSTID',
                    'scctcust.NO_WA',
                ])
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })
                ->groupBy('scctcust.CUSTID')
                ->get();
            if ($records->isEmpty()) return response()->json(['message' => 'Tidak ada siswa di angkatan ini'], 422);
        } else {
            $records =
                ScctBillModel::leftJoin('scctcust', 'scctcust.CUSTID', 'scctbill.CUSTID')
                ->where('scctbill.PAIDST', 0)
                ->whereNotNull('scctcust.NO_WA')
                ->where('scctbill.FSTSBolehBayar', 1)
                ->select([
                    'scctbill.AA',
                    'scctbill.BILLNM',
                    'scctbill.BILLAM',
                    'scctbill.PAIDST',
                    'scctbill.BTA',
                    'scctbill.FIDBANK',
                    'scctbill.FUrutan',
                    'scctcust.NMCUST',
                    'scctcust.NOCUST',
                    'scctcust.NO_WA',
                    'scctcust.CUSTID',
                ])
                ->where(function ($query) use ($filterQuery) {
                    if ($filterQuery) {
                        $filterQuery($query);
                    }
                })
                ->groupBy('scctcust.CUSTID')
                ->orderBy('scctbill.FUrutan', 'desc')
                ->get();
            if ($records->isEmpty()) return response()->json(['message' => 'Tidak ada siswa di angkatan ini'], 422);
        };
        $payload = [
            "api_key" => "1FOPYD2SA8VPIU4Q",
            "number_key" => "3eF1CHDzjLi35eE2",
        ];

        if ($records->count() >= 100) {
            return response()->json(['message' => 'jumlah siswa yang dipilih tidak boleh lebih dari 100'], 413);
        }
        set_time_limit(500);
        $log = new LogModel();
        $log->user_id =  auth()->check() ? auth()->user()->id : null;
        $log->menu =  'Whatsapp Tagihan';
        $log->aksi =  'Kirim Whatsapp Tagihan';
        $log->client_info =  $request->server('HTTP_USER_AGENT');
        $log->target_id =  'Kirim Whatsapp Tagihan';
        $log->ip_address =   $request->ip();
        $log->status =  "kirim whatsapp";
        $log->save();

        $idLog = $log->id;

        $url = 'https://api.watzap.id/v1/send_message';
        foreach ($records as $siswa) {
            try {
                // $messages = [
                //     "Assalamualaikum Wr Wb,
                //     \nSalam sejahtera bagi kita semua. Kami ingin menginformasikan kepada Anda, orang tua ananda {nama_anak}, untuk tagihan anak Anda sebesar {jumlah_tagihan}.
                //     \nDengan Rincian :{rincian}\nDemikian pesan dari kami. Wassalam 🙏.\n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

                //     "Selamat pagi/siang/malam,Bapak/Ibu {nama_orang_tua}. Kami ingin mengingatkan bahwa tagihan untuk ananda {nama_anak} sebesar {jumlah_tagihan} sudah jatuh tempo.
                //     \nRincian Tagihan :{rincian}\nTerima kasih atas perhatiannya. Salam hormat dari kami 🙏.\n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

                //     " *_*Friendly Rimender From " . config('app.nama_instansi') . "_*\n\nPermisi Bapak/Ibu {nama_orang_tua}, berikut ini merupakan pesan pengingat untuk tagihan sekolah yang dimiliki ananda {nama_anak} yang berjumlah {jumlah_tagihan}.
                //     \nDengan Rincian Tagihannya :{rincian}\nKami harap Bapak/Ibu dapat segera melunaskan beban tagihan tersebut. Terimakasih Wassalamualaikum wr wb 🙏.\n\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

                //     "Dengan hormat, kami sampaikan kepada Bapak/Ibu {nama_orang_tua}, bahwa tagihan untuk ananda {nama_anak} sebesar {jumlah_tagihan} sudah harus dibayarkan.
                //     \nRincian :{rincian}\nTerima kasih atas perhatian dan kerjasamanya. Wassalam 🙏.\n\n*_pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*"
                // ];
                $messages = [
                    "Assalamu'alaikum Warahmatullahi wabarakatuh
                    \nKami dari keuangan Ibnu Abbas 
                    \nMemberitahukan kepada wali santri atas nama {nama_anak} 
                    \nMasih ada kekurangan pembayaran sebesar {jumlah_tagihan}, dengan rincian :
                    {rincian}
                    \nMohon untuk segera di tunaikan, atas perhatiannya kami sampaikan
                    \nJazaakumullah khairan katsiran",

                    "-السلام عليكم ورحمة الله وبركاته-
                    \n\nKami dari Ibnu Abbas, memberitahukan kepada wali santri atas nama {nama_anak} 
                    \nAnanda masih memiliki kekurangan pembayaran sebesar {jumlah_tagihan}, dengan rincian :
                    \n\n......\n 
                    \nMohon untuk segera di tunaikan, atas perhatiannya kami sampaikan
                    \nJazaakumullah khairan katsiran",

                    "Dengan hormat, kami sampaikan kepada Bapak/Ibu {nama_orang_tua}, bahwa tagihan untuk ananda {nama_anak} sebesar {jumlah_tagihan} sudah harus dibayarkan.
                    \nRincian :{rincian}\nTerima kasih atas perhatian dan kerjasamanya. Wassalam 🙏.\n\n*_pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

                    "Assalamualaikum Wr Wb,
                    \nSalam sejahtera bagi kita semua. Kami ingin menginformasikan kepada Anda, orang tua ananda {nama_anak}, untuk tagihan anak Anda sebesar {jumlah_tagihan}.
                    \nDengan Rincian :{rincian}\nDemikian pesan dari kami. Wassalam 🙏.\n\n*_*pesan otomatis dari " . config('app.nama_instansi') . "_*\n*_*silahkan hubingi admin sekolah jika ada kesalahan tagihan atau nama siswa_*",

                ];
                $randomArray = Arr::random($messages);

                if ($request->status_tagihan == 1) {
                    $rincian = ScctBillModel::where('CUSTID', $siswa->CUSTID)
                        ->where('PAIDST', 0)
                        ->where('scctbill.FSTSBolehBayar', 1)
                        ->get();
                } else {
                    $rincian = ScctBillModel::where('CUSTID', $siswa->CUSTID)
                        ->where('PAIDST', 0)
                        ->where('scctbill.FSTSBolehBayar', 1)
                        ->groupBy('scctbill.CUSTID')
                        ->orderBy('scctbill.FUrutan', 'desc')
                        ->get();
                }
                $rincianString = "\n";
                foreach ($rincian as $item) {
                    $rincianString .= "- " . $item->BILLNM . ": Rp *" . number_format($item->BILLAM, 0, ',', '.') . "*\n";
                }
                $jumlah_tagihan = 'Rp *' . number_format((int)$siswa->BILLAM, 0, ',', '.') . '*';
                $message = str_replace(
                    ['{nama_anak}', '{nama_orang_tua}', '{jumlah_tagihan}', '{rincian}'],
                    [$siswa->NMCUST ?? '', $siswa->GENUS, $jumlah_tagihan, $rincianString],
                    $randomArray
                );

                $payload['phone_no'] = $siswa->NO_WA;
                $payload['message'] = $message;

                // $payload['target'] = $siswa->custid;
                // $payload['description'] = $message;

                $jsonPayload = json_encode($payload);
                // dd($jsonPayload);

                $response = Http::withBody($jsonPayload, 'application/json')->post($url);
                Log::error('Wa Response: ' . $response);

                $randomDelay = rand(1100000, 3200000);
                usleep($randomDelay);

                $arrResponse = json_decode($response, true);
                DB::beginTransaction();

                LogWhatsappsModel::create([
                    'custid' => $siswa->CUSTID,
                    'log_id' => $idLog,
                    'user_id' => Auth::id(),
                    'status' => $arrResponse['status'],
                    'no_wa' => $siswa->NO_WA,
                    'pesan' => $message,
                    'nama' => $siswa->NMCUST,
                    'response' => $response
                ]);

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                Log::channel('whatsapp')->error('Payment failed', [
                    'error' => $e->getMessage(),
                    'NOREFF' => "error"
                ]);
                return response()->json(['error' => true, 'message' => $e->getMessage()], 500);
            }
            //untuk mengecek sesuai furutan atau tidak

        }

        return response()->json(['message' => 'Pesan Whatsapp telah dikirimkan!']);
    }
}
