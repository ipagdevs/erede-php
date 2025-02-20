<?php

namespace Rede;

use DateTime;
use Exception;

trait CreateTrait
{
    /**
     * @param object $data
     *
     * @return object
     * @throws Exception
     */
    public static function create(object $data): object
    {
        $object = new self();
        $dataKeys = get_object_vars($data);
        $objectKeys = get_object_vars($object);

        foreach ($dataKeys as $property => $value) {
            if (array_key_exists($property, $objectKeys)) {
                $value = self::mapPropertyToObject($property, $value);

                $object->{$property} = $value;
            }
        }

        return $object;
    }

    private static function mapPropertyToObject($property, mixed $value): mixed
    {
        return match ($property) {
            'requestDateTime', 'dateTime', 'refundDateTime' => new DateTime($value),
            'brand' => Brand::create($value),
            'billing' => Billing::create($value),
            default => $value,
        };
    }
}
