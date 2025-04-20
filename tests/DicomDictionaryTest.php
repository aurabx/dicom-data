<?php

declare(strict_types=1);

namespace Aurabx\DicomData\Tests;

use Aurabx\DicomData\DicomDictionary;
use PHPUnit\Framework\TestCase;

class DicomDictionaryTest extends TestCase
{
    protected function setUp(): void
    {
        // Make sure we have tags loaded for testing
        $tagsPath = dirname(__DIR__) . '/resources/tags';

        // If the resources/tags directory doesn't exist, create minimal test data
        if (!is_dir($tagsPath)) {
            $this->createMinimalTagsFile();
            $tagsPath = dirname(__DIR__) . '/resources/tags';
        }
    }

    /**
     * Create a minimal tags file structure for testing if none exists
     */
    private function createMinimalTagsFile(): void
    {
        $tagsDir = dirname(__DIR__) . '/resources/tags';
        if (!is_dir($tagsDir) && !mkdir($tagsDir, 0755, true) && !is_dir($tagsDir)) {
            $this->markTestSkipped('Could not create tags directory for testing');
        }

        // Create a simple patient.json file with minimal tags
        $patientTags = [
            '00100010' => [
                'name' => 'PatientName',
                'vr' => 'PN',
                'description' => 'Patient\'s full name'
            ],
            '00100020' => [
                'name' => 'PatientID',
                'vr' => 'LO',
                'description' => 'Primary identifier for the patient'
            ]
        ];

        file_put_contents(
            $tagsDir . '/patient.json',
            json_encode($patientTags, JSON_PRETTY_PRINT)
        );

        // Create a simple study.json file with minimal tags
        $studyTags = [
            '0020000D' => [
                'name' => 'StudyInstanceUID',
                'vr' => 'UI',
                'description' => 'Unique identifier for the study'
            ]
        ];

        file_put_contents(
            $tagsDir . '/study.json',
            json_encode($studyTags, JSON_PRETTY_PRINT)
        );
    }

    public function testGetName(): void
    {
        $this->assertEquals('PatientName', DicomDictionary::getTagName('00100010'));
        $this->assertEquals('PatientName', DicomDictionary::getTagName('0010,0010'));
        $this->assertEquals('PatientName', DicomDictionary::getTagName('(0010,0010)'));
        $this->assertNull(DicomDictionary::getTagName('12345678')); // Unknown tag
    }

    public function testGetTagByName(): void
    {
        $this->assertEquals('00100010', DicomDictionary::getTagByName('PatientName'));
        $this->assertNull(DicomDictionary::getTagByName('NonExistentTag'));
    }



    public function testGetVR(): void
    {
        $this->assertEquals('PN', DicomDictionary::getTagVr('00100010'));
        $this->assertEquals('UI', DicomDictionary::getTagVr('0020000D'));
        $this->assertNull(DicomDictionary::getTagVr('12345678')); // Unknown tag
    }


    public function testGetDescription(): void
    {
        $this->assertEquals('Patient\'s full name', DicomDictionary::getTagDescription('00100010'));
        $this->assertNull(DicomDictionary::getTagDescription('12345678')); // Unknown tag
    }

    public function testIsKnownTag(): void
    {
        $this->assertTrue(DicomDictionary::isKnownTag('00100010'));
        $this->assertTrue(DicomDictionary::isKnownTag('0010,0010'));
        $this->assertFalse(DicomDictionary::isKnownTag('12345678'));
    }

    public function testGetAllTags(): void
    {
        $tags = DicomDictionary::getAllTags();
        $this->assertIsArray($tags);
        $this->assertNotEmpty($tags);
        $this->assertArrayHasKey('00100010', $tags);
    }

    public function testGetTagInfo(): void
    {
        $tagInfo = DicomDictionary::getTagInfo('00100010');
        $this->assertIsArray($tagInfo);
        $this->assertEquals('PatientName', $tagInfo['name']);
        $this->assertEquals('PN', $tagInfo['vr']);
        $this->assertNotEmpty($tagInfo['description']);

        $this->assertNull(DicomDictionary::getTagInfo('12345678')); // Unknown tag
    }
}
