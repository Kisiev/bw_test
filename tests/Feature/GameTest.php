<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class GameTest extends TestCase
{
	public function authUser()
	{
		$response = $this->json('POST', '/api/login', [
			'name' => 'uniq_user',
			'password' => '11111111'
		]);
		$response->assertStatus(200);
	}
	public function testGenerateGame()
	{
		$this->artisan('migrate:refresh --seed');
		// Запрос неавторизованного пользователя
		$response = $this->json('GET', '/api/game');
		$response->assertStatus(401);

		$this->authUser();
		// После авторизации
		$response = $this->json('GET', '/api/game');
		$response->assertStatus(200);
		$response->assertJson(['game_id' => 1]);
		$responseData = $response->decodeResponseJson();
		// Проверяем есть ли в редисе перемешанный вариант игры
		$this->assertEquals($responseData['sequence'], Redis::get('mixed_game_sequence_1'));
		$this->assertDatabaseHas('games', [
			'user_id' => 1,
			'id' => 1
		]);
		$this->assertDatabaseMissing('games', [
			'user_id' => 1,
			'id' => 1,
			'sequence' => $responseData['sequence']
		]);
		// передать свою игру (Некорректная последовательность)
		$response = $this->json('GET', '/api/game', [
			'sequence' => 'abc'
		]);
		$response->assertStatus(422);

		// передать свою игру (Корректная последовательность)
		$response = $this->json('GET', '/api/game', [
			'sequence' => 'abcqweasdzxcvbg_'
		]);
		$response->assertStatus(200);
		$responseData = $response->decodeResponseJson();
		$response->assertJson(['game_id' => 2]);
		$this->assertEquals($responseData['sequence'], Redis::get('mixed_game_sequence_2'));
		$this->assertDatabaseHas('games', [
			'user_id' => 1,
			'id' => 2,
			'sequence' => 'abcqweasdzxcvbg_'
		]);
	}
	public function testSolve()
	{
		$this->artisan('migrate:refresh --seed');

		$this->authUser();
		$response = $this->json('POST', '/api/game/1/solve');
		$response->assertStatus(404);

		$response = $this->json('GET', '/api/game');
		$response->assertStatus(200);

		$response = $this->json('POST', '/api/game/1/solve');
		$response->assertStatus(422);

		// Добавим другого пользователя
		DB::table('users')->insert(['name' => 'second_user', 'password' => rand(0, 9999)]);
		// Добавляем игру
		DB::table('games')->insert(['sequence' => 'abcqweasdzxcvbg_', 'user_id' => 2]);

		// 403 ошибка. Тест не принадлежит пользователю
		$response = $this->json('POST', '/api/game/2/solve');
		$response->assertStatus(403);

		// тест валидатора
		$response = $this->json('POST', '/api/game/1/solve', [
			'steps' => [
				[
					'from' => ['x' => 1, 'y' => 'g'],
					'to'   => ['x' => 1, 'y' => 'g'],
				],
				[
					'from' => 'x',
				],
				[
					'from' => ['k' => 2, 'j' => 2],
				],
				[
					'from' => ['x' => -2, 'y' => 2],
				]
			]
		]);
		$responseData = $response->decodeResponseJson();

		$response->assertStatus(422);
		$this->assertEquals(empty($responseData['errors']['steps.0.from.y']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.0.to.y']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.1.from']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.1.to']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.2.to']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.2.from.x']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.2.from.y']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.2.to.y']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.2.to.x']), 0);
		$this->assertEquals(empty($responseData['errors']['steps.3.from.x']), 0);

		// создать игру
		$response = $this->json('GET', '/api/game', [
			'sequence' => '123456789123456_'
		]);
		$response->assertStatus(200);
		$responseData = $response->decodeResponseJson();
		// 1234
		// 5678
		// 9123
		// 456_

		// 1_34
		// 5278
		// 9613
		// 4526
		$gameId = $responseData['game_id'];
		Redis::set('mixed_game_sequence_' . $gameId, '1_34527896134526');
		Redis::set('start_time_' . $gameId, time());
		// тест на прохождение игры
		$response = $this->json('POST', '/api/game/' . $responseData['game_id'] . '/solve', [
			'steps' => [
				[
					'from' => ['x' => 1, 'y' => 1],
					'to'   => ['x' => 0, 'y' => 1],
				],
				[
					'from' => ['x' => 2, 'y' => 1],
					'to'   => ['x' => 1, 'y' => 1],
				],
				[
					'from' => ['x' => 2, 'y' => 2],
					'to'   => ['x' => 2, 'y' => 1],
				],
				[
					'from' => ['x' => 3, 'y' => 2],
					'to'   => ['x' => 2, 'y' => 2],
				],
				[
					'from' => ['x' => 3, 'y' => 3],
					'to'   => ['x' => 3, 'y' => 2],
				]
			]
		]);
		$response->assertStatus(200);
		$responseData = $response->decodeResponseJson();
		$this->assertEquals($responseData['message'], 'Успешно');
		$this->assertEquals(empty($responseData['execution_time']), 0);

		$this->assertDatabaseHas('games', [
			'user_id' => 1,
			'id' => $gameId,
			'sequence' => '123456789123456_',
			'execution_time' => $responseData['execution_time']
		]);
	}
}
