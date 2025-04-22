# Dicom Data

**Aurabx\\DicomData** is a PHP 8.2+ library for managing and resolving DICOM tag metadata. It provides an interface for loading DICOM dictionaries and resolving tag names with ease. Designed for integration with medical imaging platforms or DICOMWeb services.

This module effectively utilises the excellent DICOM data source from https://github.com/innolitics/dicom-standard and makes it available for PHP sources. 

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
$info = DicomDictionary::getAttributeInfo('00180084');

// Get tag VR or keyword
$vr = DicomDictionary::getAttributeVr('00180084');
$desc = DicomDictionary::getAttributeKeyword('00180084');
```

### Testing with Custom Tags

```php
use Aurabx\DicomData\DicomDictionary;
use Aurabx\DicomData\DicomTagLoader;

$loader = new DicomTagLoader();
$loader->loadFromArray([
    '00100020' => ['keyword' => 'PatientID', 'valueRepresentation' => 'LO'],
    '00180084' => ['keyword' => 'ImagingFrequency', 'valueRepresentation' => 'DS'],
]);

DicomDictionary::preload($loader);
```


## Development

Update the standards from the Inolitic source
```bash
python -m venv venv
source venv/bin/activate
pip install pydicom

python utils/update_dicom_dict.py
```

Run tests
```bash
composer test
```

## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## License
MIT – see LICENSE for details.
