<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountSetting;
use Illuminate\Database\Seeder;

class AccountSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'sales',
                'name' => 'Penjualan',
                'code' => '4-1000',
                'description' => 'Akun untuk mencatat pendapatan penjualan',
            ],
            [
                'key' => 'sales_discount',
                'name' => 'Diskon Penjualan',
                'code' => '4-2000',
                'description' => 'Akun untuk mencatat diskon penjualan',
            ],
            [
                'key' => 'sales_return',
                'name' => 'Retur Penjualan',
                'code' => '4-3000',
                'description' => 'Akun untuk mencatat retur penjualan',
            ],
            [
                'key' => 'cogs',
                'name' => 'Harga Pokok Penjualan (HPP)',
                'code' => '5-1000',
                'description' => 'Akun untuk mencatat HPP',
            ],
            [
                'key' => 'inventory',
                'name' => 'Persediaan Barang',
                'code' => '1-1400',
                'description' => 'Akun untuk mencatat persediaan barang dagangan',
            ],
            [
                'key' => 'cash',
                'name' => 'Kas',
                'code' => '1-1100',
                'description' => 'Akun untuk mencatat kas tunai',
            ],
            [
                'key' => 'bank',
                'name' => 'Bank',
                'code' => '1-1200',
                'description' => 'Akun untuk mencatat kas di bank',
            ],
            [
                'key' => 'accounts_receivable',
                'name' => 'Piutang Usaha',
                'code' => '1-1300',
                'description' => 'Akun untuk mencatat piutang dari pelanggan',
            ],
            [
                'key' => 'accounts_payable',
                'name' => 'Hutang Usaha',
                'code' => '2-1100',
                'description' => 'Akun untuk mencatat hutang kepada supplier',
            ],
            [
                'key' => 'purchase',
                'name' => 'Pembelian',
                'code' => '5-1000',
                'description' => 'Akun untuk mencatat pembelian barang',
            ],
            [
                'key' => 'purchase_return',
                'name' => 'Retur Pembelian',
                'code' => '5-1000',
                'description' => 'Akun untuk mencatat retur pembelian',
            ],
            [
                'key' => 'sales_cash',
                'name' => 'Penjualan Cash (Akun Debit)',
                'code' => '1-1100',
                'description' => 'Akun yang di-debit saat penjualan tunai (misal: Kas)',
            ],
            [
                'key' => 'sales_credit',
                'name' => 'Penjualan Tempo (Akun Debit)',
                'code' => '1-1300',
                'description' => 'Akun yang di-debit saat penjualan tempo/hutang (misal: Piutang Usaha)',
            ],
        ];

        foreach ($settings as $setting) {
            $account = Account::where('code', $setting['code'])->first();
            
            AccountSetting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'name' => $setting['name'],
                    'account_id' => $account?->id,
                    'description' => $setting['description'],
                ]
            );
        }

        $this->command->info('Account settings seeded successfully!');
    }
}
