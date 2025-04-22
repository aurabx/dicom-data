<?php

declare(strict_types=1);

namespace Tests\Aurabx\DicomData\Unit;

use Aurabx\DicomData\DicomDictionary;
use Aurabx\DicomData\DicomTagLoader;
use Aurabx\DicomData\DicomDictionaryException;
use PHPUnit\Framework\TestCase;

final class DicomTagLoaderTest extends TestCase
{
    private array $mockTags = [
        '00080020' => [
            'name' => 'Study Date',
            'keyword' => 'StudyDate',
            'valueRepresentation' => 'DA',
            'description' => 'Date the study was performed'
        ],
        '00080030' => [
            'name' => 'Study Time',
            'keyword' => 'StudyTime',
            'valueRepresentation' => 'TM',
            'description' => 'Time the study was performed',
            'valueMultiplicity' => '1'
        ],
        '00081048' => [
            'name' => 'Physicians of Record',
            'keyword' => 'PhysiciansOfRecord',
            'valueRepresentation' => 'PN',
            'description' => 'Names of the physicians of record for the study',
            'valueMultiplicity' => '1-n'
        ]
    ];

    public function test_it_can_load_tags_from_array(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $this->assertNotEmpty($loader->getAllAttributes());
        $this->assertSame('Study Date', $loader->getAttribute('00080020')['name']);
    }

    public function test_it_can_return_tag_by_id(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $tag = $loader->getAttribute('00080030');
        $this->assertNotNull($tag);
        $this->assertSame('Study Time', $tag['name']);
    }

    public function test_it_can_return_tag_name(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $name = $loader->getAttributeName('00080020');
        $this->assertSame('Study Date', $name);
    }

    public function test_it_can_return_tag_vr(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $vr = $loader->getAttributeVr('00080030');
        $this->assertSame('TM', $vr);
    }

    public function test_it_can_return_tag_vm(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $vm1 = $loader->getAttributeVm('00080030');
        $vm2 = $loader->getAttributeVm('00081048');

        $this->assertSame('1', $vm1);
        $this->assertSame('1-n', $vm2);
    }

    public function test_it_can_resolve_tag_id_by_name(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $tagId = $loader->getAttributeIdByKeyword('StudyDate');
        $this->assertSame('00080020', $tagId);

        $tagIdCamel = $loader->getAttributeIdByKeyword('StudyTime');
        $this->assertSame('00080030', $tagIdCamel);
    }

    public function test_it_can_return_vr_meaning(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);

        $this->assertSame('Date', $loader->getVrMeaning('DA'));
        $this->assertSame('Time', $loader->getVrMeaning('tm'));
        $this->assertNull($loader->getVrMeaning('xyz'));
    }

    public function test_it_throws_if_file_is_invalid(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);

        $this->expectException(DicomDictionaryException::class);
        $this->expectExceptionMessage('Invalid file path');
        $loader->loadFromFile(path: '/this/does/not/exist.json');
    }

    public function testNormalizeTag(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $this->assertEquals('00100010', $loader->normaliseTag('00100010'));
        $this->assertEquals('00100010', $loader->normaliseTag('0010,0010'));
        $this->assertEquals('00100010', $loader->normaliseTag('(0010,0010)'));
        $this->assertEquals('00100000', $loader->normaliseTag('0010'));
    }

    public function testFormatTag(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $this->assertEquals('0010,0010', $loader->formatTag('00100010', 'comma'));
        $this->assertEquals('(00100010)', $loader->formatTag('00100010', 'paren'));
        $this->assertEquals('(0010,0010)', $loader->formatTag('00100010', 'both'));
        $this->assertEquals('00100010', $loader->formatTag('00100010', 'unknown'));

        // Test with already formatted tags
        $this->assertEquals('0010,0010', $loader->formatTag('0010,0010', 'comma'));
        $this->assertEquals('(0010,0010)', $loader->formatTag('(0010,0010)', 'both'));
    }

    public function testGetVRMeaning(): void
    {
        $loader = new DicomTagLoader(attributes_file_path: null);
        $loader->loadFromArray(data: $this->mockTags);

        $this->assertEquals('Person Name', $loader->getVrMeaning('PN'));
        $this->assertEquals('Unique Identifier', $loader->getVrMeaning('UI'));
        $this->assertNull($loader->getVrMeaning('XX')); // Unknown VR
    }
}
