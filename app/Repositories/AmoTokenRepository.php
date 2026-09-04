<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\AmoToken;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Token\AccessToken;

class AmoTokenRepository
{
    public function saveToken(AccessToken $token, string $baseDomain): void
    {
        AmoToken::query()->updateOrCreate(
            ['id' => 1],
            [
                'access_token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'base_domain' => $baseDomain,
                'expires_at' => $token->getExpires(),
            ],
        );

        $expires_at = $token->getExpires() - time();
        if($expires_at > 0) {
            Cache::put('amocrm_access_token', [
                'access_token' => $token->getToken(),
                'refresh_token' => $token->getRefreshToken(),
                'base_domain' => $baseDomain,
                'expires' => $token->getExpires(),
            ], $expires_at);
        }
    }

    public function getToken(): ?AccessToken
    {
        $token = Cache::get('amocrm_access_token', null);
        if ($token) {
            return new AccessToken($token);
        }

        $model = AmoToken::query()->first();
        if (! $model) {
            return null;
        }
        $expires_at = $model->expires_at;
        $token = [
            'access_token' => $model->access_token,
            'refresh_token' => $model->refresh_token,
            'base_domain' => $model->base_domain,
            'expires' => $expires_at,
        ];
        
        $ttl = $expires_at - time();
        if($expires_at > 0) {
            Cache::put('amocrm_access_token', $token, $expires_at);
        }

        return new AccessToken($token);
    }
}
