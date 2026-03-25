<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_json_payload_can_be_parsed(): void
    {
        $payload = [
            'status' => 'success',
            'status_code' => 200,
        ];

        $json = json_encode($payload);
        $this->assertNotFalse($json);

        $decoded = json_decode((string) $json, true);
        $this->assertIsArray($decoded);
        $this->assertSame('success', $decoded['status'] ?? null);
    }
}
