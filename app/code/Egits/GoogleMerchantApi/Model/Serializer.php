<?php
/**
 *  Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */

namespace Egits\GoogleMerchantApi\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Unserialize\Unserialize;

/**
 * Wrapper for Serialize
 * Class Serializer
 */
class Serializer
{
    /**
     * @var null|SerializerInterface
     */
    protected $serializer;

    /**
     * @var Unserialize
     */
    protected $unserialize;

    /**
     * Serializer constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param Unserialize $unserialize
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Unserialize $unserialize
    ) {
        if (interface_exists(SerializerInterface::class)) {
            // for magento later then 2.2
            $this->serializer = $objectManager->get(SerializerInterface::class);
        }

        $this->unserialize = $unserialize;
    }

    /**
     * Method serialize
     *
     * @param array $value
     * @return bool|string
     */
    public function serialize($value)
    {
        return $this->serializer->serialize($value);
    }

    /**
     * Method unserialize
     *
     * @param string $value
     * @return array|bool|float|int|mixed|null|string
     */
    public function unserialize($value)
    {
        if ($this->serializer === null) {
            return $this->unserialize->unserialize($value);
        }

        return $this->serializer->unserialize($value);
    }
}
