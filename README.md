# Dicom Data

***The list of DICOM tags is not currently complete and should be used with caution**.

**Aurabx\\DicomData** is a PHP 8.2+ library for managing and resolving DICOM tag metadata. It provides a robust interface for loading DICOM dictionaries and resolving tag names with ease. Designed for integration with medical imaging platforms or DICOMWeb services.

## Requirements

- PHP 8.2 or higher
- `ext-json`

## Installation

```bash
composer require aurabx/dicom-data
```

## Usage

```php
use Aurabx\DicomData\DicomDictionary;

// Lookup tag ID by name
$tagId = DicomDictionary::getTagIdByName('ImagingFrequency');  // Returns '00180084'

// Get full metadata
$info = DicomDictionary::getTagInfo('00180084');

// Get tag VR or description
$vr = DicomDictionary::getTagVR('00180084');
$desc = DicomDictionary::getTagDescription('00180084');
```

### Testing with Custom Tags

```php
use Aurabx\DicomData\DicomDictionary;
use Aurabx\DicomData\DicomTagLoader;

$loader = new DicomTagLoader();
$loader->loadFromArray([
    '00100020' => ['name' => 'PatientID', 'vr' => 'LO'],
    '00180084' => ['name' => 'ImagingFrequency', 'vr' => 'DS'],
]);

DicomDictionary::preload($loader);
```


## Development
```bash
composer test
composer check-style
composer fix-style
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
MIT – see LICENSE for details.
