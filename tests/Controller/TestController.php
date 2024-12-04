<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestController extends WebTestCase
{
    public function testDiscountCodeIsSet()
    {
        $discountCode = getenv('DISCOUNT_CODE');

        $this->assertNotNull($discountCode, 'DISCOUNT_CODE should not be null');

        $this->assertEquals('discount20', $discountCode, 'DISCOUNT_CODE should be equal to discount20');
    }
    public function testEnvironmentVariabless()
{
    $discountCode = getenv('DISCOUNT_CODE');
    $this->assertNotNull($discountCode, 'DISCOUNT_CODE should not be null');
    $this->assertEquals('discount20', $discountCode, 'DISCOUNT_CODE should be discount20');

    $appSecret = getenv('APP_SECRET');
    $this->assertNotNull($appSecret, 'APP_SECRET should not be null');
    $this->assertEquals('ecretf0rt3st', $appSecret, 'APP_SECRET should be ecretf0rt3st');
}
public function testEnvironmentVariables()
{
    var_dump(getenv('APP_SECRET')); 
    var_dump(getenv('DISCOUNT_CODE')); 

    $discountCode = getenv('DISCOUNT_CODE');
    $this->assertNotNull($discountCode, 'DISCOUNT_CODE should not be null');
    $this->assertEquals('discount20', $discountCode, 'DISCOUNT_CODE should be discount20');

    $appSecret = getenv('APP_SECRET');
    $this->assertNotNull($appSecret, 'APP_SECRET should not be null');
    $this->assertEquals('ecretf0rt3st', $appSecret, 'APP_SECRET should be ecretf0rt3st');
}


}
