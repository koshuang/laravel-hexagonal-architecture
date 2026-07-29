<?php

namespace Modules\Account\Tests\Unit\Infrastructure\Adapter\In\Web\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @internal
 */
#[CoversNothing]
class AccountControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    #[Test]
    public function lists_accounts(): void
    {
        AccountModel::factory()->count(2)->create();

        $response = $this->json('GET', '/api/accounts');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    #[Test]
    public function lists_accounts_with_balance(): void
    {
        $account = AccountModel::factory()->create();
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'target_account_id' => $account->id,
            'amount' => 500,
        ]);

        $response = $this->json('GET', '/api/accounts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'balance', 'created_at'],
            ],
        ]);
    }

    #[Test]
    public function shows_account_detail(): void
    {
        $account = AccountModel::factory()->create();
        ActivityModel::factory()->count(2)->in()->create([
            'owner_account_id' => $account->id,
            'target_account_id' => $account->id,
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'balance', 'activities'],
        ]);
    }

    #[Test]
    public function returns_404_for_nonexistent_account(): void
    {
        $response = $this->json('GET', '/api/accounts/999');

        $response->assertStatus(404);
    }

    #[Test]
    public function creates_account(): void
    {
        $response = $this->json('POST', '/api/accounts');

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => ['id']]);
        $this->assertEquals(1, AccountModel::count());
        $this->assertTrue(ActivityModel::count() > 0, 'Should seed activities');
    }

    #[Test]
    public function creates_account_without_seed_activities(): void
    {
        $response = $this->json('POST', '/api/accounts', [
            'seed_activities' => false,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(1, AccountModel::count());
        $this->assertEquals(0, ActivityModel::count());
    }

    // === EDGE CASES ===

    #[Test]
    public function first_account_seed_has_zero_balance(): void
    {
        // First account seed: no other account exists, so activities are self-transactions
        $response = $this->json('POST', '/api/accounts', [
            'seed_activities' => true,
        ]);

        $response->assertStatus(201);
        $accountId = $response->json('data.id');
        $this->assertIsInt($accountId);

        // Get account detail — balance should be 0 (self-transaction)
        $detailResponse = $this->json('GET', "/api/accounts/{$accountId}");
        $detailResponse->assertStatus(200);

        $balance = $detailResponse->json('data.balance');
        $this->assertEquals(0, $balance, 'First seeded account should have 0 balance');
    }

    #[Test]
    public function second_account_seed_receives_deposits(): void
    {
        // Create first account as source
        $firstAccount = AccountModel::factory()->create();
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $firstAccount->id,
            'source_account_id' => $firstAccount->id,
            'target_account_id' => $firstAccount->id,
            'amount' => 1000,
        ]);

        // Create second account — should use first account as deposit source
        $response = $this->json('POST', '/api/accounts', [
            'seed_activities' => true,
        ]);

        $response->assertStatus(201);
        $secondAccountId = $response->json('data.id');
        $this->assertIsInt($secondAccountId);

        // Second account should have non-zero balance
        $detailResponse = $this->json('GET', "/api/accounts/{$secondAccountId}");
        $detailResponse->assertStatus(200);

        $balance = $detailResponse->json('data.balance');
        $this->assertGreaterThan(0, $balance, 'Second seeded account should have positive balance');
    }

    #[Test]
    public function account_with_no_activities_returns_empty_array(): void
    {
        $account = AccountModel::factory()->create();

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'balance', 'activities'],
        ]);
        $this->assertIsArray($response->json('data.activities'));
        $this->assertCount(0, $response->json('data.activities'), 'Account with no activities should have empty activities array');
    }

    #[Test]
    public function account_balance_is_calculated_correctly(): void
    {
        $account = AccountModel::factory()->create();

        // Use in() factory which creates an "incoming" activity referencing a real other account
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'target_account_id' => $account->id,
            'amount' => 1000,
        ]);

        // Use out() factory which creates an "outgoing" activity referencing a real other account
        ActivityModel::factory()->out()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $account->id,
            'amount' => 300,
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $this->assertEquals(700, $response->json('data.balance'));
    }

    #[Test]
    public function account_balance_can_be_negative(): void
    {
        $account = AccountModel::factory()->create();

        // Only has outgoing, no incoming
        ActivityModel::factory()->out()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $account->id,
            'amount' => 999,
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $this->assertLessThan(0, $response->json('data.balance'), 'Balance can be negative');
    }

    #[Test]
    public function create_account_with_explicit_id(): void
    {
        $response = $this->json('POST', '/api/accounts', [
            'id' => 42,
        ]);

        $response->assertStatus(201);
        $this->assertEquals(42, $response->json('data.id'));
        $this->assertDatabaseHas('accounts', ['id' => 42]);
    }

    #[Test]
    public function send_money_between_accounts_successfully(): void
    {
        $source = AccountModel::factory()->create();
        $target = AccountModel::factory()->create();

        // Give source account some money via incoming activity
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $source->id,
            'target_account_id' => $source->id,
            'amount' => 1000,
        ]);

        $response = $this->json('POST', "/api/accounts/send/{$source->id}/{$target->id}/300");

        // SendMoneyUseCase should succeed
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    #[Test]
    public function send_money_insufficient_balance_returns_success_false(): void
    {
        $source = AccountModel::factory()->create();
        $target = AccountModel::factory()->create();

        // Source has no money — send should fail
        $response = $this->json('POST', "/api/accounts/send/{$source->id}/{$target->id}/9999");

        // Use case returns success=false, not a 4xx/5xx
        $response->assertStatus(200);
        $response->assertJson(['success' => false]);
    }

    #[Test]
    public function lists_json_structure_is_consistent(): void
    {
        $account = AccountModel::factory()->create();
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'target_account_id' => $account->id,
            'amount' => 500,
        ]);

        $response = $this->json('GET', '/api/accounts');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'balance', 'created_at'],
            ],
        ]);

        // Verify field types via JSON fragment matching
        $response->assertJsonFragment(['id' => $account->id]);
    }

    #[Test]
    public function show_json_structure_is_consistent(): void
    {
        $account = AccountModel::factory()->create();
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'target_account_id' => $account->id,
            'amount' => 500,
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => ['id', 'balance', 'activities'],
        ]);

        // Verify activity structure if present
        $activities = $response->json('data.activities');
        $this->assertIsArray($activities);

        if (count($activities) > 0) {
            /** @var array<string, mixed> $activity */
            $activity = $activities[0];
            $this->assertArrayHasKey('id', $activity);
            $this->assertArrayHasKey('amount', $activity);
            $this->assertArrayHasKey('source_account_id', $activity);
            $this->assertArrayHasKey('target_account_id', $activity);
            $this->assertArrayHasKey('type', $activity);
            $this->assertContains($activity['type'], ['incoming', 'outgoing']);
        }
    }

    // === MORE EDGE CASES ===

    #[Test]
    public function lists_accounts_empty(): void
    {
        $response = $this->json('GET', '/api/accounts');

        $response->assertStatus(200);
        $response->assertJson(['data' => []]);
        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertCount(0, $data);
    }

    #[Test]
    public function shows_account_with_only_outgoing_activities(): void
    {
        $source = AccountModel::factory()->create();
        $target = AccountModel::factory()->create();

        // Only outgoing activities from source account
        ActivityModel::factory()->out()->create([
            'owner_account_id' => $source->id,
            'source_account_id' => $source->id,
            'target_account_id' => $target->id,
            'amount' => 200,
        ]);

        $response = $this->json('GET', "/api/accounts/{$source->id}");

        $response->assertStatus(200);
        $activities = $response->json('data.activities');
        $this->assertIsArray($activities);
        $this->assertCount(1, $activities);

        /** @var array<string, mixed> $activity */
        $activity = $activities[0];
        $this->assertEquals('outgoing', $activity['type']);
        $this->assertEquals($source->id, $activity['source_account_id']);
    }

    #[Test]
    public function shows_account_with_only_incoming_activities(): void
    {
        $source = AccountModel::factory()->create();
        $target = AccountModel::factory()->create();

        // Only incoming activities to target account
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $target->id,
            'source_account_id' => $source->id,
            'target_account_id' => $target->id,
            'amount' => 500,
        ]);

        $response = $this->json('GET', "/api/accounts/{$target->id}");

        $response->assertStatus(200);
        $activities = $response->json('data.activities');
        $this->assertIsArray($activities);
        $this->assertCount(1, $activities);

        /** @var array<string, mixed> $activity */
        $activity = $activities[0];
        $this->assertEquals('incoming', $activity['type']);
        $this->assertEquals($target->id, $activity['target_account_id']);
    }

    #[Test]
    public function shows_account_with_mixed_activity_types(): void
    {
        $account = AccountModel::factory()->create();
        $other = AccountModel::factory()->create();

        // One outgoing
        ActivityModel::factory()->out()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $account->id,
            'target_account_id' => $other->id,
            'amount' => 100,
        ]);

        // One incoming
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $other->id,
            'target_account_id' => $account->id,
            'amount' => 300,
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $activities = $response->json('data.activities');
        $this->assertIsArray($activities);
        $this->assertCount(2, $activities);

        /** @var list<array{type: string}> $activities */
        $types = array_map(fn ($a) => $a['type'], $activities);
        sort($types);
        $this->assertEquals(['incoming', 'outgoing'], $types);
    }

    #[Test]
    public function shows_account_activities_ordered_by_date_desc(): void
    {
        $account = AccountModel::factory()->create();
        $other = AccountModel::factory()->create();

        // Create 3 activities with different dates
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $other->id,
            'target_account_id' => $account->id,
            'amount' => 100,
            'created_at' => now()->subDays(10),
        ]);

        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $other->id,
            'target_account_id' => $account->id,
            'amount' => 200,
            'created_at' => now()->subDays(5),
        ]);

        ActivityModel::factory()->in()->create([
            'owner_account_id' => $account->id,
            'source_account_id' => $other->id,
            'target_account_id' => $account->id,
            'amount' => 300,
            'created_at' => now(),
        ]);

        $response = $this->json('GET', "/api/accounts/{$account->id}");

        $response->assertStatus(200);
        $activities = $response->json('data.activities');
        $this->assertIsArray($activities);
        $this->assertCount(3, $activities);

        // Newest first — compare created_at ISO strings (lexicographic ordering)
        /** @var array<string, mixed> $first */
        $first = $activities[0];

        /** @var array<string, mixed> $second */
        $second = $activities[1];

        /** @var array<string, mixed> $third */
        $third = $activities[2];
        $this->assertGreaterThan($second['created_at'], $first['created_at']);
        $this->assertGreaterThan($third['created_at'], $second['created_at']);
    }

    #[Test]
    public function send_money_with_exact_balance(): void
    {
        $source = AccountModel::factory()->create();
        $target = AccountModel::factory()->create();

        // Source has exactly $500
        ActivityModel::factory()->in()->create([
            'owner_account_id' => $source->id,
            'target_account_id' => $source->id,
            'amount' => 500,
        ]);

        // Send exactly $500 — boundary: spending every cent
        $response = $this->json('POST', "/api/accounts/send/{$source->id}/{$target->id}/500");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }
}
