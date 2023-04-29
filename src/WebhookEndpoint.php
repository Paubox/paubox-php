
<?php

namespace Paubox;

use CURLFile;

class WebhookEndpoint {

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

  public function createWebhookEndpoint($targetUrl, $events, $active, $signingKey, $apiKey) {
    $curl = curl_init();

    $data = array(
        'target_url' => $targetUrl,
        'events' => $events,
        'active' => $active,
        'signing_key' => $signingKey,
        'api_key' => $apiKey
    );
    $data_string = json_encode($data);
    curl_setopt_array($curl, array(
        CURLOPT_URL => $this->getURL("webhook_endpoints"),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data_string,
        CURLOPT_HTTPHEADER => array(
            'Authorization: Token token=' . $this->getToken(),
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    return $response;
  }

  function updateWebhookEndpoint($endpoint_id, $target_url, $events, $active, $signing_key, $api_key) { 
    
    $data = array(
        'target_url' => $target_url,
        'events' => $events,
        'active' => $active,
        'signing_key' => $signing_key,
        'api_key' => $api_key
    );

    $data_string = json_encode($data);

    $url = $this->getURL("webhook_endpoints/{$endpoint_id}");
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
      'Authorization: Token token=' . $this->getToken(),
      'Content-Type: application/json'
    ));

    $response = curl_exec($ch);

    curl_close($ch);

    return $response;
  }

  public function deleteDynamicTemplate($templateId) {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->getURL("webhook_endpoints/$templateId"),
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
      CURLOPT_URL => $this->getURL("webhook_endpoints"),
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
      CURLOPT_URL => $this->getURL("webhook_endpoints/$templateId"),
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