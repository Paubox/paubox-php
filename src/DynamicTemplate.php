
<?php

namespace Paubox;

use CURLFile;

class DynamicTemplate {

  private function getURL($uri)
  {
    $base_url = "https://api.paubox.net/v1/";
    $base_url .= \getenv('PAUBOX_API_USER');
    $base_url .= "/";
    $base_url .= $uri;
    return $base_url;
  }

  private function getAuthentication()
  {
    $token = "Token token=";
    $token .= \getenv('PAUBOX_API_KEY');
    return $token;
  }

  public function createDynamicTemplate($templateName, $templateFilePath) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("dynamic_templates"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array(
        'data[body]' => new CURLFile($templateFilePath),
        'data[name]' => $templateName
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: ' . $this->getAuthentication()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  public function updateDynamicTemplate($templateId, $templateName, $templateFilePath) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("dynamic_templates/$templateId"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'PATCH',
      CURLOPT_POSTFIELDS => array(
        'data[body]' => new CURLFile($templateFilePath),
        'data[name]' => $templateName
      ),
      CURLOPT_HTTPHEADER => array(
        'Authorization: ' . $this->getAuthentication()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  public function deleteDynamicTemplate($templateId) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("dynamic_templates/$templateId"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'DELETE',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: ' . $this->getAuthentication()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  public function getAllDynamicTemplates() {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("dynamic_templates"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: ' . $this->getAuthentication()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  public function getDynamicTemplate($templateId) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("dynamic_templates/$templateId"),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: ' . $this->getAuthentication()
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }
  
}
?>