<?php

declare(strict_types=1);

namespace App\Services;

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\CustomFieldsValuesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\CustomFieldsValues\TextCustomFieldValuesModel;
use AmoCRM\Models\CustomFieldsValues\ValueCollections\TextCustomFieldValueCollection;
use AmoCRM\Models\CustomFieldsValues\ValueModels\TextCustomFieldValueModel;
use AmoCRM\Models\LeadModel;
use App\Repositories\AmoTokenRepository;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Log;

class AmoCrmService
{
    private string $clientId;

    private string $clientSecret;

    private string $redirectUri;

    private int $guestPreferencesFieldId;

    public function __construct(private AmoTokenRepository $repository)
    {
        $clientId = config('services.amocrm.client_id');
        $clientSecret = config('services.amocrm.client_secret');
        $redirectUri = config('services.amocrm.redirect_uri');
        $guestPreferencesFieldId = config('services.amocrm.fields.guest_preferences');
        if (! $clientId) {
            throw new \InvalidArgumentException('Отсутствует client_id');
        }
        if (! $clientSecret) {
            throw new \InvalidArgumentException('Отсутствует client_secret');
        }
        if (! $redirectUri) {
            throw new \InvalidArgumentException('Отсутствует redirect_uri');
        }
        if (! $guestPreferencesFieldId) {
            throw new \InvalidArgumentException('Отсутствует guest_preferences');
        }
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->guestPreferencesFieldId = (int) $guestPreferencesFieldId;
    }

    public function getClient()
    {
        $apiClient = new AmoCRMApiClient($this->clientId, $this->clientSecret, $this->redirectUri);

        $token = $this->repository->getToken();

        if ($token !== null) {
            $apiClient->setAccessToken($token);
            $apiClient->setAccountBaseDomain($token->getValues()['base_domain']);
            $apiClient->onAccessTokenRefresh(
                function (AccessTokenInterface $accessToken, string $baseDomain) {
                    $this->repository->saveToken($accessToken, $baseDomain);
                }
            );
        }

        return $apiClient;
    }

    public function exchangeAuthorizationCode(string $code, string $baseDomain): void
    {
        try {
            $apiClient = $this->getClient();

            $apiClient->setAccountBaseDomain($baseDomain);

            $accessToken = $apiClient->getOAuthClient()->getAccessTokenByCode($code);

            $this->repository->saveToken($accessToken, $baseDomain);
        } catch (AmoCRMApiException $e) {
            Log::error("AmoCrm error: {$e->getMessage()}", $e->getLastRequestInfo());
            throw $e;
        }
    }

    public function syncLead(?int $leadId, array $data)
    {
        try {
            $apiClient = $this->getClient();

            $lead = new LeadModel;

            if (isset($data['budget'])) {
                $lead->setPrice((int) $data['budget']);
            }

            $customFields = new CustomFieldsValuesCollection;

            if (isset($data['guest_preferences']) && ! empty($data['guest_preferences'])) {
                $preferencesField = new TextCustomFieldValuesModel;
                $preferencesField->setFieldId((int) $this->guestPreferencesFieldId);

                $preferencesField->setValues(
                    (new TextCustomFieldValueCollection)
                        ->add((new TextCustomFieldValueModel)->setValue($data['guest_preferences']))
                );

                $customFields->add($preferencesField);
            }

            if (! $customFields->isEmpty()) {
                $lead->setCustomFieldsValues($customFields);
            }

            if ($leadId !== null) {
                $lead->setId($leadId);
                $apiClient->leads()->updateOne($lead);

                return $leadId;
            } else {
                $savedLead = $apiClient->leads()->addOne($lead);

                return $savedLead->getId();
            }
        } catch (AmoCRMApiException $e) {
            Log::error("AmoCrm error: {$e->getMessage()}", $e->getLastRequestInfo());
            throw $e;
        }

    }
}
