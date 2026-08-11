<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxForms;
use Paubox\Forms\Form;
use Paubox\Forms\FormSubmission;
use Paubox\Forms\FormAttachment;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxForms.php";
require_once dirname(__DIR__) . "/forms/Form.php";
require_once dirname(__DIR__) . "/forms/FormSubmission.php";
require_once dirname(__DIR__) . "/forms/FormAttachment.php";

class PauboxFormsTest extends TestCase
{
    private $forms;

    protected function setUp(): void
    {
        $this->forms = new PauboxForms(getenv('PAUBOX_API_KEY') ?: null);
    }

    protected function tearDown(): void
    {
        $this->forms = null;
        parent::tearDown();
    }

    private function skipIfNoApiKey()
    {
        if (!getenv('PAUBOX_API_KEY')) {
            $this->markTestSkipped('PAUBOX_API_KEY is not set; skipping authenticated integration test.');
        }
    }

    public function getFormDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE']
        ];
    }

    public function getFormDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000']
        ];
    }

    /**
     * @dataProvider getFormDataProvider_Success
     * @group integration
     */
    public function testGetForm_ReturnSuccess($formId)
    {
        $form = $this->forms->getForm($formId);
        if (is_null($form) || !isset($form->id) || !isset($form->title)) {
            $this->fail('Expected form with id and title');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getFormDataProvider_NotFound
     * @group integration
     */
    public function testGetForm_ReturnNotFound($formId)
    {
        $this->expectException(\Exception::class);
        $this->forms->getForm($formId);
    }

    public function submitFormDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', ['first_name' => 'Jane', 'last_name' => 'Smith']]
        ];
    }

    public function submitFormDataProvider_Error()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', ['first_name' => 'Jane']]
        ];
    }

    /**
     * @dataProvider submitFormDataProvider_Success
     * @group integration
     * @group destructive
     */
    public function testSubmitForm_ReturnSuccess($formId, $formData)
    {
        $submission = new FormSubmission();
        $submission->setFormData($formData);
        $result = $this->forms->submitForm($formId, $submission);
        $this->assertTrue($result);
    }

    /**
     * @dataProvider submitFormDataProvider_Error
     * @group integration
     */
    public function testSubmitForm_ReturnError($formId, $formData)
    {
        $this->expectException(\Exception::class);
        $submission = new FormSubmission();
        $submission->setFormData($formData);
        $this->forms->submitForm($formId, $submission);
    }

    // ------------------------------------------------------------------
    // Missing API key tests (no network access, must never be skipped)
    // ------------------------------------------------------------------

    public function missingApiKeyDataProvider()
    {
        $validForm = new Form();
        $validForm->setTitle('Missing Key Test Form');
        $validForm->setFormJson(['pages' => []]);
        $validForm->setCustomerId(1);

        return [
            'getFormById'       => ['getFormById', ['00000000-0000-0000-0000-000000000000']],
            'listForms'         => ['listForms', [[]]],
            'createForm'        => ['createForm', [$validForm]],
            'updateForm'        => ['updateForm', ['00000000-0000-0000-0000-000000000000', ['title' => 'New Title']]],
            'archiveForm'       => ['archiveForm', ['00000000-0000-0000-0000-000000000000']],
            'unarchiveForm'     => ['unarchiveForm', ['00000000-0000-0000-0000-000000000000']],
            'copyForm'          => ['copyForm', ['00000000-0000-0000-0000-000000000000', 'Copied Form']],
            'getFormStats'      => ['getFormStats', [[]]],
            'listSubmissions'   => ['listSubmissions', ['00000000-0000-0000-0000-000000000000', []]],
            'getSubmissionsCsv' => ['getSubmissionsCsv', ['00000000-0000-0000-0000-000000000000']],
            'getSubmissionCsv'  => ['getSubmissionCsv', ['00000000-0000-0000-0000-000000000000', '00000000-0000-0000-0000-000000000000']],
            'getSubmissionPdf'  => ['getSubmissionPdf', ['00000000-0000-0000-0000-000000000000', '00000000-0000-0000-0000-000000000000']]
        ];
    }

    /**
     * @dataProvider missingApiKeyDataProvider
     */
    public function testAuthenticatedMethod_ThrowsWithoutApiKey($method, $args)
    {
        $originalKey = getenv('PAUBOX_API_KEY');
        putenv('PAUBOX_API_KEY');

        try {
            $keylessClient = new PauboxForms();
            $caught = false;
            try {
                call_user_func_array([$keylessClient, $method], $args);
            } catch (\Exception $e) {
                $caught = true;
                $this->assertStringContainsString("scoped API key", $e->getMessage());
            }
            $this->assertTrue($caught, "Expected \\Exception when calling $method() without an API key.");
        } finally {
            if ($originalKey !== false) {
                putenv('PAUBOX_API_KEY=' . $originalKey);
            }
        }
    }

    // ------------------------------------------------------------------
    // getFormById
    // ------------------------------------------------------------------

    public function getFormByIdDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE']
        ];
    }

    public function getFormByIdDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000']
        ];
    }

    /**
     * @dataProvider getFormByIdDataProvider_Success
     * @group integration
     */
    public function testGetFormById_ReturnSuccess($formId)
    {
        $this->skipIfNoApiKey();
        $form = $this->forms->getFormById($formId);
        if (is_null($form) || !isset($form->id) || !isset($form->title)) {
            $this->fail('Expected form with id and title');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getFormByIdDataProvider_NotFound
     * @group integration
     */
    public function testGetFormById_ReturnNotFound($formId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->getFormById($formId);
    }

    // ------------------------------------------------------------------
    // listForms
    // ------------------------------------------------------------------

    public function listFormsDataProvider_Success()
    {
        return [
            [['customer_id' => 'YOUR-CUSTOMER-ID-HERE']],
            [['customer_id' => 'YOUR-CUSTOMER-ID-HERE', 'page' => 1, 'items' => 5, 'order' => 'desc', 'order_by' => 'updated_at']],
            [['customer_id' => 'YOUR-CUSTOMER-ID-HERE', 'archived' => false, 'active' => true]]
        ];
    }

    public function listFormsDataProvider_Error()
    {
        return [
            [['order_by' => 'not_a_real_column']]
        ];
    }

    /**
     * @dataProvider listFormsDataProvider_Success
     * @group integration
     */
    public function testListForms_ReturnSuccess($params)
    {
        $this->skipIfNoApiKey();
        $result = $this->forms->listForms($params);
        if (is_null($result) || !isset($result->results) || !isset($result->page_info)) {
            $this->fail('Expected response with results and page_info');
        } else {
            $this->assertTrue(is_array($result->results));
        }
    }

    /**
     * @dataProvider listFormsDataProvider_Error
     * @group integration
     */
    public function testListForms_ReturnError($params)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->listForms($params);
    }

    // ------------------------------------------------------------------
    // createForm
    // ------------------------------------------------------------------

    public function createFormDataProvider_Success()
    {
        return [
            ['Paubox PHP SDK Test Form', ['pages' => []], 0] // Replace 0 with your customer ID
        ];
    }

    /**
     * @dataProvider createFormDataProvider_Success
     * @group integration
     * @group destructive
     */
    public function testCreateForm_ReturnSuccess($title, $formJson, $customerId)
    {
        $this->skipIfNoApiKey();

        $form = new Form();
        $form->setTitle($title);
        $form->setFormJson($formJson);
        $form->setCustomerId($customerId);
        $form->setDescription('Created by the Paubox PHP SDK test suite');
        $form->setActive(true);

        $newFormId = $this->forms->createForm($form);
        $this->assertNotEmpty($newFormId);
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
            'missing title'       => [$missingTitle],
            'missing formJson'    => [$missingFormJson],
            'missing customerId'  => [$missingCustomerId]
        ];
    }

    /**
     * Validation happens before any HTTP call, so this runs without
     * network access or credentials and must never be skipped.
     *
     * @dataProvider createFormValidationDataProvider
     */
    public function testCreateForm_ThrowsOnMissingRequiredField($form)
    {
        $this->expectException(\Exception::class);
        $this->forms->createForm($form);
    }

    // ------------------------------------------------------------------
    // updateForm
    // ------------------------------------------------------------------

    public function updateFormDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', ['title' => 'Updated by PHP SDK Test', 'description' => 'Updated description']]
        ];
    }

    public function updateFormDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', ['title' => 'Updated Title']]
        ];
    }

    /**
     * @dataProvider updateFormDataProvider_Success
     * @group integration
     * @group destructive
     */
    public function testUpdateForm_ReturnSuccess($formId, $attributes)
    {
        $this->skipIfNoApiKey();
        $result = $this->forms->updateForm($formId, $attributes);
        if (is_null($result) || !isset($result->detail)) {
            $this->fail('Expected response with detail');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider updateFormDataProvider_NotFound
     * @group integration
     */
    public function testUpdateForm_ReturnNotFound($formId, $attributes)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->updateForm($formId, $attributes);
    }

    /**
     * The allowed-keys filter throws before any HTTP call, so this runs
     * without network access or credentials and must never be skipped.
     *
     */
    public function testUpdateForm_ThrowsWhenNoAllowedAttributes()
    {
        $this->expectException(\Exception::class);
        $this->forms->updateForm('00000000-0000-0000-0000-000000000000', ['bogus_key' => 'value']);
    }

    // ------------------------------------------------------------------
    // archiveForm / unarchiveForm
    // ------------------------------------------------------------------

    public function archiveFormDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE']
        ];
    }

    public function archiveFormDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000']
        ];
    }

    /**
     * @dataProvider archiveFormDataProvider_Success
     * @group integration
     * @group destructive
     */
    public function testArchiveAndUnarchiveForm_ReturnSuccess($formId)
    {
        $this->skipIfNoApiKey();
        $this->assertTrue($this->forms->archiveForm($formId));
        $this->assertTrue($this->forms->unarchiveForm($formId));
    }

    /**
     * @dataProvider archiveFormDataProvider_NotFound
     * @group integration
     */
    public function testArchiveForm_ReturnNotFound($formId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->archiveForm($formId);
    }

    /**
     * @dataProvider archiveFormDataProvider_NotFound
     * @group integration
     */
    public function testUnarchiveForm_ReturnNotFound($formId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->unarchiveForm($formId);
    }

    // ------------------------------------------------------------------
    // copyForm
    // ------------------------------------------------------------------

    public function copyFormDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', 'Copied by PHP SDK Test']
        ];
    }

    public function copyFormDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', 'Copy of Missing Form']
        ];
    }

    /**
     * @dataProvider copyFormDataProvider_Success
     * @group integration
     * @group destructive
     */
    public function testCopyForm_ReturnSuccess($formId, $newTitle)
    {
        $this->skipIfNoApiKey();
        $newForm = $this->forms->copyForm($formId, $newTitle);
        if (is_null($newForm) || !isset($newForm->id)) {
            $this->fail('Expected new form object with id');
        } else {
            $this->assertNotEquals($formId, $newForm->id);
        }
    }

    /**
     * @dataProvider copyFormDataProvider_NotFound
     * @group integration
     */
    public function testCopyForm_ReturnNotFound($formId, $newTitle)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->copyForm($formId, $newTitle);
    }

    // ------------------------------------------------------------------
    // getFormStats
    // ------------------------------------------------------------------

    public function getFormStatsDataProvider_Error()
    {
        return [
            [['customer_id' => 'not-a-number']]
        ];
    }

    /**
     * @group integration
     */
    public function testGetFormStats_ReturnSuccess()
    {
        $this->skipIfNoApiKey();
        $stats = $this->forms->getFormStats();
        if (is_null($stats)
            || !isset($stats->active_form_count)
            || !isset($stats->total_submission_count)
            || !isset($stats->submissions_last_7_days)) {
            $this->fail('Expected stats with active_form_count, total_submission_count and submissions_last_7_days');
        } else {
            $this->assertTrue(true);
        }
    }

    /**
     * @dataProvider getFormStatsDataProvider_Error
     * @group integration
     */
    public function testGetFormStats_ReturnError($params)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->getFormStats($params);
    }

    // ------------------------------------------------------------------
    // listSubmissions
    // ------------------------------------------------------------------

    public function listSubmissionsDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', []],
            ['YOUR-VALID-FORM-UUID-HERE', ['page' => 1, 'items' => 5, 'order' => 'desc', 'order_by' => 'created_at']]
        ];
    }

    public function listSubmissionsDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000', []]
        ];
    }

    /**
     * @dataProvider listSubmissionsDataProvider_Success
     * @group integration
     */
    public function testListSubmissions_ReturnSuccess($formId, $params)
    {
        $this->skipIfNoApiKey();
        $result = $this->forms->listSubmissions($formId, $params);
        if (is_null($result) || !isset($result->data) || !isset($result->total)) {
            $this->fail('Expected response with data and total');
        } else {
            $this->assertTrue(is_array($result->data));
        }
    }

    /**
     * @dataProvider listSubmissionsDataProvider_NotFound
     * @group integration
     */
    public function testListSubmissions_ReturnNotFound($formId, $params)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->listSubmissions($formId, $params);
    }

    // ------------------------------------------------------------------
    // getSubmissionsCsv / getSubmissionCsv / getSubmissionPdf
    // ------------------------------------------------------------------

    public function submissionsCsvDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE']
        ];
    }

    public function submissionsCsvDataProvider_NotFound()
    {
        return [
            ['00000000-0000-0000-0000-000000000000']
        ];
    }

    public function submissionCsvDataProvider_Success()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', 'YOUR-VALID-SUBMISSION-UUID-HERE']
        ];
    }

    public function submissionCsvDataProvider_NotFound()
    {
        return [
            ['YOUR-VALID-FORM-UUID-HERE', '00000000-0000-0000-0000-000000000000']
        ];
    }

    /**
     * @dataProvider submissionsCsvDataProvider_Success
     * @group integration
     */
    public function testGetSubmissionsCsv_ReturnSuccess($formId)
    {
        $this->skipIfNoApiKey();
        $csv = $this->forms->getSubmissionsCsv($formId);
        $this->assertIsString($csv);
        $this->assertNotEmpty($csv);
    }

    /**
     * @dataProvider submissionsCsvDataProvider_NotFound
     * @group integration
     */
    public function testGetSubmissionsCsv_ReturnNotFound($formId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->getSubmissionsCsv($formId);
    }

    /**
     * @dataProvider submissionCsvDataProvider_Success
     * @group integration
     */
    public function testGetSubmissionCsv_ReturnSuccess($formId, $submissionId)
    {
        $this->skipIfNoApiKey();
        $csv = $this->forms->getSubmissionCsv($formId, $submissionId);
        $this->assertIsString($csv);
        $this->assertNotEmpty($csv);
    }

    /**
     * @dataProvider submissionCsvDataProvider_NotFound
     * @group integration
     */
    public function testGetSubmissionCsv_ReturnNotFound($formId, $submissionId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->getSubmissionCsv($formId, $submissionId);
    }

    /**
     * @dataProvider submissionCsvDataProvider_Success
     * @group integration
     */
    public function testGetSubmissionPdf_ReturnSuccess($formId, $submissionId)
    {
        $this->skipIfNoApiKey();
        $pdf = $this->forms->getSubmissionPdf($formId, $submissionId);
        $this->assertIsString($pdf);
        $this->assertNotEmpty($pdf);
        $this->assertSame('%PDF', substr($pdf, 0, 4));
    }

    /**
     * @dataProvider submissionCsvDataProvider_NotFound
     * @group integration
     */
    public function testGetSubmissionPdf_ReturnNotFound($formId, $submissionId)
    {
        $this->skipIfNoApiKey();
        $this->expectException(\Exception::class);
        $this->forms->getSubmissionPdf($formId, $submissionId);
    }
}
?>
