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
    private array $attributes = [];

    /**
     * @var array<string, string> Mapping from tag name to tag ID
     */
    private array $attributesByName = [];

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
     * @param  string|null  $attributes_file_path
     */
    /**
     * @param  string|null  $attributes_file_path Path to PHP array or JSON file
     */
    public function __construct(?string $attributes_file_path = null)
    {
        if ($attributes_file_path !== null) {
            $this->loadFromFile($attributes_file_path);
        } else {
            $this->loadDefaultTags();
        }
    }

    /**
     * @return void
     */
    private function loadDefaultTags(): void
    {
        $phpPath = dirname(__DIR__) . '/resources/dicom/php/standard/attributes.php';

        if (!file_exists($phpPath)) {
            throw new DicomDictionaryException("DICOM tag PHP export not found: $phpPath");
        }

        /** @var array<string, array<string, mixed>> $data */
        $data = require $phpPath;

        $this->loadFromArray($data, true);
    }

    /**
     * @param  string  $path
     * @param  bool  $clearExisting
     * @return void
     */
    public function loadFromFile(string $path, bool $clearExisting = true): void
    {
        if (!is_file($path)) {
            throw new DicomDictionaryException("Invalid file path: $path");
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if ($extension === 'php') {
            $data = require $path;

            if (!is_array($data)) {
                throw new DicomDictionaryException("PHP file did not return an array: $path");
            }
        } elseif ($extension === 'json') {
            $jsonContent = file_get_contents($path);
            if ($jsonContent === false) {
                throw new DicomDictionaryException("Failed to read JSON file: $path");
            }

            try {
                $data = json_decode($jsonContent, true, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                throw new DicomDictionaryException("Invalid JSON: {$e->getMessage()}");
            }
        } else {
            throw new DicomDictionaryException("Unsupported file type: $path");
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
            $this->attributes = [];
            $this->attributesByName = [];
        }

        foreach ($data as $tagId => $tagInfo) {

            $this->attributes[$tagId] = $tagInfo;
            $this->attributes[$tagId]['id'] = $tagId;

            if (isset($tagInfo['keyword'])) {
                $this->attributesByName[$tagInfo['keyword']] = $tagId;
            }
        }
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttribute(string $id): ?array
    {
        return $this->attributes[$this->normaliseTag($id)] ?? null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttributeTag(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            return $this->attributes[$id]['tag'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttributeName(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            return $this->attributes[$id]['name'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return bool
     */
    public function getAttributeRetired(string $id): bool
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            return isset($this->attributes[$id]['retired']) && $this->attributes[$id]['retired'] === 'Y';
        }

        return false;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttributeVr(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            return $this->attributes[$id]['valueRepresentation'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttributeKeyword(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            return $this->attributes[$id]['keyword'];
        }

        return null;
    }

    /**
     * @param  string  $id
     * @return array|null
     */
    public function getAttributeVm(string $id): ?string
    {
        $id = $this->normaliseTag($id);

        if (isset($this->attributes[$id])) {
            if (array_key_exists('valueMultiplicity', $this->attributes[$id])) {
                return $this->attributes[$id]['valueMultiplicity'];
            }

            return '1';
        }

        return null;
    }

    /**
     * @param  string  $keyword
     * @return array|null
     */
    public function getAttributeByKeyword(string $keyword): ?array
    {
        $tagId = $this->getAttributeIdByKeyword($keyword);

        if (!empty($tagId)) {
            return $this->attributes[$tagId];
        }

        return null;
    }

    /**
     * @param  string  $keyword
     * @return array|null
     */
    public function getAttributeIdByKeyword(string $keyword): ?string
    {
        $keyword = $this->toCamelCase($keyword);

        return $this->attributesByName[$keyword] ?? null;
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
    public function getAllAttributes(): array
    {
        return $this->attributes;
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

    /**
     * @param  string  $input
     * @return string
     */
    private function toCamelCase(string $input): string
    {
        // If the input is already camelCase, we leave it alone
        if (!str_contains($input, '_') && !str_contains($input, '-')) {
            return $input;
        }

        // Normalise hyphens and underscores to one format
        $input = str_replace(['-', '_'], ' ', $input);

        // Capitalise words
        $input = ucwords($input);

        // Remove spaces
        $input = str_replace(' ', '', $input);

        // Lowercase the first character to get proper camelCase
        return lcfirst($input);
    }

}
