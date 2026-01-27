<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Level 1 - Main Categories
        $asset = Account::create([
            'code' => '1',
            'name' => 'ASET',
            'type' => 'asset',
            'level' => 1,
            'description' => 'Akun-akun aset perusahaan',
        ]);

        $liability = Account::create([
            'code' => '2',
            'name' => 'KEWAJIBAN',
            'type' => 'liability',
            'level' => 1,
            'description' => 'Akun-akun kewajiban/hutang perusahaan',
        ]);

        $equity = Account::create([
            'code' => '3',
            'name' => 'EKUITAS',
            'type' => 'equity',
            'level' => 1,
            'description' => 'Akun-akun ekuitas/modal perusahaan',
        ]);

        $revenue = Account::create([
            'code' => '4',
            'name' => 'PENDAPATAN',
            'type' => 'revenue',
            'level' => 1,
            'description' => 'Akun-akun pendapatan perusahaan',
        ]);

        $expense = Account::create([
            'code' => '5',
            'name' => 'BEBAN',
            'type' => 'expense',
            'level' => 1,
            'description' => 'Akun-akun beban/biaya perusahaan',
        ]);

        // Level 2 - ASET
        $asetLancar = Account::create([
            'code' => '1-1000',
            'name' => 'Aset Lancar',
            'type' => 'asset',
            'parent_id' => $asset->id,
            'level' => 2,
        ]);

        $asetTetap = Account::create([
            'code' => '1-2000',
            'name' => 'Aset Tetap',
            'type' => 'asset',
            'parent_id' => $asset->id,
            'level' => 2,
        ]);

        // Level 3 - Aset Lancar Details
        Account::create([
            'code' => '1-1100',
            'name' => 'Kas',
            'type' => 'asset',
            'parent_id' => $asetLancar->id,
            'level' => 3,
            'description' => 'Kas di tangan',
        ]);

        Account::create([
            'code' => '1-1200',
            'name' => 'Bank',
            'type' => 'asset',
            'parent_id' => $asetLancar->id,
            'level' => 3,
            'description' => 'Kas di bank',
        ]);

        Account::create([
            'code' => '1-1300',
            'name' => 'Piutang Usaha',
            'type' => 'asset',
            'parent_id' => $asetLancar->id,
            'level' => 3,
            'description' => 'Piutang dari pelanggan',
        ]);

        Account::create([
            'code' => '1-1400',
            'name' => 'Persediaan Barang',
            'type' => 'asset',
            'parent_id' => $asetLancar->id,
            'level' => 3,
            'description' => 'Persediaan barang dagangan',
        ]);

        Account::create([
            'code' => '1-1500',
            'name' => 'Perlengkapan',
            'type' => 'asset',
            'parent_id' => $asetLancar->id,
            'level' => 3,
        ]);

        // Level 3 - Aset Tetap Details
        Account::create([
            'code' => '1-2100',
            'name' => 'Peralatan',
            'type' => 'asset',
            'parent_id' => $asetTetap->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '1-2200',
            'name' => 'Kendaraan',
            'type' => 'asset',
            'parent_id' => $asetTetap->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '1-2300',
            'name' => 'Gedung/Bangunan',
            'type' => 'asset',
            'parent_id' => $asetTetap->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '1-2900',
            'name' => 'Akumulasi Penyusutan',
            'type' => 'asset',
            'parent_id' => $asetTetap->id,
            'level' => 3,
            'description' => 'Akumulasi penyusutan aset tetap',
        ]);

        // Level 2 - KEWAJIBAN
        $kewajibanLancar = Account::create([
            'code' => '2-1000',
            'name' => 'Kewajiban Lancar',
            'type' => 'liability',
            'parent_id' => $liability->id,
            'level' => 2,
        ]);

        $kewajibanJangkaPanjang = Account::create([
            'code' => '2-2000',
            'name' => 'Kewajiban Jangka Panjang',
            'type' => 'liability',
            'parent_id' => $liability->id,
            'level' => 2,
        ]);

        // Level 3 - Kewajiban Lancar Details
        Account::create([
            'code' => '2-1100',
            'name' => 'Hutang Usaha',
            'type' => 'liability',
            'parent_id' => $kewajibanLancar->id,
            'level' => 3,
            'description' => 'Hutang kepada supplier',
        ]);

        Account::create([
            'code' => '2-1200',
            'name' => 'Hutang Gaji',
            'type' => 'liability',
            'parent_id' => $kewajibanLancar->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '2-1300',
            'name' => 'Hutang Pajak',
            'type' => 'liability',
            'parent_id' => $kewajibanLancar->id,
            'level' => 3,
        ]);

        // Level 3 - Kewajiban Jangka Panjang
        Account::create([
            'code' => '2-2100',
            'name' => 'Hutang Bank',
            'type' => 'liability',
            'parent_id' => $kewajibanJangkaPanjang->id,
            'level' => 3,
        ]);

        // Level 2 - EKUITAS
        Account::create([
            'code' => '3-1000',
            'name' => 'Modal Pemilik',
            'type' => 'equity',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Modal dari pemilik usaha',
        ]);

        Account::create([
            'code' => '3-2000',
            'name' => 'Laba Ditahan',
            'type' => 'equity',
            'parent_id' => $equity->id,
            'level' => 2,
            'description' => 'Laba yang tidak dibagikan',
        ]);

        Account::create([
            'code' => '3-3000',
            'name' => 'Laba Tahun Berjalan',
            'type' => 'equity',
            'parent_id' => $equity->id,
            'level' => 2,
        ]);

        // Level 2 - PENDAPATAN
        Account::create([
            'code' => '4-1000',
            'name' => 'Penjualan',
            'type' => 'revenue',
            'parent_id' => $revenue->id,
            'level' => 2,
            'description' => 'Pendapatan dari penjualan barang',
        ]);

        Account::create([
            'code' => '4-2000',
            'name' => 'Diskon Penjualan',
            'type' => 'revenue',
            'parent_id' => $revenue->id,
            'level' => 2,
            'description' => 'Potongan harga penjualan (kontra akun)',
        ]);

        Account::create([
            'code' => '4-3000',
            'name' => 'Retur Penjualan',
            'type' => 'revenue',
            'parent_id' => $revenue->id,
            'level' => 2,
            'description' => 'Pengembalian barang (kontra akun)',
        ]);

        Account::create([
            'code' => '4-4000',
            'name' => 'Pendapatan Lain-lain',
            'type' => 'revenue',
            'parent_id' => $revenue->id,
            'level' => 2,
        ]);

        // Level 2 - BEBAN
        $hpp = Account::create([
            'code' => '5-1000',
            'name' => 'Harga Pokok Penjualan',
            'type' => 'expense',
            'parent_id' => $expense->id,
            'level' => 2,
            'description' => 'HPP - Biaya langsung penjualan',
        ]);

        $bebanOperasional = Account::create([
            'code' => '5-2000',
            'name' => 'Beban Operasional',
            'type' => 'expense',
            'parent_id' => $expense->id,
            'level' => 2,
        ]);

        $bebanAdminUmum = Account::create([
            'code' => '5-3000',
            'name' => 'Beban Administrasi & Umum',
            'type' => 'expense',
            'parent_id' => $expense->id,
            'level' => 2,
        ]);

        // Level 3 - Beban Operasional Details
        Account::create([
            'code' => '5-2100',
            'name' => 'Beban Gaji Karyawan',
            'type' => 'expense',
            'parent_id' => $bebanOperasional->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-2200',
            'name' => 'Beban Sewa',
            'type' => 'expense',
            'parent_id' => $bebanOperasional->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-2300',
            'name' => 'Beban Listrik & Air',
            'type' => 'expense',
            'parent_id' => $bebanOperasional->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-2400',
            'name' => 'Beban Telepon & Internet',
            'type' => 'expense',
            'parent_id' => $bebanOperasional->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-2500',
            'name' => 'Beban Transportasi',
            'type' => 'expense',
            'parent_id' => $bebanOperasional->id,
            'level' => 3,
        ]);

        // Level 3 - Beban Admin & Umum Details
        Account::create([
            'code' => '5-3100',
            'name' => 'Beban ATK',
            'type' => 'expense',
            'parent_id' => $bebanAdminUmum->id,
            'level' => 3,
            'description' => 'Beban alat tulis kantor',
        ]);

        Account::create([
            'code' => '5-3200',
            'name' => 'Beban Penyusutan',
            'type' => 'expense',
            'parent_id' => $bebanAdminUmum->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-3300',
            'name' => 'Beban Pajak',
            'type' => 'expense',
            'parent_id' => $bebanAdminUmum->id,
            'level' => 3,
        ]);

        Account::create([
            'code' => '5-9000',
            'name' => 'Beban Lain-lain',
            'type' => 'expense',
            'parent_id' => $expense->id,
            'level' => 2,
        ]);

        $this->command->info('Chart of Accounts seeded successfully!');
    }
}
