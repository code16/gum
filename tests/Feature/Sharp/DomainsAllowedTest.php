<?php

namespace Code16\Gum\Tests\Feature\Sharp;

use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Tests\Feature\Utils\UserModel;
use Illuminate\Support\Str;

class DomainsAllowedTest extends GumSharpTestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        app(\Illuminate\Database\Eloquent\Factory::class)->define(DomainUserModel::class, function (\Faker\Generator $faker) {
            return [
                'email' => $faker->unique()->safeEmail,
                'name' => $faker->name,
                'password' => bcrypt('secret'),
                'remember_token' => Str::random(10),
            ];
        });
    }


    /** @test */
    function we_can_limit_domain_access_for_a_user()
    {
        $this->withoutExceptionHandling();
        
        config()->set([
            "gum" => [
                "domains" => [
                    "denied" => "Denied",
                    "allowed" => "Allowed",
                    "allowed2" => "Allowed2",
                ],
            ]
        ]);

        $this->loginAsSharpUser(factory(DomainUserModel::class)->create());

        $this->assertEquals("allowed", SharpGumSessionValue::getDomain());

        SharpGumSessionValue::setDomain("allowed2");
        $this->assertEquals("allowed2", SharpGumSessionValue::getDomain());

        SharpGumSessionValue::setDomain("denied");
        $this->assertEquals("allowed2", SharpGumSessionValue::getDomain());
    }

}

class DomainUserModel extends UserModel
{
    public function isAdminForDomain(string $domain): bool
    {
        return $domain != "denied";
    }
}