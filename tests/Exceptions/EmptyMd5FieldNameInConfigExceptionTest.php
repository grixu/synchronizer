<?php

namespace Grixu\Synchronizer\Tests\Exceptions;

use Grixu\Synchronizer\Exceptions\EmptyMd5FieldNameInConfigException;
use Illuminate\Http\JsonResponse;
use Orchestra\Testbench\TestCase;

/**
 * Class EmptyMd5FieldNameInConfigExceptionTest
 * @package Grixu\Synchronizer\Tests\Exceptions
 */
class EmptyMd5FieldNameInConfigExceptionTest extends TestCase
{
    protected EmptyMd5FieldNameInConfigException $obj;

    protected function setUp(): void
    {
        parent::setUp();
        $this->obj = new EmptyMd5FieldNameInConfigException();
    }

    /** @test */
    public function is_response_is_json()
    {
        $this->assertEquals(JsonResponse::class, get_class($this->obj->render()));
        $this->assertJson($this->obj->render()->getContent());
    }

    /** @test */
    public function is_response_code_is_500()
    {
        $this->assertEquals(500, $this->obj->render()->getStatusCode());
    }
}
