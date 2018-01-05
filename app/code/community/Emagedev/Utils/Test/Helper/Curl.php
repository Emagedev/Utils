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
 *
 * @category   Emagedev
 * @package    Emagedev_Utils
 * @subpackage Unit Test
 * @author     Dmitry Burlakov <dantaeusb@icloud.com>
 */
class Emagedev_Utils_Test_Helper_Curl extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Check session flag works correctly
     *
     * @param string       $responseText
     * @param false|string $error should helper throw error?
     *
     * @throws Mage_Core_Exception
     *
     * @dataProvider dataProvider
     * @test
     * */
    public function checkHttpRequestDispatching($responseText, $error)
    {
        $responseText = stripcslashes($responseText);

        try {
            $response = $this->getHelper()->dispatchResponse($responseText);
        } catch (Mage_Core_Exception $e) {
            if ($error) {
                $this->assertEquals($this->expected('auto')->getError(), $e->getMessage());
            } else {
                throw $e;
            }
        }

        if (!$error || empty($error)) {
            $this->assertEquals($this->expected('auto')->getCode(), $response->getCode());
            $this->assertEquals($this->expected('auto')->getBody(), $response->getBody());
            $this->assertEquals(
                $this->expected('auto')->getContentType(),
                $response->getHeaders()['Content-Type']
            );
        }
    }

    /**
     * Get helper fot tests
     *
     * @return Omedrec_Welcome_Helper_Curl
     */
    protected function getHelper()
    {
        return Mage::helper('omedrec_welcome/curl');
    }
}