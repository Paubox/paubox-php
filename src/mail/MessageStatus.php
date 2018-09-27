<?php
namespace Paubox\Mail;

class MessageStatus
{

    private  $_deliveryStatus;
    private  $_deliveryTime;
    private  $_openedStatus;
    private  $_openedTime;
    /**
     * @return $_deliveryStatus
     */
    public function  getDeliveryStatus() {
        return $this->_deliveryStatus;
    }
    /**
     * @param deliveryStatus the deliveryStatus to set
     */
    public function setDeliveryStatus($deliveryStatus) {
        $this->_deliveryStatus = $deliveryStatus;
    }
    /**
     * @return $_deliveryTime
     */
    public function getDeliveryTime() {
        return $this->_deliveryTime;
    }
    /**
     * @param deliveryTime the deliveryTime to set
     */
    public function setDeliveryTime($deliveryTime) {
        $this->_deliveryTime = $deliveryTime;
    }
    /**
     * @return $_openedStatus
     */
    public function getOpenedStatus() {
        return $this->_openedStatus;
    }
    /**
     * @param openedStatus the openedStatus to set
     */
    public function setOpenedStatus($openedStatus) {
        $this->_openedStatus = $openedStatus;
    }
    /**
     * @return $_openedTime
     */
    public function getOpenedTime() {
        return $this->_openedTime;
    }
    /**
     * @param openedTime the openedTime to set
     */
    public function setOpenedTime($openedTime) {
        $this->_openedTime = $openedTime;
    }

}

