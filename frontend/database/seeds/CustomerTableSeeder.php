<?php

use Illuminate\Database\Seeder;

class CustomerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('customer')->insert([            
            'external_id'=> '#123456',
            'type'=> 'individual',
            'country'=> 'br',
            'document_type'=> 'cpf',
            'document_number'=> '08828926651',
            'name'=> 'Sidnei da Silva Simeão',
            'email'=> 'sidneisimmon@gmail.com',
            'password'=> md5('*admin12345'),
            'birthday'=> '1986-30-06',            
            'gender'=> 'M',
            'status'=> 'A',
            'crea+at'=> date('Y-d-m')
        ]);
    }
}
