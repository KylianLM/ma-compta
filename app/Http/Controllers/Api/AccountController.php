<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AccountController extends Controller
{
    /**
     * Display a listing of user's accounts.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $accounts = Account::query()
            ->forUser($request->user()->id)
            ->with([]) // Pas de relations pour l'instant
            ->when($request->boolean('active_only'), fn ($q) => $q->active())
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('currency'), fn ($q) => $q->where('currency', $request->currency))
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->get();

        return AccountResource::collection($accounts);
    }

    /**
     * Display the specified account.
     */
    public function show(Request $request, Account $account): AccountResource
    {
        // Vérifier que l'utilisateur peut voir ce compte
        if ($account->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to this account');
        }

        return new AccountResource($account);
    }

    /**
     * Get dashboard summary of accounts.
     */
    public function dashboard(Request $request): array
    {
        $userId = $request->user()->id;

        $accounts = Account::query()
            ->forUser($userId)
            ->active()
            ->get();

        // Calculer les totaux par devise
        $totalsByCurrency = $accounts->groupBy('currency')
            ->map(function ($accounts, $currency) {
                $total = $accounts->sum(function ($account) {
                    return $account->getEffectiveBalanceCents();
                });

                return [
                    'currency'        => $currency,
                    'total_cents'     => $total,
                    'total_formatted' => $accounts->first()->currency->formatAmount($total),
                    'accounts_count'  => $accounts->count(),
                ];
            })
            ->values();

        // Statistiques par type de compte
        $accountsByType = $accounts->groupBy('type')
            ->map(function ($accounts, $type) {
                $total = $accounts->sum(function ($account) {
                    return $account->getEffectiveBalanceCents();
                });

                return [
                    'type'           => $type,
                    'type_label'     => $accounts->first()->type->label(),
                    'total_cents'    => $total,
                    'accounts_count' => $accounts->count(),
                ];
            })
            ->values();

        return [
            'totals_by_currency' => $totalsByCurrency,
            'accounts_by_type'   => $accountsByType,
            'total_accounts'     => $accounts->count(),
            'active_accounts'    => $accounts->where('is_active', true)->count(),
        ];
    }
}
