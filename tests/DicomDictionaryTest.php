<?php /** @noinspection JsonEncodingApiUsageInspection */

declare(strict_types=1);

namespace Aurabx\DicomData\Tests;

use Aurabx\DicomData\DicomDictionary;
use PHPUnit\Framework\TestCase;

class DicomDictionaryTest extends TestCase
{
    public function testGetName(): void
    {
        $this->assertEquals("Patient's Name", DicomDictionary::getAttributeName('00100010'));
        $this->assertEquals("Patient's Name", DicomDictionary::getAttributeName('0010,0010'));
        $this->assertEquals("Patient's Name", DicomDictionary::getAttributeName('(0010,0010)'));
        $this->assertNull(DicomDictionary::getAttributeName('12345678')); // Unknown tag
    }

    public function testGetKeyword(): void
    {
        $this->assertEquals("PatientName", DicomDictionary::getAttributeKeyword('00100010'));
        $this->assertEquals("PatientName", DicomDictionary::getAttributeKeyword('0010,0010'));
        $this->assertEquals("PatientName", DicomDictionary::getAttributeKeyword('(0010,0010)'));
        $this->assertNull(DicomDictionary::getAttributeName('12345678')); // Unknown tag
    }

    public function testGetTagByName(): void
    {
        $tag = DicomDictionary::getAttributeByName('PatientName');
        $this->assertEquals('00100010', $tag['id']);
        $this->assertNull(DicomDictionary::getAttributeByName('NonExistentTag'));
    }

    public function testGetVR(): void
    {
        $this->assertEquals('PN', DicomDictionary::getAttributeVr('00100010'));
        $this->assertEquals('UI', DicomDictionary::getAttributeVr('0020000D'));
        $this->assertNull(DicomDictionary::getAttributeVr('12345678')); // Unknown tag
    }

    public function testIsKnownTag(): void
    {
        $this->assertTrue(DicomDictionary::isKnownAttribute('00100010'));
        $this->assertTrue(DicomDictionary::isKnownAttribute('0010,0010'));
        $this->assertFalse(DicomDictionary::isKnownAttribute('12345678'));
    }

    public function testGetAllTags(): void
    {
        $tags = DicomDictionary::getAllAttributes();
        $this->assertIsArray($tags);
        $this->assertNotEmpty($tags);
        $this->assertArrayHasKey('00100010', $tags);
    }

    public function testGetTagInfo(): void
    {
        $tagInfo = DicomDictionary::getAttributeInfo('00100010');
        $this->assertIsArray($tagInfo);
        $this->assertEquals('PatientName', $tagInfo['keyword']);
        $this->assertEquals('PN', $tagInfo['valueRepresentation']);

        $this->assertNull(DicomDictionary::getAttributeInfo('12345678')); // Unknown tag
    }
}
