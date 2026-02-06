<?php

/**
 * Copyright 2024 AlexaCRM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace AlexaCRM\Nextgen;

use AlexaCRM\Nextgen\Twig\User;
use AlexaCRM\WebAPI\OData\AuthenticationException;
use AlexaCRM\WebAPI\OrganizationException;
use AlexaCRM\WebAPI\ToolkitException;
use AlexaCRM\Xrm\Entity as XrmEntity;
use AlexaCRM\Xrm\Metadata\DateTimeAttributeMetadata;
use AlexaCRM\Xrm\Metadata\DateTimeBehavior;
use AlexaCRM\Xrm\Metadata\DateTimeFormat;
use AlexaCRM\Xrm\Metadata\EntityMetadata;
use DateTimeInterface;
use DateTimeZone;

/**
 * Class Entity
 * Represents a record in Dynamics 365.
 *
 * @package AlexaCRM\Nextgen
 */
class Entity extends XrmEntity {

    public const DATETIME_LEGACY = 'legacy';

    public const DATETIME_UTC = 'utc';

    public const DATETIME_LOCAL = 'local';

    public ?EntityMetadata $metadata = null;

    public ?XrmEntity $updatedEntity = null;

    public ?string $dateTimeMode = null;

    public ?string $icdsUserTimezone = null;

    public ?DateTimeZone $userTimezone = null;

    /**
     * Entity constructor.
     *
     * @param string|null $entityName
     * @param $entityId
     * @param $keyValue
     * @param string|null $dateTimeValue
     *
     * @throws \Exception
     */
    public function __construct( ?string $entityName = null, $entityId = null, $keyValue = null, ?string $dateTimeValue = null ) {

        $this->dateTimeMode = $dateTimeValue;
        if ( !$this->dateTimeMode ) {
            $this->dateTimeMode = AdvancedSettingsProvider::instance( 'ICDS_DATETIME_MODE' )->getValue();
        }

        if ( class_exists( User::class ) ) {
            $this->icdsUserTimezone = ( new User() )->timezone();
        }
        $this->userTimezone = $this->getUserTimeZone();

        parent::__construct( $entityName, $entityId, $keyValue );
    }

    /**
     * @param string $name
     *
     * @return mixed|string|null
     */
    public function __get( string $name ) {

        $attributeName = $this->getAttributeName( $name );
        if ( !$this->metadata ) {
            return $this->Attributes[ $attributeName ];
        }

        try {
            $attrMetaData = $this->metadata->Attributes[ $attributeName ] ?? null;
            if ( !$attrMetaData instanceof DateTimeAttributeMetadata || $attrMetaData?->DateTimeBehavior?->Value !== DateTimeBehavior::UserLocal ) {
                return $this->Attributes[ $attributeName ] . 'Z';
            }

            $dateValue = new \DateTimeImmutable( $this->Attributes[ $attributeName ] );
        } catch ( \Exception ) {
            return null;
        }

        if ( !str_contains( $name, '_utc' ) ) {
            $timeZoneOffset = timezone_offset_get( $this->userTimezone, $dateValue );
            if ( is_numeric( $timeZoneOffset ) ) {
                $dateValue = $dateValue->setTimestamp( $dateValue->getTimestamp() + (int)$timeZoneOffset );
            }
        }

        if ( str_contains( $name, '_local_time' ) || str_contains( $name, '_utc_time' ) ) {
            return $dateValue->format( 'H:i:s' );
        }
        if ( str_contains( $name, '_local_date' ) || str_contains( $name, '_utc_date' ) ) {
            return $dateValue->format( 'Y-m-d' );
        }

        return $dateValue->format( DateTimeInterface::ATOM );
    }

    /**
     * @param string $name
     * @param $value
     *
     * @return void
     * @throws \Exception
     */
    public function __set( string $name, $value ): void {

        if ( !$this->metadata ) {
            return;
        }
        $attributeName = $this->getAttributeName( $name );
        $attrMetaData = $this->metadata->Attributes[ $attributeName ] ?? null;

        if ( !$attrMetaData instanceof DateTimeAttributeMetadata || !$value ) {
            return;
        }

        $dateValue = new \DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
        if ( $attrMetaData->DateTimeBehavior->Value === DateTimeBehavior::UserLocal ) {
            if ( $attrMetaData->Format->getValue() === DateTimeFormat::DateOnly && $this->updatedEntity ) {
                if ( $this->isUtcDateTimeAttributeName( $name ) ) {
                    $attrTimeName = $attributeName . '_utc_time';
                } else {
                    $attrTimeName = $attributeName . '_local_time';
                }
//                $timeDiff = $this->updatedEntity->Attributes [ $attrTimeName ] ?? '00:00:00';
//                $dateValue = new \DateTimeImmutable( $dateValue->format( 'Y-m-d' ) . 'T' . $timeDiff . 'Z' );
                $dateValue = new \DateTimeImmutable( $dateValue->format( 'Y-m-d' ) . 'T00:00:00Z' );
            }

            if ( !$this->isUtcDateTimeAttributeName( $name ) ) {
                $timeZoneOffset = timezone_offset_get( $this->userTimezone, $dateValue );
                $dateValue = $dateValue->setTimestamp( $dateValue->getTimestamp() - $timeZoneOffset );
            }
        }

        if ( $attrMetaData->DateTimeBehavior->Value === DateTimeBehavior::DateOnly ) {
            $this->SetAttributeValue( $attributeName, $dateValue->format( 'Y-m-d' ) );
        } else {
            if ( $attrMetaData->DateTimeBehavior->Value === DateTimeBehavior::TimeZoneIndependent ||
                 ( ( str_contains( $name, '_time' ) ) && $this->updatedEntity ) ) {
                $dateValue = $this->getCorrectDateTime( $name, $dateValue );
            }
            $this->SetAttributeValue( $attributeName, $dateValue->format( 'Y-m-d\TH:i:s' ) . 'Z' );
        }
    }

    /**
     * @return string
     */
    public function __toString(): string {
        try {
            $metadata = MetadataService::instance()->getRegistry()->getDefinition( $this->LogicalName );
        } catch ( \Exception ) {
            return '';
        }

        return (string)$this->GetAttributeValue( $metadata->PrimaryNameAttribute );
    }

    /**
     * @param XrmEntity $record
     * @param XrmEntity|null $updatedEntity
     *
     * @return Entity
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     * @throws \Exception
     */
    public function toEntity( XrmEntity $record, ?XrmEntity $updatedEntity = null, bool $crmCreate = false ): XrmEntity {

        $this->Attributes = $record->Attributes;
        $this->updatedEntity = $updatedEntity;

        $this->metadata = MetadataService::instance()->getRegistry()->getDefinition( $record->LogicalName );

        foreach ( $this->Attributes as $attributeName => $value ) {
            $attrMetaData = $this->metadata->Attributes[ $attributeName ] ?? null;

            if ( empty( $value ) && $attrMetaData instanceof DateTimeAttributeMetadata ) {
                $this->SetAttributeValue( $this->getAttributeName( $attributeName ), null );
                continue;
            }

            if ( ( $this->isLocalDateAttributeName( $attributeName ) || $this->isUtcDateTimeAttributeName( $attributeName ) || $crmCreate)
                 && $attrMetaData instanceof DateTimeAttributeMetadata ) {
                if ( str_contains( $attributeName, '_local_time' ) || str_contains( $attributeName, '_utc_time' ) ) {
                    $attributeDateName = str_replace( [ '_local_time', '_utc_time' ],
                        [ '_local_date', '_utc_date' ], $attributeName );
                    if ( isset( $this->Attributes[ $attributeDateName ] ) ) {
                        $value = $this->Attributes[ $attributeDateName ] . ' ' . $value;
                    }
                }
                if ( str_contains( $attributeName, '_local_date' ) || str_contains( $attributeName, '_utc_date' ) ) {
                    $attributeDateName = str_replace( [ '_local_date', '_utc_date' ],
                        [ '_local_time', '_utc_time', ], $attributeName );
                    if ( isset( $this->Attributes[ $attributeDateName ] ) ) {
                        $value = $value . ' ' . $this->Attributes[ $attributeDateName ];
                    }
                }
                $this->$attributeName = $value;
            } else {
                if ( str_contains( $attributeName, '_time' ) && $value ) {
                    $dateValue = new \DateTimeImmutable( $value, new DateTimeZone( 'UTC' ) );
                    $dateValue = $this->getCorrectDateTime( $attributeName, $dateValue );
                    $this->SetAttributeValue( $this->getAttributeName( $attributeName ), $dateValue->format( 'Y-m-d\TH:i:s' ) . 'Z' );
                }
            }
        }
        $record->Attributes = $this->Attributes;
        foreach ( $this->getAttributeState() as $fieldName => $_ ) {
            $record->attributeState[ $fieldName ] = true;
        }

        return $record;
    }

    /**
     * @param XrmEntity $record
     * @param EntityMetadata|null $entityMetadata
     *
     * @return Entity
     * @throws AuthenticationException
     * @throws OrganizationException
     * @throws ToolkitException
     */

    public function createFromEntity( XrmEntity $record, ?EntityMetadata $entityMetadata = null ): Entity {
        $result = new Entity();
        $this->Attributes = $record->Attributes;

        if ( !$entityMetadata ) {
            $this->metadata = MetadataService::instance()->getRegistry()->getDefinition( $record->LogicalName );
            if ( $this->metadata === null ) {
                return new Entity();
            }
        } else {
            $this->metadata = $entityMetadata;
        }

        $hasNoLimitTableExpansion = TwigProvider::isLimitTableExpansion() === false;
        foreach ( get_object_vars( $record ) as $propertyName => $propertyValue ) {
            if ( $propertyName !== 'Attributes' || !is_array( $propertyValue ) ) {
                $result->$propertyName = $propertyValue;
                continue;
            }

            //work only with Attributes
            foreach ( $propertyValue as $attributeName => $attributeValue ) {
                if ( $hasNoLimitTableExpansion ) {
                    [ $alias, $field ] = array_pad( explode( '.', $attributeName ), 2, null );
                    if ( $field ) {
                        $propertyValue[ $alias ][ $field ] = $attributeValue;
                    }
                }

                $attrMetaData = $this->metadata->Attributes[ $attributeName ] ?? null;
                if ( $attrMetaData instanceof DateTimeAttributeMetadata ) {
                    if ( $attrMetaData->DateTimeBehavior->Value === DateTimeBehavior::UserLocal ) {
                        $propertyValue[ $attributeName . '_local_time' ] = $this->{$attributeName . '_local_time'};
                        $propertyValue[ $attributeName . '_local_date' ] = $this->{$attributeName . '_local_date'};
                        $propertyValue[ $attributeName . '_local' ] = $this->{$attributeName . '_local'};

                        $propertyValue[ $attributeName . '_utc_time' ] = $this->{$attributeName . '_utc_time'};
                        $propertyValue[ $attributeName . '_utc_date' ] = $this->{$attributeName . '_utc_date'};
                        $propertyValue[ $attributeName . '_utc' ] = $this->{$attributeName . '_utc'};
                    }

                    if ( $attrMetaData->DateTimeBehavior->Value === DateTimeBehavior::DateOnly ) {
                        try {
                            $dateValue = new \DateTimeImmutable( $attributeValue );
//                            $propertyValue[ $attributeName ] = $dateValue->format( 'Y-m-d' );
                            $propertyValue[ $attributeName ] = $dateValue->format( 'Y-m-d\T00:00:00.000\Z' );
                        } catch ( \Exception ) {
                            $propertyValue[ $attributeName ] = null;
                        }
                    }
                }
            }
            $result->$propertyName = $propertyValue;
        }

        if ( $this->dateTimeMode === Entity::DATETIME_LEGACY ) {
            return $result;
        }

        return $this->convertDateTimeValue( $result, $this->dateTimeMode );
    }

    public function convertDateTimeValue( $entity, $dateTimeMode = null ) {
        if ( !$dateTimeMode ) {
            $dateTimeMode = AdvancedSettingsProvider::instance( 'ICDS_DATETIME_MODE' )->getValue();
        }

        foreach ( $entity->Attributes as $attributeName => $attributeValue ) {
            if ( str_contains( $attributeName, '_' . $dateTimeMode ) ) {
                $entity->Attributes[ $this->getAttributeName( $attributeName ) ] = $entity->Attributes[ $attributeName ];
            }
        }

        return $entity;
    }

    /**
     * Gets the formatted value of the attribute.
     *
     * Returns empty string if the entity doesn't have the specified formatted value.
     *
     * @param string $attribute
     *
     * @return string
     */
    public function GetFormattedAttributeValue( string $attribute ): string {
        if ( !array_key_exists( $attribute, $this->FormattedValues ) ) {
            return '';
        }

        return $this->FormattedValues[ $attribute ];
    }

    /**
     * Tells whether specified attribute value exists.
     *
     * @param string $attribute
     *
     * @return bool
     */
    public function Contains( string $attribute ): bool {
        return array_key_exists( $attribute, $this->Attributes );
    }

    /**
     * Get correct Date or Time or DateTime for attribute name
     *
     * @param $name
     * @param $dateValue
     *
     * @return \DateTimeImmutable|false
     * @throws \Exception
     */
    private function getCorrectDateTime( $name, $dateValue ) {
        $attributeValue = new \DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
        $attributeName = $this->getAttributeName( $name );

        if ( isset( $this->Attributes[ $attributeName ] ) ) {
            try {
                $attributeValue = new \DateTimeImmutable( $this->Attributes[ $attributeName ] );
            } catch ( \Exception ) {
                $attributeValue = new \DateTimeImmutable( 'now', new DateTimeZone( 'UTC' ) );
            }
        }
        if ( str_contains( $name, '_local_time' ) || str_contains( $name, '_time' ) || str_contains( $name, '_utc_time' ) ) {
            $dateValue = $attributeValue->setTime( $dateValue->format( 'H' ), $dateValue->format( 'i' ), $dateValue->format( 's' ) );
        } elseif ( str_contains( $name, '_local_date' ) || str_contains( $name, '_utc_date' ) ) {
            $dateValue = $attributeValue->setDate( $dateValue->format( 'Y' ), $dateValue->format( 'm' ), $dateValue->format( 'd' ) );
        } else {
            $dateValue = $attributeValue;
        }

        return $dateValue;
    }

    /**
     * Get real attribute name
     *
     * @param string $name
     *
     * @return string
     */
    private function getAttributeName( string $name ): string {
        return str_replace( [
            '_local_time',
            '_local_date',
            '_local',
            '_utc_time',
            '_utc_date',
            '_utc',
            '_time',
        ], '', $name );
    }

    /**
     * Is dateTime attribute name
     *
     * @param string $name
     *
     * @return bool
     */
    private function isLocalDateAttributeName( string $name ): bool {
        $dateTimeAttrName = [ '_local_time', '_local_date', '_local' ];
        foreach ( $dateTimeAttrName as $attr ) {
            if ( str_contains( $name, $attr ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Is dateTime attribute name
     *
     * @param string $name
     *
     * @return bool
     */
    private function isUtcDateTimeAttributeName( string $name ): bool {
        $dateTimeAttrName = [ '_utc_time', '_utc_date' ];
        foreach ( $dateTimeAttrName as $attr ) {
            if ( str_contains( $name, $attr ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get User or default time zone
     *
     * @return DateTimeZone
     * @throws \Exception
     */
    private function getUserTimeZone(): DateTimeZone {

        if ( str_contains( $this->icdsUserTimezone, 'UTC' ) ) {
            $tzFound = preg_match( '/^UTC([+-])((\d{1,2}[:]\d{1,2})|(\d{1,2}))$/s', $this->icdsUserTimezone, $matches );
            if ( $tzFound ) {
                $tz = $matches[1] . $matches[2];
            } else {
                $tz = 'UTC';
            }

            return new DateTimeZone( $tz );
        } elseif ( !empty( $this->icdsUserTimezone ) ) {
            return new DateTimeZone ( $this->icdsUserTimezone );
        }

        return wp_timezone();
    }

}
