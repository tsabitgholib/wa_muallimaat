<?php

namespace App\Http\Controllers\Admin\Utilitas;

use App\Helpers\Messages;
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
use App\Models\ScctcustModel;
use App\Models\UAkunModel;
use App\Models\ValidationMessage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
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
use Termwind\Components\Dd;

use function Laravel\Prompts\select;

class NotifikasiWhatsappTunggakanController extends Controller
{

    public $mainTitle;

    public function __construct()
    {
        $this->title = 'Keuangan';
        $this->mainTitle = 'Tunggakan Tagihan Siswa';
        $this->dataTitle = 'Data TunggakanTagihan';
        $this->showTitle = 'Detail Data Tunggakan Tagihan';
    }

    public function getColumn()
    {
        return [
            ['data' => 'id', 'name' => 'no', 'columnType' => 'checkbox', 'selectName' => 'scctbill', 'preData' => '', 'selectClass' => 'scctbill'],
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

                    ($colName) && $filters[] =  $colName == 'scctcust.NMCUST' || 'scctcust.NOCUST' ?  [$colName, 'LIKE', "%" . $val . "%"] : [$colName, '=', $val];
                }
            };
        }
        if (isset($request->unit)) {
            if ($request->unit != "Semua") {
                $waduh = explode('-', $request->unit);
                $jenjang = $waduh[0];
                $unit = $waduh[1];
                $kelas = $request->kelas;
                if (isset($kelas)) {
                    $filters[] = ['scctcust.DESC03', '=', $kelas];
                }
                $filters[] = ['scctcust.DESC02', '=', $jenjang];
                $filters[] = ['scctcust.CODE02', '=', $unit];
            }
        }

        $tahun = Carbon::now()->year;
        $bulan = Carbon::now()->format('m');
        $filters[] = ['scctbill.BILLAC', '<', '' . $tahun . $bulan];
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

        if (count($filters) < 2) {
            $totalRecords = 0;
            $totalRecordswithFilter = 0;
            $records = [];
        } else {
            $totalRecords = ScctBillModel::select('count(*) as allcount')->where('scctbill.PAIDST', 0)
                ->where('scctbill.BILLAC', '<', '' . $tahun . $bulan)
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
                    'scctbill.AA as id',
                    'scctbill.BILLNM',
                    'scctbill.BILLAM',
                    'scctbill.PAIDST',
                    'scctbill.BTA',
                    'scctbill.FIDBANK',
                    'scctbill.FUrutan',
                    'scctbill.CUSTID',
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
        $data['columnsUrl'] = route('admin.notifikasi-whatsapp-tunggakan.get-column');
        $data['datasUrl'] = route('admin.notifikasi-whatsapp-tunggakan.get-data');
        // $data['modalLink'] = view('admin.utilitas.notifikasi_whatsapp_tagihan.modal', compact('post'));

        return view('admin.utilitas.notifikasi_whatsapp_tunggakan.index', $data);
    }

    public function sendWhatsapp(Request $request)
    {
        set_time_limit(500);

        $tagihan = $request->tagihan;
        if ($tagihan == null) {
            return response()->json(['error' => true, 'message' => 'tidak ada data yang dipiih'], 422);
        }
        $idSiswaKeys = array_keys($request->tagihan);
        $siswa = ScctcustModel::whereIn('CUSTID', $idSiswaKeys)->select('CUSTID', 'NO_WA', 'NMCUST', 'NOCUST', 'GENUS')->get();
        $payload = [
            "api_key" => "1FOPYD2SA8VPIU4Q",
            "number_key" => "3eF1CHDzjLi35eE2",
        ];

        if ($siswa->count() >= 100) {
            return response()->json(['message' => 'jumlah siswa yang dipilih tidak boleh lebih dari 100'], 413);
        }
        $log = new LogModel();
        $log->user_id =  auth()->check() ? auth()->user()->id : null;
        $log->menu =  'Whatsapp Tunggakan';
        $log->aksi =  'Kirim Whatsapp Tunggakan';
        $log->client_info =  $request->server('HTTP_USER_AGENT');
        $log->target_id =  'Kirim Whatsapp Tunggakan';
        $log->ip_address =   $request->ip();
        $log->status =  "kirim whatsapp";
        $log->save();

        $idLog = $log->id;

        $url = 'https://api.watzap.id/v1/send_message';
        $pesan = "Pesan Whatsapp telah dikirimkan!";
        $siswaPesan = "";

        foreach ($siswa as $siswas) {
            try {
                if ($siswas->NO_WA != null) {
                    $messages = Messages::wa("Tunggakan");
                    $randomArray = Arr::random($messages);

                    $id_tagihan_array = $tagihan[$siswas->CUSTID];

                    $rincian = ScctBillModel::where('CUSTID', $siswas->CUSTID)
                        ->whereIn('AA', $id_tagihan_array)
                        ->where('PAIDST', 0)
                        ->where('scctbill.FSTSBolehBayar', 1)
                        ->get();

                    $totaltagihan = $rincian->sum('BILLAM');

                    $rincianString = "\n";
                    foreach ($rincian as $item) {
                        $rincianString .= "- " . $item->BILLNM . ": Rp *" . number_format($item->BILLAM, 0, ',', '.') . "*\n";
                    }
                    $jumlah_tagihan = 'Rp *' . number_format((int)$totaltagihan, 0, ',', '.') . '*';
                    $message = str_replace(
                        ['{nama_anak}', '{nama_orang_tua}', '{jumlah_tagihan}', '{rincian}'],
                        [$siswas->NMCUST ?? '', $siswas->GENUS, $jumlah_tagihan, $rincianString],
                        $randomArray
                    );

                    $payload['phone_no'] = $siswas->NO_WA;
                    $payload['message'] = $message;
                    $jsonPayload = json_encode($payload);
                    $response = Http::withBody($jsonPayload, 'application/json')->post($url);
                    Log::error('Wa Response: ' . $response);

                    $randomDelay = rand(1100000, 3200000);
                    usleep($randomDelay);

                    $arrResponse = json_decode($response, true);
                    DB::beginTransaction();

                    LogWhatsappsModel::create([
                        'custid' => $siswas->CUSTID,
                        'log_id' => $idLog,
                        'user_id' => Auth::id(),
                        'status' => $arrResponse['status'],
                        'no_wa' => $siswas->NO_WA,
                        'pesan' => $message,
                        'nama' => $siswas->NMCUST,
                        'response' => $response
                    ]);

                    DB::commit();
                } else {
                    $siswaPesan .= $siswas->NMCUST . ", ";
                    $pesan .= " Kecuali " . $siswaPesan;
                }
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
        return response()->json(['message' => $pesan]);
    }
}
