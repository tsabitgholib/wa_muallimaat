<?php

use App\Http\Controllers\MakeRoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/reload-captcha', [App\Http\Controllers\Auth\LoginController::class, 'reloadCaptcha']);

Route::get('/testrole', [\App\Http\Controllers\UserController::class, 'testRole'])->name('test-role');

Auth::routes([
    'register' => false,
]);

Route::get('login_siswa', [\App\Http\Controllers\Auth\LoginController::class, 'loginWithToken'])->name('login-siswa');

Route::get('/', [App\Http\Controllers\Controller::class, 'welcome']);

Route::get('/preview', function () {
    return view('preview');
});
Route::get('make-role', [MakeRoleController::class, 'makeRole']);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Route::get('/', [\App\Http\Controllers\Admin\AdminController::class, 'index'])->name('index');

    Route::resource('profile', \App\Http\Controllers\Admin\ProfileController::class)->names('profile');

    Route::prefix('keuangan')->name('keuangan.')->group(function () {
        Route::prefix('penerimaan-siswa')->name('penerimaan-siswa.')->group(function () {
            Route::prefix('data-penerimaan')->name('data-penerimaan.')->group(function () {
                Route::get('get-data', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class, 'getData'])->name('get-data');
                Route::get('get-column', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class, 'getColumn'])->name('get-column');
                Route::get('cetak-tagihan-dibayar', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class, 'cetakPembayaran'])->name('cetak-tagihan-dibayar');
            });
            Route::resource('data-penerimaan', \App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\DataPenerimaanController::class)->names('data-penerimaan');

            Route::prefix('rekap-penerimaan')->name('rekap-penerimaan.')->group(function () {
                Route::get('get-data', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\RekapPenerimaanController::class, 'getData'])->name('get-data');
                Route::get('get-column', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\RekapPenerimaanController::class, 'getColumn'])->name('get-column');
                Route::get('cetak-rekap', [\App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\RekapPenerimaanController::class, 'cetakRekapPenerimaan'])->name('cetak-rekap');
            });
            Route::resource('rekap-penerimaan', \App\Http\Controllers\Admin\Keuangan\PenerimaanSiswa\RekapPenerimaanController::class)->names('rekap-penerimaan');
        });
    });

    Route::prefix('utilitas')->name('utilitas.')->group(function () {


        Route::prefix('manajemen-user')->name('manajemen-user.')->group(function () {
            Route::get('get-column', [\App\Http\Controllers\Admin\Utilitas\ManajemenUserController::class, 'getColumn'])->name('get-column');
            Route::get('get-data', [\App\Http\Controllers\Admin\Utilitas\ManajemenUserController::class, 'getData'])->name('get-data');
        });

        Route::resource('manajemen-user', \App\Http\Controllers\Admin\Utilitas\ManajemenUserController::class)->names('manajemen-user');

        Route::prefix('notifikasi-whatsapp')->name('notifikasi-whatsapp.')->group(function () {
            Route::get('get-siswa', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappController::class, 'getSiswa'])->name('get-siswa');
            Route::post('send-wa', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappController::class, 'sendWa'])->name('send-wa');
        });

        Route::resource('notifikasi-whatsapp', \App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappController::class)->names('notifikasi-whatsapp');

        Route::prefix('notifikasi-whatsapp-tagihan')->name('notifikasi-whatsapp-tagihan.')->group(function () {
            Route::get('get-data', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class, 'getData'])->name('get-data');
            Route::get('get-column', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class, 'getColumn'])->name('get-column');
            Route::get('cetak', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class, 'cetak'])->name('cetak');
            Route::post('send-wa', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class, 'sendWhatsapp'])->name('send-wa');
            Route::post('get-kelas', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class, 'getKelas'])->name('get-kelas');
        });
        Route::resource('notifikasi-whatsapp-tagihan', \App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTagihanController::class)->names('notifikasi-whatsapp-tagihan');

        Route::prefix('notifikasi-whatsapp-tunggakan')->name('notifikasi-whatsapp-tunggakan.')->group(function () {
            Route::post('get-kelas', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'getKelas'])->name('get-kelas');
            Route::get('get-data', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'getData'])->name('get-data');
            Route::get('get-column', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'getColumn'])->name('get-column');
            Route::get('cetak', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'cetak'])->name('cetak');
            Route::post('send-wa', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'sendWhatsapp'])->name('send-wa');
            Route::post('get-kelas', [\App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class, 'getKelas'])->name('get-kelas');
        });
        Route::resource('notifikasi-whatsapp-tunggakan', \App\Http\Controllers\Admin\Utilitas\NotifikasiWhatsappTunggakanController::class)->names('notifikasi-whatsapp-tunggakan');
    });
});

Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', function () {
        return view('siswa.index');
    })->name('index');
});

Route::get('/logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout']);
