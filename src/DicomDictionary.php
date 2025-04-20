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
    public static function isKnownTag(string $tag): bool
    {
        return self::getLoader()->getTagName($tag) !== null;
    }

    /**
     * Get the tag ID for a descriptive name
     *
     * @param  string  $name
     * @return string|null
     */
    public static function getTagIdByName(string $name): ?string
    {
        return self::getLoader()->getTagByName($name);
    }

    /**
     * Get the descriptive name for a tag
     *
     * @param  string  $tagId
     * @return string|null
     */
    public static function getTagName(string $tagId): ?string
    {
        return self::getLoader()->getTagName($tagId);
    }

    /**
     * @param  string  $tagId
     * @return array|null
     */
    public static function getTagInfo(string $tagId): ?array
    {
        return self::getLoader()->getTag($tagId);
    }

    /**
     * Get the Value Representation (VR) for a tag
     *
     * @param  string  $tagId
     * @return string|null
     */
    public static function getTagVR(string $tagId): ?string
    {
        return self::getLoader()->getTagVR($tagId);
    }

    public static function getTagDescription(string $tagId): ?string
    {
        return self::getLoader()->getTagDescription($tagId);
    }

    public static function getVRMeaning(string $vr): ?string
    {
        return self::getLoader()->getVRMeaning($vr);
    }

    public static function getAllTags(): array
    {
        return self::getLoader()->getAllTags();
    }

    public static function getAllVRs(): array
    {
        return self::getLoader()->getAllVRs();
    }
}
