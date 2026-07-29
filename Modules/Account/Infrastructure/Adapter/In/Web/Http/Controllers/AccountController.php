<?php

namespace Modules\Account\Infrastructure\Adapter\In\Web\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Modules\Account\Application\Port\Out\LoadAccountPort;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;

class AccountController extends Controller
{
    public function __construct(
        private readonly LoadAccountPort $loadAccountPort,
    ) {
    }

    public function index(): JsonResponse
    {
        $accountModels = AccountModel::all();
        $accounts = $accountModels->map(function (AccountModel $accountModel) {
            $account = $this->loadAccountPort->loadAccount(
                new AccountId($accountModel->id),
                Carbon::now()->subDays(10),
            );

            return [
                'id' => $accountModel->id,
                'created_at' => $accountModel->created_at,
                'balance' => $account->calculateBalance()->amount,
            ];
        });

        return Response::json(['data' => $accounts]);
    }

    public function show(int $id): JsonResponse
    {
        $accountModel = AccountModel::findOrFail($id);
        $account = $this->loadAccountPort->loadAccount(
            new AccountId($id),
            Carbon::now()->subDays(10),
        );

        return Response::json([
            'data' => [
                'id' => $accountModel->id,
                'created_at' => $accountModel->created_at,
                'balance' => $account->calculateBalance()->amount,
                'activities' => $this->loadAccountActivities($id),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'sometimes|int',
        ]);

        $accountModel = AccountModel::factory()->create(
            isset($validated['id']) ? ['id' => $validated['id']] : [],
        );

        $this->seedDemoActivities($request, $accountModel);

        return Response::json(['data' => ['id' => $accountModel->id]], 201);
    }

    /**
     * Load and format activities for a given account, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    private function loadAccountActivities(int $accountId): array
    {
        return ActivityModel::where('owner_account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (ActivityModel $activity) => [
                'id' => $activity->id,
                'amount' => $activity->amount,
                'source_account_id' => $activity->source_account_id,
                'target_account_id' => $activity->target_account_id,
                'type' => $activity->source_account_id === $accountId ? 'outgoing' : 'incoming',
                'created_at' => $activity->created_at,
            ])
            ->all();
    }

    /**
     * Seed demo deposits for a newly created account.
     *
     * If another account already exists, uses it as the deposit source
     * so the new account gets a real non-zero balance.
     * Otherwise, uses self as source (balance stays 0 for the first account).
     */
    private function seedDemoActivities(Request $request, AccountModel $accountModel): void
    {
        if ($request->has('seed_activities') && ! $request->boolean('seed_activities')) {
            return;
        }

        $otherAccount = AccountModel::where('id', '!=', $accountModel->id)->first();
        $sourceId = $otherAccount !== null ? $otherAccount->id : $accountModel->id;

        ActivityModel::factory()->count(3)->sequence(
            ['amount' => 1000, 'created_at' => now()->subDays(5)],
            ['amount' => 500, 'created_at' => now()->subDays(3)],
            ['amount' => 200, 'created_at' => now()->subDays(1)],
        )->create([
            'owner_account_id' => $accountModel->id,
            'source_account_id' => $sourceId,
            'target_account_id' => $accountModel->id,
        ]);
    }
}
