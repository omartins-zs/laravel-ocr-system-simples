<?php

namespace Tests\Unit;

use App\Support\ApiResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_response_follows_standard_contract(): void
    {
        $response = ApiResponse::success(
            message: 'ok',
            data: ['id' => 1],
            statusCode: 201
        );

        $payload = $response->getData(true);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('success', $payload['status']);
        $this->assertSame(201, $payload['status_code']);
        $this->assertSame('ok', $payload['message']);
        $this->assertSame(['id' => 1], $payload['data']);
        $this->assertSame([], $payload['errors']);
    }

    public function test_error_response_follows_standard_contract(): void
    {
        $response = ApiResponse::error(
            message: 'erro',
            statusCode: 422,
            errors: ['file' => ['invalido']]
        );

        $payload = $response->getData(true);

        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('error', $payload['status']);
        $this->assertSame(422, $payload['status_code']);
        $this->assertSame('erro', $payload['message']);
        $this->assertSame(['file' => ['invalido']], $payload['errors']);
    }
}
