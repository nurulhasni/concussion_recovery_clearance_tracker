<?php

namespace App\Services;

use App\Contracts\AiCompletionProvider;
use App\Models\Patient;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class SymptomAnalyzer
{
    /**
     * Bilingual danger signs/red flag keywords (English & Indonesian).
     * Used for deterministic safety verification and guardrails.
     */
    public const RED_FLAG_KEYWORDS = [
        // English
        'repeated vomiting',
        'vomiting repeatedly',
        'loss of consciousness',
        'passed out',
        'blackout',
        'severe confusion',
        'disoriented',
        'seizure',
        'convulsions',
        'sudden severe headache',
        'worst headache of life',
        'slurred speech',
        'unequal pupils',
        'weakness or numbness',
        'fluid from nose or ears',
        'fluid from ear',
        'worsening headache',
        'stumbling',
        'inability to wake up',
        // Indonesian
        'muntah berulang',
        'muntah-muntah',
        'muntah terus',
        'hilang kesadaran',
        'kehilangan kesadaran',
        'pingsan',
        'kebingungan parah',
        'bingung berat',
        'disorientasi',
        'kejang',
        'kejang-kejang',
        'nyeri kepala hebat mendadak',
        'sakit kepala hebat mendadak',
        'sakit kepala tak tertahankan',
        'bicara pelo',
        'bicara tidak jelas',
        'pupil tidak sama',
        'lemas sebelah',
        'mati rasa',
        'cairan dari hidung',
        'cairan dari telinga',
        'sulit dibangunkan',
        'sempoyongan parah',
    ];

    public function __construct(
        protected AiCompletionProvider $provider
    ) {}

    /**
     * Stage 1: Extract symptoms and evaluate clinical severity & red flags using AI.
     *
     * @return array{
     *     severity: string,
     *     red_flag: bool,
     *     reasoning: string,
     *     extracted_symptoms: array<string>,
     *     safety_override: bool,
     *     override_reason?: string
     * }
     */
    public function extractSymptoms(string $reportText, string $locale = 'en'): array
    {
        $prompt = <<<PROMPT
You are a clinical symptom extraction assistant for a pediatric/student concussion recovery monitoring system.
Analyze the following free-text symptom report logged by a student or parent.

CRITICAL INSTRUCTIONS:
1. Do NOT diagnose the patient.
2. Reply ONLY with a valid JSON object. No Markdown code fences, no extra text.

SEVERITY CRITERIA:
- mild: no symptoms reported, or symptoms are barely noticeable and resolve quickly/on their own. Explicit negations (e.g. 'zero headache', 'no dizziness', 'felt fine') should be classified as mild, not moderate — read negation carefully.
- moderate: noticeable symptoms that affect daily activity but are manageable with rest/accommodation.
- severe: symptoms substantially interfere with function, even without meeting a red_flag danger sign.
Pay close attention to negation words (zero, no, without, denies, none) — a report explicitly denying symptoms must not be classified as moderate or severe based on those symptom words merely being mentioned.

Example: "Felt fine today, zero headache or dizziness" -> severity: "mild", red_flag: false.

3. Response JSON structure:
{
  "severity": "mild" | "moderate" | "severe",
  "red_flag": true | false,
  "reasoning": "Brief clinical reasoning in the requested locale ({$locale})",
  "extracted_symptoms": ["list", "of", "symptoms", "in", "English"]
}
4. Set "red_flag" to true if the report mentions any danger signs, such as:
   - Repeated vomiting
   - Loss of consciousness / fainting
   - Severe confusion or extreme disorientation
   - Seizures or convulsions
   - Sudden, rapidly worsening, or unbearable headache
   - Slurred speech or difficulty speaking
   - Weakness, numbness, or loss of motor coordination
   - Clear fluid leaking from nose or ears
   - Unequal pupil sizes
5. "extracted_symptoms" MUST ALWAYS be in English (for consistent data records). If symptoms are explicitly negated or no symptoms are present, use ["none"].
6. "reasoning" MUST be in language '{$locale}' ('en' = English, 'id' = Indonesian).

SYMPTOM REPORT TEXT (NO PII):
"{$reportText}"
PROMPT;

        try {
            $rawResponse = $this->provider->complete($prompt);
            $parsed = $this->extractJson($rawResponse);
        } catch (Throwable $e) {
            Log::error('SymptomAnalyzer AI extraction failed: '.$e->getMessage());
            // Fail-safe fallback: moderate severity with red flag true
            $parsed = [
                'severity' => 'moderate',
                'red_flag' => true,
                'reasoning' => $locale === 'id'
                    ? 'Analisis otomatis dialihkan ke mode pencegahan aman (fail-safe) untuk evaluasi tenaga kesehatan.'
                    : 'Automated analysis defaulted to safe precautionary mode (fail-safe) for clinician review.',
                'extracted_symptoms' => ['reported symptom requiring review'],
            ];
        }

        // Apply secondary deterministic keyword safety verification
        return $this->applyKeywordSafetyCheck($reportText, $parsed);
    }

    /**
     * Deterministic keyword safety layer (guardrail against AI false negatives).
     */
    public function applyKeywordSafetyCheck(string $reportText, array $aiResult): array
    {
        $normalizedText = mb_strtolower($reportText, 'UTF-8');
        $matchedKeyword = null;

        foreach (self::RED_FLAG_KEYWORDS as $keyword) {
            if (mb_stripos($normalizedText, mb_strtolower($keyword, 'UTF-8')) !== false) {
                $matchedKeyword = $keyword;
                break;
            }
        }

        if ($matchedKeyword !== null && empty($aiResult['red_flag'])) {
            // Safety override triggered
            $aiResult['red_flag'] = true;
            $aiResult['ai_red_flag'] = true;
            $aiResult['safety_override'] = true;
            $aiResult['override_reason'] = "keyword match: {$matchedKeyword}";
            if (($aiResult['severity'] ?? '') === 'mild') {
                $aiResult['severity'] = 'severe';
            }
        } else {
            $aiResult['safety_override'] = false;
        }

        return $aiResult;
    }

    /**
     * Stage 2: Generate a 1-2 sentence layperson recovery summary based on 7-day trends.
     */
    public function generateSummary(Patient $patient, array $latestExtraction, string $locale = 'en'): string
    {
        $recentReports = $patient->symptomReports()
            ->take(7)
            ->get();

        $trendDescription = $this->calculateSeverityTrend($recentReports);
        $stage = $patient->current_stage;
        $step = $patient->activity_step;

        $symptomsList = implode(', ', $latestExtraction['extracted_symptoms'] ?? ['none noted']);
        $severity = $latestExtraction['severity'] ?? 'mild';

        $prompt = <<<PROMPT
You are a helpful concussion recovery assistant writing a brief update for parents and school staff.
Context:
- Current Recovery Milestone Stage: {$stage}/4
- Activity Step (if in Stage 3): {$step}
- Latest Reported Symptoms: {$symptomsList} (Severity: {$severity})
- 7-Day Symptom Trajectory Trend: {$trendDescription}

MANDATORY SAFETY RULES:
1. Write EXACTLY 1 to 2 clear, empathetic, non-medical sentences in language '{$locale}' ('en' = English, 'id' = Indonesian).
2. DO NOT declare the student "cured", "healed", "cleared", or "ready to advance/play".
3. Only describe observed symptom patterns and encourage continued adherence to prescribed rest and protocol guidelines.
4. Return ONLY the plain text summary. No quotes, no markdown, no prefixes.
PROMPT;

        try {
            $response = trim($this->provider->complete($prompt));
            if (! empty($response)) {
                return $response;
            }
        } catch (Throwable $e) {
            Log::warning('SymptomAnalyzer summary generation failed: '.$e->getMessage());
        }

        // Safe deterministic fallback summary
        if ($locale === 'id') {
            return "Pola gejala menunjukkan tren {$trendDescription}. Pasien disarankan untuk tetap mengikuti batas aktivitas dan akomodasi yang telah ditentukan.";
        }

        return "Reported symptoms show a {$trendDescription} pattern. The student should continue following active activity restrictions and recovery accommodations.";
    }

    /**
     * Calculate 7-day severity trend (improving / stable / worsening).
     *
     * @param  Collection  $reports
     */
    protected function calculateSeverityTrend($reports): string
    {
        if ($reports->count() < 2) {
            return 'baseline tracking';
        }

        $scores = [
            'mild' => 1,
            'moderate' => 2,
            'severe' => 3,
        ];

        $list = $reports->values();
        $newest = $scores[$list->first()->ai_severity ?? 'mild'] ?? 1;
        $oldest = $scores[$list->last()->ai_severity ?? 'mild'] ?? 1;

        if ($newest < $oldest) {
            return 'improving';
        }

        if ($newest > $oldest) {
            return 'worsening';
        }

        return 'stable';
    }

    /**
     * Robust JSON extractor that strips markdown fences and extracts JSON substring.
     */
    public function extractJson(string $raw): array
    {
        $clean = trim($raw);

        // Remove markdown code blocks if present
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean);
        $clean = preg_replace('/\s*```$/', '', $clean);
        $clean = trim($clean);

        // Try direct decode
        $decoded = json_decode($clean, true);
        if (is_array($decoded) && isset($decoded['severity'])) {
            return $this->normalizeExtractionArray($decoded);
        }

        // Try regex match for first JSON object
        if (preg_match('/\{[\s\S]*?\}/', $clean, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded) && isset($decoded['severity'])) {
                return $this->normalizeExtractionArray($decoded);
            }
        }

        // If json_decode completely fails, return safe fallback
        Log::warning('JSON extraction failed from AI output: '.substr($raw, 0, 200));

        return [
            'severity' => 'moderate',
            'red_flag' => true,
            'reasoning' => 'Response parsing fallback: response could not be verified.',
            'extracted_symptoms' => ['unspecified symptom'],
        ];
    }

    /**
     * Normalize extraction array keys and types.
     */
    protected function normalizeExtractionArray(array $data): array
    {
        $severity = strtolower(trim((string) ($data['severity'] ?? 'mild')));
        if (! in_array($severity, ['mild', 'moderate', 'severe'], true)) {
            $severity = 'moderate';
        }

        $redFlag = filter_var($data['red_flag'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return [
            'severity' => $severity,
            'red_flag' => $redFlag,
            'reasoning' => (string) ($data['reasoning'] ?? ''),
            'extracted_symptoms' => is_array($data['extracted_symptoms'] ?? null)
                ? array_values(array_map('strval', $data['extracted_symptoms']))
                : [],
        ];
    }
}
