<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package    Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class GoogleDebug
 * Custom debug log
 */
class GoogleDebug extends Base
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/google/debug.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;
}
