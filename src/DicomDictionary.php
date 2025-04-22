<?php

declare(strict_types=1);

namespace Aurabx\DicomData;

/**
 * Provides static access to DICOM tag lookup
 */
class DicomDictionary
{
    private static ?DicomTagLoader $loader = null;

    /**
     * @param  DicomTagLoader  $customLoader
     * @return void
     */
    public static function preload(DicomTagLoader $customLoader): void
    {
        self::$loader = $customLoader;
    }

    /**
     * @return DicomTagLoader
     */
    public static function getLoader(): DicomTagLoader
    {
        if (!self::$loader) {
            self::$loader = new DicomTagLoader();
        }

        return self::$loader;
    }

    /**
     * Check if a tag exists in the known tags dictionary
     *
     * @param string $tag DICOM tag
     * @return bool
     */
    public static function isKnownAttribute(string $tag): bool
    {
        return self::getLoader()->getAttributeName($tag) !== null;
    }

    /**
     * Get the tag ID for a descriptive name
     *
     * @param  string  $name
     * @return string|null
     */
    public static function getAttributeByName(string $name): ?array
    {
        return self::getLoader()->getAttributeByName($name);
    }

    /**
     * Get the descriptive name for a tag
     *
     * @param  string  $tagId
     * @return string|null
     */
    public static function getAttributeName(string $tagId): ?string
    {
        return self::getLoader()->getAttributeName($tagId);
    }

    /**
     * @param  string  $tagId
     * @return array|null
     */
    public static function getAttributeInfo(string $tagId): ?array
    {
        return self::getLoader()->getAttribute($tagId);
    }

    /**
     * Get the Value Representation (VR) for a tag
     *
     * @param  string  $tagId
     * @return string|null
     */
    public static function getAttributeVr(string $tagId): ?string
    {
        return self::getLoader()->getAttributeVr($tagId);
    }

    /**
     * Get the Value Representation (VR) for a tag
     *
     * @param  string  $tagId
     * @return string|null
     */
    public static function getAttributeVm(string $tagId): ?string
    {
        return self::getLoader()->getAttributeVm($tagId);
    }

    public static function getAttributeKeyword(string $tagId): ?string
    {
        return self::getLoader()->getAttributeKeyword($tagId);
    }

    public static function getVrMeaning(string $vr): ?string
    {
        return self::getLoader()->getVrMeaning($vr);
    }

    public static function getAllAttributes(): array
    {
        return self::getLoader()->getAllAttributes();
    }

    public static function getAllVRs(): array
    {
        return self::getLoader()->getAllVRs();
    }
}
