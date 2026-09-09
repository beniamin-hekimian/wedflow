<?php

namespace Tests\Feature;

use Tests\TestCase as BaseTestCase;

class SwaggerDocTest extends BaseTestCase
{
    public function test_api_docs_generate_a_valid_openapi_spec(): void
    {
        $this->artisan('l5-swagger:generate')->assertExitCode(0);

        $this->assertFileExists(storage_path('api-docs/api-docs.json'));

        $docs = json_decode(
            (string) file_get_contents(storage_path('api-docs/api-docs.json')),
            true,
        );

        $this->assertIsArray($docs);
        $this->assertSame('3.0.0', $docs['openapi']);
        $this->assertSame('Wedflow API', $docs['info']['title']);
        $this->assertCount(31, $docs['paths']);
        $this->assertArrayHasKey('PageResponse', $docs['components']['schemas']);
        $this->assertArrayHasKey('component', $docs['components']['schemas']['PageResponse']['properties']);
        $this->assertArrayHasKey('/{slug}/rsvp', $docs['paths']);
        $this->assertArrayHasKey('/admin/users', $docs['paths']);
        $this->assertArrayHasKey('/invitations/{slug}', $docs['paths']);
        $this->assertArrayHasKey('/password', $docs['paths']);
    }
}