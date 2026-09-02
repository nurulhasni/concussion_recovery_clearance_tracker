<?php

namespace Tests\Unit;

use App\Contracts\AiCompletionProvider;
use App\Services\SymptomAnalyzer;
use Tests\TestCase;

class SymptomAnalyzerTest extends TestCase
{
    protected SymptomAnalyzer $analyzer;

    protected function setUp(): void
    {
        parent::setUp();

        $mockProvider = $this->createMock(AiCompletionProvider::class);
        $this->analyzer = new SymptomAnalyzer($mockProvider);
    }

    public function test_keyword_safety_check_overrides_false_negative_ai_for_danger_signs_en(): void
    {
        $reportText = 'The student experienced repeated vomiting and seizure after school.';

        // Simulating an AI false negative (AI returned mild and red_flag = false)
        $aiFalseNegative = [
            'severity' => 'mild',
            'red_flag' => false,
            'reasoning' => 'Student reported feeling unwell.',
            'extracted_symptoms' => ['stomach upset'],
        ];

        $guardedResult = $this->analyzer->applyKeywordSafetyCheck($reportText, $aiFalseNegative);

        // Safety override must trigger!
        $this->assertTrue($guardedResult['red_flag']);
        $this->assertTrue($guardedResult['safety_override']);
        $this->assertStringContainsString('keyword match', $guardedResult['override_reason']);
    }

    public function test_keyword_safety_check_overrides_false_negative_ai_for_danger_signs_id(): void
    {
        $reportText = 'Anak saya pingsan dan muntah berulang saat di rumah.';

        $aiFalseNegative = [
            'severity' => 'mild',
            'red_flag' => false,
            'reasoning' => 'Gejala ringan biasa.',
            'extracted_symptoms' => ['mual'],
        ];

        $guardedResult = $this->analyzer->applyKeywordSafetyCheck($reportText, $aiFalseNegative);

        $this->assertTrue($guardedResult['red_flag']);
        $this->assertTrue($guardedResult['safety_override']);
    }

    public function test_keyword_safety_check_preserves_mild_when_no_danger_keywords(): void
    {
        $reportText = 'Felt normal today, walked for 20 minutes with no headache.';

        $aiNormal = [
            'severity' => 'mild',
            'red_flag' => false,
            'reasoning' => 'No symptoms present.',
            'extracted_symptoms' => ['none'],
        ];

        $guardedResult = $this->analyzer->applyKeywordSafetyCheck($reportText, $aiNormal);

        $this->assertFalse($guardedResult['red_flag']);
        $this->assertFalse($guardedResult['safety_override']);
    }

    public function test_extract_json_handles_code_fences_and_raw_json(): void
    {
        $markdownWrapped = "```json\n{\"severity\": \"mild\", \"red_flag\": false, \"reasoning\": \"Safe\", \"extracted_symptoms\": [\"mild headache\"]}\n```";

        $result = $this->analyzer->extractJson($markdownWrapped);

        $this->assertEquals('mild', $result['severity']);
        $this->assertFalse($result['red_flag']);
        $this->assertEquals('Safe', $result['reasoning']);
        $this->assertEquals(['mild headache'], $result['extracted_symptoms']);
    }

    public function test_extract_json_fails_safe_to_moderate_severity_and_red_flag(): void
    {
        $corruptOutput = 'This is not valid json at all and cannot be decoded.';

        $result = $this->analyzer->extractJson($corruptOutput);

        // Fail-safe must default to moderate and red_flag true
        $this->assertEquals('moderate', $result['severity']);
        $this->assertTrue($result['red_flag']);
    }
}
