# Dicom Data

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
use Aurabx\DicomData\DicomDictionaryTagNameResolver;

$dictionary = new DicomDictionary();
$resolver = new DicomDictionaryTagNameResolver($dictionary);

$tagName = $resolver->resolve('00100010'); // Patient's Name
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
