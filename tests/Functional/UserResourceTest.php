<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Factory\ApiTokenFactory;
use App\Factory\UserFactory;

class UserResourceTest extends  ApiTestCase
{
    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'name' => 'draggin_in_the_morning',
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ]
            ])
            ->assertStatus(201)
            ->post('/login', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ]
            ])->dump()
            ->assertSuccessful()
        ;
    }

    public function testPatchCanUpdateUser()
    {
        $user = UserFactory::createOne(['name' => 'ben']);

        $this->browserToken($user, ['ROLE_USER_EDIT'])->patch('api/users/'.$user->getId(), [
            'json' => [
                'name' => 'ben update'
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json'
            ]
        ])->assertStatus(200);
    }
}