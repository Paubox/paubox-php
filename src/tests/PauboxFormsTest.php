<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxForms;
use Paubox\Forms\Form;
use Paubox\Forms\FormSubmission;
use Paubox\Forms\PauboxFormsException;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxForms.php";
require_once dirname(__DIR__) . "/forms/Form.php";
require_once dirname(__DIR__) . "/forms/FormSubmission.php";
require_once dirname(__DIR__) . "/forms/PauboxFormsException.php";

/**
 * PauboxForms test suite.
 *
 * Test groups:
 *   - default (no group) — safe to run, either offline or read-only
 *   - @group mutating    — writes to the target Forms API. Excluded by
 *     default in phpunit.xml. Opt in with:
 *         vendor/bin/phpunit --group mutating src/tests/PauboxFormsTest.php
 *
 * Environment variables:
 *   PAUBOX_FORMS_API_KEY   — scoped API key with the 'forms' scope
 *   PAUBOX_FORMS_BASE_URL  — optional; override the default prod endpoint
 *   QA_TEST_FORM_UUID      — an existing form on the target customer
 *   QA_TEST_SUBMISSION_UUID — an existing submission on the above form
 *   QA_TEST_CUSTOMER_ID    — the numeric customer id owning the fixtures
 *
 * Any authenticated / fixture-dependent test skips (not fails) when its
 * env vars aren't set, so a keyless local run always terminates cleanly.
 */
class PauboxFormsTest extends TestCase
{
    private $forms;

    protected function setUp(): void
    {
        $this->forms = new PauboxForms(getenv('PAUBOX_FORMS_API_KEY') ?: null);
    }

    protected function tearDown(): void
    {
        $this->forms = null;
        parent::tearDown();
    }

    private function skipIfNoApiKey()
    {
        if (!getenv('PAUBOX_FORMS_API_KEY')) {
            $this->markTestSkipped('PAUBOX_FORMS_API_KEY is not set; skipping authenticated integration test.');
        }
    }

    private function requireFixture($envName)
    {
        $value = getenv($envName);
        if ($value === false || $value === '') {
            $this->markTestSkipped("$envName is not set; skipping fixture-dependent test.");
        }
        return $value;
    }

    private function skipIfNoWrongScopeKey()
    {
        if (!getenv('PAUBOX_FORMS_WRONG_SCOPE_KEY')) {
            $this->markTestSkipped('PAUBOX_FORMS_WRONG_SCOPE_KEY is not set; skipping wrong-scope auth test.');
        }
    }

    // ------------------------------------------------------------------
    // Missing API key — no network, must never be skipped
    // ------------------------------------------------------------------

    public function missingApiKeyDataProvider()
    {
        $validForm = new Form();
        $validForm->setTitle('Missing Key Test Form');
        $validForm->setFormJson(['pages' => []]);
        $validForm->setCustomerId(1);
        $validUuid = '00000000-0000-0000-0000-000000000001';

        return [
            'getFormById'       => ['getFormById', [$validUuid]],
            'listForms'         => ['listForms', [[]]],
            'createForm'        => ['createForm', [$validForm]],
            'updateForm'        => ['updateForm', [$validUuid, ['title' => 'New Title']]],
            'archiveForm'       => ['archiveForm', [$validUuid]],
            'unarchiveForm'     => ['unarchiveForm', [$validUuid]],
            'copyForm'          => ['copyForm', [$validUuid, 'Copied Form']],
            'getFormStats'      => ['getFormStats', [[]]],
            'listSubmissions'   => ['listSubmissions', [$validUuid, []]],
            'getSubmissionsCsv' => ['getSubmissionsCsv', [$validUuid]],
            'getSubmissionCsv'  => ['getSubmissionCsv', [$validUuid, $validUuid]],
            'getSubmissionPdf'  => ['getSubmissionPdf', [$validUuid, $validUuid]]
        ];
    }

    /**
     * @dataProvider missingApiKeyDataProvider
     */
    public function testAuthenticatedMethod_ThrowsWithoutApiKey($method, $args)
    {
        $originalKey = getenv('PAUBOX_FORMS_API_KEY');
        putenv('PAUBOX_FORMS_API_KEY');

        try {
            $keylessClient = new PauboxForms();
            $caught = false;
            try {
                call_user_func_array([$keylessClient, $method], $args);
            } catch (PauboxFormsException $e) {
                $caught = true;
                $this->assertStringContainsString("scoped API key", $e->getMessage());
                $this->assertStringContainsString("PAUBOX_FORMS_API_KEY", $e->getMessage());
            }
            $this->assertTrue($caught, "Expected PauboxFormsException when calling $method() without an API key.");
        } finally {
            if ($originalKey !== false) {
                putenv('PAUBOX_FORMS_API_KEY=' . $originalKey);
            }
        }
    }

    // ------------------------------------------------------------------
    // URL path guard regression — hostile input rejected pre-HTTP
    // ------------------------------------------------------------------

    public function hostileUuidProvider()
    {
        return [
            'traversal ..'          => ['../stats'],
            'query splice'          => ['a?other=1'],
            'fragment splice'       => ['a#frag'],
            'slash extra segment'   => ['other-id/submissions'],
            'not a uuid'            => ['not-a-uuid'],
            'null byte'             => ["good-uuid\0bad"],
            'empty string'          => [''],
        ];
    }

    /**
     * Every authenticated method that takes a formId (or submissionId) must
     * reject hostile input before any HTTP call. Uses a keyless client to
     * prove the UUID check fires before the auth check.
     *
     * @dataProvider hostileUuidProvider
     */
    public function testGetFormById_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getFormById($hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testUpdateForm_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->updateForm($hostile, ['title' => 'x']);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testArchiveForm_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->archiveForm($hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testUnarchiveForm_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->unarchiveForm($hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testListSubmissions_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->listSubmissions($hostile, []);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testGetSubmissionsCsv_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionsCsv($hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testGetSubmissionCsv_RejectsHostileSubmissionId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionCsv('00000000-0000-0000-0000-000000000000', $hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testGetSubmissionPdf_RejectsHostileSubmissionId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionPdf('00000000-0000-0000-0000-000000000000', $hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testGetForm_RejectsHostileFormId($hostile)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getForm($hostile);
    }

    /**
     * @dataProvider hostileUuidProvider
     */
    public function testSubmitForm_RejectsHostileFormId($hostile)
    {
        $submission = new FormSubmission();
        $submission->setFormData(['x' => 'y']);
        $this->expectException(PauboxFormsException::class);
        $this->forms->submitForm($hostile, $submission);
    }

    // ------------------------------------------------------------------
    // Public getForm / submitForm (no auth)
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testGetForm_ReturnSuccess()
    {
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $form = $this->forms->getForm($formId);
        $this->assertIsObject($form);
        $this->assertTrue(isset($form->id), 'Expected form to have id');
        $this->assertTrue(isset($form->title), 'Expected form to have title');
    }

    /**
     * @group network
     */
    public function testGetForm_ReturnNotFound()
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->getForm('00000000-0000-0000-0000-000000000000');
    }

    /**
     * @group mutating
     */
    public function testSubmitForm_ReturnSuccess()
    {
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $submission = new FormSubmission();
        $submission->setFormData([
            'first_name' => 'Jane',
            'last_name'  => 'Smith',
            'email'      => 'test@example.com'
        ]);
        $result = $this->forms->submitForm($formId, $submission);
        $this->assertTrue($result);
    }

    /**
     * @group network
     */
    public function testSubmitForm_ReturnError()
    {
        $submission = new FormSubmission();
        $submission->setFormData(['first_name' => 'Jane']);
        $this->expectException(PauboxFormsException::class);
        $this->forms->submitForm('00000000-0000-0000-0000-000000000000', $submission);
    }

    // ------------------------------------------------------------------
    // getFormById
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testGetFormById_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $form = $this->forms->getFormById($formId);
        $this->assertIsObject($form);
        $this->assertTrue(isset($form->id));
        $this->assertTrue(isset($form->title));
    }

    /**
     * @group network
     */
    public function testGetFormById_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->getFormById('00000000-0000-0000-0000-000000000000');
    }

    /**
     * @group network
     */
    public function testGetFormById_WrongScopeKey_ReturnsUnauthorized()
    {
        // README documents a getStatusCode() === 403 pattern for scope failures;
        // live behavior is 401. Pins the real code so a regression (or a doc fix)
        // is caught, not silently trusted from the README.
        $this->skipIfNoWrongScopeKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $wrongScopeForms = new PauboxForms(getenv('PAUBOX_FORMS_WRONG_SCOPE_KEY'));
        try {
            $wrongScopeForms->getFormById($formId);
            $this->fail('Expected PauboxFormsException for a key without the forms scope.');
        } catch (PauboxFormsException $e) {
            $this->assertSame(401, $e->getStatusCode());
        }
    }

    // ------------------------------------------------------------------
    // listForms (read-only)
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testListForms_ReturnSuccess_WithCustomerId()
    {
        $this->skipIfNoApiKey();
        $customerId = (int)$this->requireFixture('QA_TEST_CUSTOMER_ID');
        $result = $this->forms->listForms(['customer_id' => $customerId]);
        $this->assertIsObject($result);
        $this->assertTrue(isset($result->results));
        $this->assertTrue(isset($result->page_info));
        $this->assertIsArray($result->results);
    }

    /**
     * @group network
     */
    public function testListForms_ReturnSuccess_WithPagination()
    {
        $this->skipIfNoApiKey();
        $customerId = (int)$this->requireFixture('QA_TEST_CUSTOMER_ID');
        $result = $this->forms->listForms([
            'customer_id' => $customerId,
            'page'        => 1,
            'items'       => 5,
            'order'       => 'desc',
            'order_by'    => 'updated_at'
        ]);
        $this->assertIsObject($result);
        $this->assertIsArray($result->results);
    }

    /**
     * @group network
     */
    public function testListForms_ThrowsWithoutCustomerId()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->listForms([]);
    }

    // ------------------------------------------------------------------
    // createForm  (mutating: archives the new form in finally)
    // ------------------------------------------------------------------

    /**
     * @group mutating
     */
    public function testCreateForm_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $customerId = (int)$this->requireFixture('QA_TEST_CUSTOMER_ID');
        $form = new Form();
        $form->setTitle('Paubox PHP SDK Test Form ' . uniqid('test-', true));
        $form->setFormJson(['pages' => []]);
        $form->setCustomerId($customerId);
        $form->setDescription('Created by the Paubox PHP SDK test suite - safe to delete');
        $form->setActive(true);

        $newFormId = null;
        try {
            $newFormId = $this->forms->createForm($form);
            $this->assertNotEmpty($newFormId);
        } finally {
            if ($newFormId) {
                try {
                    $this->forms->archiveForm($newFormId);
                } catch (\Exception $e) {
                    fwrite(STDERR, "Cleanup: failed to archive created form $newFormId: " . $e->getMessage() . "\n");
                }
            }
        }
    }

    public function createFormValidationDataProvider()
    {
        $missingTitle = new Form();
        $missingTitle->setFormJson(['pages' => []]);
        $missingTitle->setCustomerId(1);

        $missingFormJson = new Form();
        $missingFormJson->setTitle('Missing Form JSON');
        $missingFormJson->setCustomerId(1);

        $missingCustomerId = new Form();
        $missingCustomerId->setTitle('Missing Customer ID');
        $missingCustomerId->setFormJson(['pages' => []]);

        return [
            'missing title'      => [$missingTitle],
            'missing formJson'   => [$missingFormJson],
            'missing customerId' => [$missingCustomerId]
        ];
    }

    /**
     * @dataProvider createFormValidationDataProvider
     */
    public function testCreateForm_ThrowsOnMissingRequiredField($form)
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->createForm($form);
    }

    // ------------------------------------------------------------------
    // updateForm  (mutating: restores title+description in finally)
    // ------------------------------------------------------------------

    /**
     * @group mutating
     */
    public function testUpdateForm_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $before = $this->forms->getFormById($formId);

        try {
            $result = $this->forms->updateForm($formId, [
                'title'       => 'Updated by PHP SDK Test - ' . uniqid(),
                'description' => 'Updated by the Paubox PHP SDK test suite'
            ]);
            $this->assertIsObject($result);
            $this->assertTrue(isset($result->detail));
        } finally {
            $restore = ['title' => $before->title];
            if (isset($before->description)) {
                $restore['description'] = $before->description;
            }
            try {
                $this->forms->updateForm($formId, $restore);
            } catch (\Exception $e) {
                fwrite(STDERR, "Cleanup: failed to restore form $formId: " . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * @group network
     */
    public function testUpdateForm_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->updateForm('00000000-0000-0000-0000-000000000000', ['title' => 'Updated Title']);
    }

    public function testUpdateForm_ThrowsWhenNoAllowedAttributes()
    {
        $this->expectException(PauboxFormsException::class);
        $this->forms->updateForm('00000000-0000-0000-0000-000000000000', ['bogus_key' => 'value']);
    }

    public function testUpdateForm_ThrowsWhenAllValuesAreNull()
    {
        // Regression: array_key_exists would have transmitted null,
        // clearing a live form's field. Now null is filtered out and
        // an empty body is rejected before any HTTP call.
        $this->expectException(PauboxFormsException::class);
        $this->forms->updateForm('00000000-0000-0000-0000-000000000000', ['recipient' => null]);
    }

    public function testUpdateForm_ThrowsOnNonBooleanShapedActive()
    {
        // Regression: an accidental string like 'yes' or 'no' must not
        // silently serialize as truthy; require an explicit boolean shape.
        $this->expectException(PauboxFormsException::class);
        $this->forms->updateForm('00000000-0000-0000-0000-000000000000', ['active' => 'yes']);
    }

    // ------------------------------------------------------------------
    // archive/unarchive  (mutating: restores prior state in finally)
    // ------------------------------------------------------------------

    /**
     * @group mutating
     */
    public function testArchiveAndUnarchiveForm_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $before = $this->forms->getFormById($formId);
        $wasArchived = isset($before->archived) && $before->archived;

        try {
            if ($wasArchived) {
                $this->assertTrue($this->forms->unarchiveForm($formId));
                $this->assertTrue($this->forms->archiveForm($formId));
            } else {
                $this->assertTrue($this->forms->archiveForm($formId));
                $this->assertTrue($this->forms->unarchiveForm($formId));
            }
        } finally {
            try {
                $after = $this->forms->getFormById($formId);
                $endState = isset($after->archived) && $after->archived;
                if ($endState !== $wasArchived) {
                    if ($wasArchived) {
                        $this->forms->archiveForm($formId);
                    } else {
                        $this->forms->unarchiveForm($formId);
                    }
                }
            } catch (\Exception $e) {
                fwrite(STDERR, "Cleanup: could not verify/restore archive state for $formId: " . $e->getMessage() . "\n");
            }
        }
    }

    /**
     * @group network
     */
    public function testArchiveForm_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->archiveForm('00000000-0000-0000-0000-000000000000');
    }

    /**
     * @group network
     */
    public function testUnarchiveForm_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->unarchiveForm('00000000-0000-0000-0000-000000000000');
    }

    // ------------------------------------------------------------------
    // copyForm  (mutating: archives the copy in finally)
    // ------------------------------------------------------------------

    /**
     * @group mutating
     */
    public function testCopyForm_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $newTitle = 'Copied by PHP SDK Test - ' . uniqid();

        $newForm = null;
        try {
            $newForm = $this->forms->copyForm($formId, $newTitle);
            $this->assertIsObject($newForm);
            $this->assertTrue(isset($newForm->id));
            $this->assertNotEquals($formId, $newForm->id);
        } finally {
            if ($newForm && isset($newForm->id)) {
                try {
                    $this->forms->archiveForm($newForm->id);
                } catch (\Exception $e) {
                    fwrite(STDERR, "Cleanup: failed to archive copied form {$newForm->id}: " . $e->getMessage() . "\n");
                }
            }
        }
    }

    /**
     * @group network
     */
    public function testCopyForm_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->copyForm('00000000-0000-0000-0000-000000000000', 'Copy of Missing Form');
    }

    // ------------------------------------------------------------------
    // getFormStats (read-only)
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testGetFormStats_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $customerId = (int)$this->requireFixture('QA_TEST_CUSTOMER_ID');
        $stats = $this->forms->getFormStats(['customer_id' => $customerId]);
        $this->assertIsObject($stats);
        $this->assertTrue(isset($stats->active_form_count));
        $this->assertTrue(isset($stats->total_submission_count));
        $this->assertTrue(isset($stats->submissions_last_7_days));
    }

    /**
     * @group network
     */
    public function testGetFormStats_ReturnError()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->getFormStats(['customer_id' => 'not-a-number']);
    }

    // ------------------------------------------------------------------
    // listSubmissions (read-only)
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testListSubmissions_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $result = $this->forms->listSubmissions($formId, []);
        $this->assertIsObject($result);
        $this->assertTrue(isset($result->data));
        $this->assertTrue(isset($result->total));
        $this->assertIsArray($result->data);
    }

    /**
     * @group network
     */
    public function testListSubmissions_ReturnSuccess_WithPagination()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $result = $this->forms->listSubmissions($formId, [
            'page'     => 1,
            'items'    => 5,
            'order'    => 'desc',
            'order_by' => 'created_at'
        ]);
        $this->assertIsObject($result);
        $this->assertIsArray($result->data);
    }

    /**
     * @group network
     */
    public function testListSubmissions_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->listSubmissions('00000000-0000-0000-0000-000000000000', []);
    }

    // ------------------------------------------------------------------
    // getSubmissionsCsv / getSubmissionCsv / getSubmissionPdf (read-only)
    // ------------------------------------------------------------------

    /**
     * @group network
     */
    public function testGetSubmissionsCsv_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $csv = $this->forms->getSubmissionsCsv($formId);
        $this->assertIsString($csv);
        $this->assertNotEmpty($csv);
    }

    /**
     * @group network
     */
    public function testGetSubmissionsCsv_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionsCsv('00000000-0000-0000-0000-000000000000');
    }

    /**
     * @group network
     */
    public function testGetSubmissionCsv_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $submissionId = $this->requireFixture('QA_TEST_SUBMISSION_UUID');
        $csv = $this->forms->getSubmissionCsv($formId, $submissionId);
        $this->assertIsString($csv);
        $this->assertNotEmpty($csv);
    }

    /**
     * @group network
     */
    public function testGetSubmissionCsv_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionCsv($formId, '00000000-0000-0000-0000-000000000000');
    }

    /**
     * @group network
     */
    public function testGetSubmissionPdf_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $submissionId = $this->requireFixture('QA_TEST_SUBMISSION_UUID');
        $pdf = $this->forms->getSubmissionPdf($formId, $submissionId);
        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        $this->assertSame('%PDF', substr($pdf, 0, 4));
    }

    /**
     * @group network
     */
    public function testGetSubmissionPdf_ReturnNotFound()
    {
        $this->skipIfNoApiKey();
        $formId = $this->requireFixture('QA_TEST_FORM_UUID');
        $this->expectException(PauboxFormsException::class);
        $this->forms->getSubmissionPdf($formId, '00000000-0000-0000-0000-000000000000');
    }
}
