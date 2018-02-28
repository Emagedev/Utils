<?php
/**
 * Emagedev extension for Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade
 * the Omedrec Welcome module to newer versions in the future.
 * If you wish to customize the Omedrec Welcome module for your needs
 * please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (C) Emagedev, LLC (https://www.emagedev.com/)
 * @license    https://opensource.org/licenses/BSD-3-Clause     New BSD License
 */

/**
 * @category   Emagedev
 * @package    Emagedev_Utils
 * @subpackage Helper
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */

/**
 * Class Emagedev_Utils_Helper_Curl
 *
 * Fix problems with native curl module in php/hhvm:
 * Sometimes in some environments instead of data, curl_getinfo returns 1 like CURLOPT_RETURNTRANSFER
 * not set (but in fact it is, see Varien_Http_Adapter_Curl::write)
 *
 * I have no better idea than make custom helper to dispatch HTTP Response and get HTTP
 * code, errors, separate head and body, whatever: so here it goes
 *
 * @see Varien_Http_Adapter_Curl::write
 */
class Emagedev_Utils_Helper_Curl extends Mage_Core_Helper_Abstract
{
    /**
     * Get response code, headers, status, body
     *
     * @param string $response
     *
     * @return Omedrec_Welcome_Model_Curl_Response
     */
    public function dispatchResponse($response)
    {
        $parts = explode("\r\n\r\n", $response, 2);

        if (count($parts) < 2) {
            $this->throwException();
        }

        list($headersAsString, $body) = $parts;

        $headers = explode("\r\n", $headersAsString);
        list($code, $status) = $this->dispatchHttpStatus(array_shift($headers));
        $headers = $this->dispatchHeaders($headers);

        /** @var Emagedev_Utils_Model_Curl_Response $responseObject */
        $responseObject = Mage::getModel('emagedev_utils/curl_response');

        return $responseObject->setData(
            array(
                'code' => $code,
                'status' => $status,
                'headers_as_string' => $headersAsString,
                'headers' => $headers,
                'body' => $body
            )
        );
    }

    /**
     * Get HTTP status and code from first header
     *
     * @param string $header
     *
     * @return array
     */
    protected function dispatchHttpStatus($header)
    {
         $statuses = array();
         $result = preg_match('/^http\/[1|2]\.\d (\d{3}) (.*)$/i', $header, $statuses);

         if ($result < 1) {
             $this->throwException();
         }

         // Code, status text
         return array((int)$statuses[1], $statuses[2]);
    }

    /**
     * Get headers an values
     *
     * @param $headersAsString
     *
     * @return array
     */
    protected function dispatchHeaders($headersAsString)
    {
        $headers = array();

        foreach ($headersAsString as $header) {
            $headerData = $this->dispatchHeaderValue($header);

            if (is_null($headerData)) {
                continue;
            }

            $headers[$headerData['name']] = $headerData['value'];
        }

        return $headers;
    }

    /**
     * Get key-value array
     *
     * @see https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request
     *
     * @param $header
     *
     * @return array|null
     */
    protected function dispatchHeaderValue($header)
    {
        $header = explode(':', $header, 2);

        if (count($header) < 2) {
            return null;
        }

        return array(
            'name' => trim($header[0]),
            'value' => trim($header[1]),
        );
    }

    /**
     * Fire exception in unhandled situation
     */
    protected function throwException()
    {
        Mage::throwException('Invalid HTTP response, cannot fetch status');
    }
}