<?php

use PHPUnit\Framework\TestCase;
use Paubox\PauboxForms;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/PauboxForms.php';

/**
 * QA verification for TEST_PLAN.md Phase 1.1 — base URL resolution.
 * Offline, no network. Reads the private $baseUrl via reflection since
 * PauboxForms exposes no getter.
 */
class BaseUrlResolutionTest extends TestCase
{
    private function resolvedBaseUrl(PauboxForms $forms)
    {
        $ref = new ReflectionProperty(PauboxForms::class, 'baseUrl');
        return $ref->getValue($forms);
    }

    protected function tearDown(): void
    {
        putenv('PAUBOX_FORMS_BASE_URL');
        parent::tearDown();
    }

    public function testDefault_NoArgNoEnv()
    {
        putenv('PAUBOX_FORMS_BASE_URL');
        $forms = new PauboxForms('test-key');
        $this->assertSame('https://api.paubox.com/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testConstructorOverride()
    {
        $forms = new PauboxForms('test-key', 'https://x.test/v1/forms');
        $this->assertSame('https://x.test/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testEnvOverride()
    {
        putenv('PAUBOX_FORMS_BASE_URL=https://y.test/v1/forms');
        $forms = new PauboxForms('test-key');
        $this->assertSame('https://y.test/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testConstructorPrecedenceOverEnv()
    {
        putenv('PAUBOX_FORMS_BASE_URL=https://y.test/v1/forms');
        $forms = new PauboxForms('test-key', 'https://x.test/v1/forms');
        $this->assertSame('https://x.test/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testTrailingSlashStripped_Constructor()
    {
        $forms = new PauboxForms('test-key', 'https://x.test/v1/forms/');
        $this->assertSame('https://x.test/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testTrailingSlashStripped_Env()
    {
        putenv('PAUBOX_FORMS_BASE_URL=https://y.test/v1/forms/');
        $forms = new PauboxForms('test-key');
        $this->assertSame('https://y.test/v1/forms', $this->resolvedBaseUrl($forms));
    }

    public function testNoDoubleSlashInBuiltUrl()
    {
        // Mirrors getForm()'s own URL construction: baseUrl . "/public/form_data/" . id
        $forms = new PauboxForms('test-key', 'https://x.test/v1/forms/');
        $built = $this->resolvedBaseUrl($forms) . '/public/form_data/' . 'abc-123';
        $this->assertStringNotContainsString('//public', $built);
        $this->assertSame('https://x.test/v1/forms/public/form_data/abc-123', $built);
    }
}
