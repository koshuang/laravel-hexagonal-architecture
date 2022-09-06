<?php

namespace Modules\Account\Infrastructure\Adapter\In\Web\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Modules\Account\Application\Port\In\SendMoneyCommand;
use Modules\Account\Application\Port\In\SendMoneyUseCase;
use Modules\Account\Domain\ValueObjects\AccountId;
use Modules\Account\Domain\ValueObjects\Money;

class SendMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function sendMoney(
        Request $request,
        SendMoneyUseCase $sendMoneyUseCase,
        int $sourceAccountId,
        int $targetAccountId,
        int $money,
    ): JsonResponse {
        $sendMoneyUseCase->sendMoney(
            new SendMoneyCommand(
                sourceAccountId: new AccountId($sourceAccountId),
                targetAccountId: new AccountId($targetAccountId),
                money: new Money($money),
            )
        );

        return Response::json([]);
    }
}
