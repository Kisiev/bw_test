<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		$users = [
			[
				'name'     => 'uniq_user',
				'password' => '$2y$04$ycot.cXS9.cueUwTNzva2eTi3cgVUI.fNK0vsyviX0SvNIyvzgrgS'
			]
		];
		DB::table('users')->insert($users);
	}
}
