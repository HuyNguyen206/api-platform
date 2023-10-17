<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\KernelBrowser;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

abstract class ApiTestCase extends KernelTestCase
{
    use HasBrowser {
        browser as baseKernelBrowser;
    }
    use ResetDatabase;

    protected function browser(array $options = [], array $server = []): KernelBrowser
    {
       return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeader('Accept', 'application/ld+json')
            );
    }

    protected function browserToken($user = null, $scopes = ['ROLE_ADMIN'], array $options = [], array $server = []): KernelBrowser
    {
        $user = $user ?? UserFactory::createOne();
        $token = ApiTokenFactory::createOne(['scopes' => $scopes, 'owner' => $user])->getToken();

       return $this->baseKernelBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()->withHeaders(['Accept', 'application/ld+json', 'Authorization' => "Bearer $token"]),
            );
    }

}