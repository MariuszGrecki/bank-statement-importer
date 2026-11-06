<?php

use App\Models\BankAccount;
use App\Models\Import;
use App\Models\Transaction;
use App\Models\User;

function bankAccountHeaders(User $user): array
{
    return ['Authorization' => 'Bearer '.$user->createToken('spa')->plainTextToken];
}

it('zwraca wyłącznie konta zalogowanego użytkownika', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $firstAccount = BankAccount::factory()->for($user)->create();
    $secondAccount = BankAccount::factory()->for($user)->create();
    $otherAccount = BankAccount::factory()->for($otherUser)->create();

    $response = $this->getJson('/api/bank-accounts', bankAccountHeaders($user));

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $firstAccount->id, 'name' => $firstAccount->name])
        ->assertJsonFragment(['id' => $secondAccount->id, 'name' => $secondAccount->name])
        ->assertJsonMissing(['id' => $otherAccount->id]);
});

it('wymaga uwierzytelnienia dla każdego endpointu kont bankowych', function () {
    $bankAccount = BankAccount::factory()->create();

    $this->getJson('/api/bank-accounts')->assertUnauthorized();
    $this->postJson('/api/bank-accounts', ['name' => 'Konto osobiste'])->assertUnauthorized();
    $this->getJson("/api/bank-accounts/{$bankAccount->id}")->assertUnauthorized();
    $this->putJson("/api/bank-accounts/{$bankAccount->id}", ['name' => 'Nowa nazwa'])->assertUnauthorized();
    $this->deleteJson("/api/bank-accounts/{$bankAccount->id}")->assertUnauthorized();
});

it('nie pozwala odczytać, zmienić ani usunąć cudzego konta', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $bankAccount = BankAccount::factory()->for($owner)->create(['name' => 'Tylko właściciel']);

    $headers = bankAccountHeaders($intruder);

    $this->getJson("/api/bank-accounts/{$bankAccount->id}", $headers)->assertForbidden();
    $this->putJson("/api/bank-accounts/{$bankAccount->id}", ['name' => 'Próba kradzieży'], $headers)->assertForbidden();
    $this->deleteJson("/api/bank-accounts/{$bankAccount->id}", [], $headers)->assertForbidden();

    $this->assertDatabaseHas('bank_accounts', [
        'id' => $bankAccount->id,
        'user_id' => $owner->id,
        'name' => 'Tylko właściciel',
    ]);
});

it('tworzy konto dla zalogowanego użytkownika i ignoruje user_id z żądania', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $response = $this->postJson('/api/bank-accounts', [
        'name' => 'Nowe konto',
        'user_id' => $otherUser->id,
    ], bankAccountHeaders($user));

    $response
        ->assertCreated()
        ->assertJsonPath('data.name', 'Nowe konto');

    $this->assertDatabaseHas('bank_accounts', [
        'user_id' => $user->id,
        'name' => 'Nowe konto',
    ]);
});

it('waliduje nazwę konta bankowego', function (string $name) {
    $user = User::factory()->create();

    $this->postJson('/api/bank-accounts', ['name' => $name], bankAccountHeaders($user))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
})->with([
    'pusta nazwa' => [''],
    'nazwa dłuższa niż 100 znaków' => [str_repeat('a', 101)],
]);

it('odrzuca duplikat nazwy u tego samego użytkownika, ale akceptuje go u innego', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    BankAccount::factory()->for($user)->create(['name' => 'Wspólna nazwa']);

    $this->postJson('/api/bank-accounts', ['name' => 'Wspólna nazwa'], bankAccountHeaders($user))
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');

    $this->app['auth']->forgetGuards();

    $this->postJson('/api/bank-accounts', ['name' => 'Wspólna nazwa'], bankAccountHeaders($otherUser))
        ->assertCreated();
});

it('pozwala zapisać konto z niezmienioną nazwą', function () {
    $user = User::factory()->create();
    $bankAccount = BankAccount::factory()->for($user)->create(['name' => 'Bez zmian']);

    $this->putJson(
        "/api/bank-accounts/{$bankAccount->id}",
        ['name' => 'Bez zmian'],
        bankAccountHeaders($user),
    )
        ->assertOk()
        ->assertJsonPath('data.name', 'Bez zmian');
});

it('usuwa własne konto razem z jego importami i transakcjami', function () {
    $user = User::factory()->create();
    $bankAccount = BankAccount::factory()->for($user)->create();
    $import = Import::factory()->create([
        'user_id' => $user->id,
        'bank_account_id' => $bankAccount->id,
    ]);
    $transaction = Transaction::factory()->create([
        'import_id' => $import->id,
        'bank_account_id' => $bankAccount->id,
    ]);

    $this->deleteJson("/api/bank-accounts/{$bankAccount->id}", [], bankAccountHeaders($user))
        ->assertNoContent();

    $this->assertDatabaseMissing('bank_accounts', ['id' => $bankAccount->id]);
    $this->assertDatabaseMissing('imports', ['id' => $import->id]);
    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
});
