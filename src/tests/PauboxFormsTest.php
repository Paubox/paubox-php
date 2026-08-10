<?php
use PHPUnit\Framework\TestCase;
use Paubox\PauboxForms;
use Paubox\Forms\FormSubmission;

require_once dirname(dirname(__DIR__)) . '/vendor/autoload.php';
require_once dirname(__DIR__) . "/PauboxForms.php";
require_once dirname(__DIR__) . "/forms/FormSubmission.php";

class PauboxFormsTest extends TestCase
{
    private $forms;

    public function setUp()
    {
        $this->forms = new PauboxForms();
    }

    public function tearDown()
    {
        $this->forms = null;
        parent::tearDown();
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
     * @expectedException \Exception
     */
    public function testGetForm_ReturnNotFound($formId)
    {
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
     * @expectedException \Exception
     */
    public function testSubmitForm_ReturnError($formId, $formData)
    {
        $submission = new FormSubmission();
        $submission->setFormData($formData);
        $this->forms->submitForm($formId, $submission);
    }
}
?>
