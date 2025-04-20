<?php

namespace Aurabx\DicomData;

/**
 * Responsible for loading and providing access to DICOM tag definitions
 */
class DicomTagLoader
{
    /**
     * @var array<string, array<string, mixed>> Loaded tag data
     */
    private array $tagData = [];

    /**
     * @var array<string, string> Mapping from tag name to tag ID
     */
    private array $tagByName = [];

    /**
     * @var array<string, string> Value Representation codes and their meanings
     */
    private array $vrMeanings = [
        'AE' => 'Application Entity',
        'AS' => 'Age String',
        'AT' => 'Attribute Tag',
        'CS' => 'Code String',
        'DA' => 'Date',
        'DS' => 'Decimal String',
        'DT' => 'Date Time',
        'FD' => 'Floating Point Double',
        'FL' => 'Floating Point Single',
        'IS' => 'Integer String',
        'LO' => 'Long String',
        'LT' => 'Long Text',
        'OB' => 'Other Byte',
        'OD' => 'Other Double',
        'OF' => 'Other Float',
        'OL' => 'Other Long',
        'OW' => 'Other Word',
        'PN' => 'Person Name',
        'SH' => 'Short String',
        'SL' => 'Signed Long',
        'SQ' => 'Sequence of Items',
        'SS' => 'Signed Short',
        'ST' => 'Short Text',
        'TM' => 'Time',
        'UC' => 'Unlimited Characters',
        'UI' => 'Unique Identifier',
        'UL' => 'Unsigned Long',
        'UN' => 'Unknown',
        'UR' => 'URI/URL',
        'US' => 'Unsigned Short',
        'UT' => 'Unlimited Text'
    ];

    /**
     * @param  string|null  $tagsPath
     */
    public function __construct(?string $tagsPath = null)
    {
        if ($tagsPath !== null) {
            $this->loadFromFile($tagsPath);
        } else {
            $this->loadDefaultTags();
        }
    }

    /**
     * @return void
     */
    private function loadDefaultTags(): void
    {
        $resourcesDir = dirname(__DIR__) . '/resources/tags';
        $jsonFiles = glob("$resourcesDir/*.json");

        if (!empty($jsonFiles)) {
            foreach ($jsonFiles as $jsonFile) {
                $this->loadFromFile($jsonFile, false);
            }
        } else {
            throw new DicomDictionaryException("Could not find any DICOM tag definitions in default locations");
        }
    }

    /**
     * @param  string  $jsonPath
     * @param  bool  $clearExisting
     * @return void
     */
    public function loadFromFile(string $jsonPath, bool $clearExisting = true): void
    {
        if (!is_file($jsonPath)) {
            throw new DicomDictionaryException("Invalid file path: $jsonPath");
        }

        $jsonContent = file_get_contents($jsonPath);
        if ($jsonContent === false) {
            throw new DicomDictionaryException("Failed to read tag definition file: $jsonPath");
        }

        try {
            $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new DicomDictionaryException("Invalid JSON: {$e->getMessage()}");
        }

        $this->loadFromArray($data, $clearExisting);
    }

    /**
     * @param  array  $data
     * @param  bool  $clearExisting
     * @return void
     */
    public function loadFromArray(array $data, bool $clearExisting = true): void
    {
        if ($clearExisting) {
            $this->tagData = [];
            $this->tagByName = [];
        }

        foreach ($data as $tagId => $tagInfo) {
            $this->tagData[$tagId] = $tagInfo;
            if (isset($tagInfo['name'])) {
                $this->tagByName[strtolower($tagInfo['name'])] = $tagId;
            }
        }
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getTag(string $id): ?array
    {
        return $this->tagData[$this->normaliseTag($id)] ?? null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getTagName(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->tagData, $id)) {
            return $this->tagData[$id]['name'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getTagVr(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->tagData, $id)) {
            return $this->tagData[$id]['vr'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getTagDescription(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->tagData, $id)) {
            return $this->tagData[$id]['description'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getTagVm(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->tagData, $id)) {
            if (array_key_exists('vm', $this->tagData[$id])) {
                return $this->tagData[$id]['vm'];
            }

            return '1';
        }

        return null;
    }

    /**
     * @param  string  $name
     * @return string|null
     */
    public function getTagByName(string $name): ?string
    {
        return $this->tagByName[strtolower($name)] ?? null;
    }

    /**
     * @param  string  $vr
     * @return string|null
     */
    public function getVrMeaning(string $vr): ?string
    {
        return $this->vrMeanings[strtoupper($vr)] ?? null;
    }

    /**
     * @return \mixed[][]
     */
    public function getAllTags(): array
    {
        return $this->tagData;
    }

    /**
     * @return string[]
     */
    public function getAllVRs(): array
    {
        return $this->vrMeanings;
    }

    /**
     * Normalize a tag by removing any group/element separators
     *
     * @param  string  $tag  DICOM tag (e.g., "0010,0010" or "(0010,0010)")
     * @return string Normalized tag (e.g., "00100010")
     * @throws DicomDictionaryException
     */
    public function normaliseTag(string $tag): string
    {
        $normalized = preg_replace('/[^0-9A-Fa-f]/', '', $tag);

        if (strlen($normalized) === 4) {
            $normalized .= '0000';
        }

        if (strlen($normalized) !== 8) {
            throw new DicomDictionaryException("Invalid DICOM tag: $tag");
        }

        return strtoupper($normalized);
    }

    /**
     * Format a tag with a group/element separator
     *
     * @param  string  $tag  DICOM tag (e.g., "00100010")
     * @param  string  $format  Format specifier ('comma', 'paren', or 'both')
     * @return string Formatted tag (e.g., "0010,0010" or "(0010,0010)")
     * @throws DicomDictionaryException
     */
    public function formatTag(string $tag, string $format = 'comma'): string
    {
        $normalized = $this->normaliseTag($tag);

        if (strlen($normalized) !== 8) {
            return $normalized;
        }

        $group = substr($normalized, 0, 4);
        $element = substr($normalized, 4, 4);

        return match ($format) {
            'comma' => $group.','.$element,
            'paren' => '('.$group.$element.')',
            'both'  => '('.$group.','.$element.')',
            default => $normalized,
        };
    }

}
