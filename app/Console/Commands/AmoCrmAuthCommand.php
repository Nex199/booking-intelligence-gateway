<?php

namespace App\Console\Commands;

use App\Services\AmoCrmService;
use Illuminate\Console\Command;

class AmoCrmAuthCommand extends Command
{
    protected $signature = 'amocrm:auth {code} {domain}';

    protected $description = 'Первичная авторизация AmoCrm обмен кода на токены, первый аргумент это код авторизации а второй домен';

    public function handle(AmoCrmService $amoService)
    {
        $code = $this->argument('code');
        $domain = $this->argument('domain');

        try {
            $amoService->exchangeAuthorizationCode($code, $domain);
            $this->info('Успешно');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Ошибка авторизации: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
