<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\ServiceUser;
use App\User;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function requestReset(Request $request)
    {
        $data = $request->validate([
            'user_name' => ['nullable', 'string', 'max:255', 'required_without:email'],
            'email' => ['nullable', 'email:rfc', 'max:255', 'required_without:user_name'],
            'type' => ['nullable', 'in:staff,service_user'],
        ]);

        $account = $this->findAccount($data);

        try {
            if ($account instanceof User) {
                User::sendCredentials($account->id, 'password_reset');
            } elseif ($account instanceof ServiceUser) {
                ServiceUser::sendCredentials($account->id, 'password_reset');
            }
        } catch (\Throwable $exception) {
            \Illuminate\Support\Facades\Log::error('API password reset email failed.', [
                'account_type' => $account ? get_class($account) : null,
                'account_id' => $account?->getKey(),
                'exception' => $exception,
            ]);
        }

        return response()->json([
            'success' => true,
            'result' => [
                'response' => true,
                'message' => 'If the account exists, a password link has been sent.',
            ],
            'message' => 'If the account exists, a password link has been sent.',
        ]);
    }

    private function findAccount(array $data)
    {
        $field = isset($data['email']) ? 'email' : 'user_name';
        $value = $data[$field];

        if (($data['type'] ?? null) === 'staff') {
            return User::query()
                ->where($field, $value)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->first();
        }

        if (($data['type'] ?? null) === 'service_user') {
            return ServiceUser::query()
                ->where($field, $value)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->first();
        }

        return ServiceUser::query()
            ->where($field, $value)
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->first()
            ?? User::query()
                ->where($field, $value)
                ->where('status', 1)
                ->where('is_deleted', 0)
                ->first();
    }
}
