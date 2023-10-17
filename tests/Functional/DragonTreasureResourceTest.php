<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\HttpOptions;

class DragonTreasureResourceTest extends ApiTestCase
{
    public function testGetCollectionOfTreasures(): void
    {
        DragonTreasureFactory::createMany(5);

        $json = $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"', 5)
            ->assertJsonMatches('length("hydra:member")', 5)
            ->json();

        $this->assertSame(array_keys($json->decoded()['hydra:member'][0]), [
            "@id",
            "@type",
            "createdAt",
            "updatedAt",
            "id",
            "name",
            "description",
            "value",
            "coolFactor",
            "isPublished",
            "owner",
            "shortDescription",
            "createdAtAgo",
        ]);
    }

    public function testCreateTreasureRequireAuthenticated()
    {
        $this->browser()
            ->post('api/treasures', [
                'json' => []
            ])->assertStatus(401);
    }

    public function testUserRequiredDataToCreateTreasure()
    {
        $user = UserFactory::createOne();
        $this->browser()->actingAs($user)
            ->post('api/treasures', [
                'json' => []
            ])->assertStatus(422);
    }

    public function testAuthenticatedUserCanCreateTreasure()
    {
        $user = UserFactory::createOne();
        $this->browser()->actingAs($user)
            ->post('api/treasures', HttpOptions::json([
                "name" => "test",
                "value" => 12,
                "coolFactor" => 1,
                "isPublished" => true,
                "description" => "test"
            ]))
            ->assertStatus(201);
    }

    public function testUserRequiredValidTokenToCreateTreasure()
    {
        $this->browser()
            ->post('api/treasures', HttpOptions::json([])->withHeader('Authorization',"Bearer invalid_token"))
            ->assertStatus(401)->dump();
    }

    public function testUserRequiredValidScopeToCreateTreasure()
    {
        $token = ApiTokenFactory::createOne(['scopes' => []])->getToken();
        $this->browser()
            ->post('api/treasures', HttpOptions::json([])->withHeader('Authorization',"Bearer $token"))
            ->assertStatus(403)->dump();
    }

    public function testUserCanCreateTreasure()
    {
        $token = ApiTokenFactory::createOne([
            'scopes' => [ApiToken::SCOPE_TREASURE_CREATE]
        ])->getToken();

        $this->browser()
            ->post('api/treasures', HttpOptions::json([
                "name" => "test",
                "value" => 12,
                "coolFactor" => 1,
                "isPublished" => true,
                "description" => "test"])->withHeader('Authorization',"Bearer $token"))
            ->assertStatus(201)->dump();
    }

    public function testOwnerCanUpdateHisTreasure()
    {
        $user = UserFactory::createOne();
        $treasure = DragonTreasureFactory::createOne([
            'owner' => $user
        ]);

        $token = ApiTokenFactory::createOne(['scopes' => [ApiToken::SCOPE_TREASURE_EDIT], 'owner' => $user])->getToken();


        $this->browser()->patch('api/treasures/'.$treasure->getId(), AppHttpOptions::api($token, [
            'coolFactor' => 4
        ]))->assertStatus(200)->assertJsonMatches('coolFactor', 4);
//
        $user2= UserFactory::createOne();
        $token2 = ApiTokenFactory::createOne(['scopes' => [ApiToken::SCOPE_TREASURE_EDIT], 'owner' => $user])->getToken();
//
        $this->browser()->patch('api/treasures/'.$treasure->getId(),AppHttpOptions::api($token2, [
            'owner' => 'api/users/'.$user2->getId(),
            'coolFactor' => 4
        ]))->assertStatus(403);

    }

    public function testAdminCanPatchToEditTreasure()
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $token = ApiTokenFactory::new()->asScopeRoleAdmin()->create(['owner' => $admin])->getToken();

        $treasure = DragonTreasureFactory::createOne();

        $this->browser()->patch('api/treasures/'.$treasure->getId(), AppHttpOptions::api($token, [
            'coolFactor' => 4
        ]))->dump()->assertStatus(200);
    }

    public function testOwnerCanSeeIsPublishPropertiesTreasure()
    {
        $user = UserFactory::createOne(['name' => 'ben']);
        $treasure = DragonTreasureFactory::createOne();

        $this->browserToken($user, ['ROLE_USER_EDIT'])->get('api/treasures')
            ->assertJsonMatches(200);
    }
}
