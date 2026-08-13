<?php

namespace Tests\Unit;

use App\Services\AiTriageService;
use Tests\TestCase;

class AiTriageServiceTest extends TestCase
{
    public function test_it_marks_severe_respiratory_symptoms_as_emergency_red(): void
    {
        $result = app(AiTriageService::class)->analyze([
            'chief_complaint' => 'Difficulty breathing and chest pain',
            'symptoms' => ['chest pain', 'shortness of breath', 'cyanosis'],
            'vitals' => [
                'blood_pressure' => 86,
                'heart_rate' => 128,
                'respiratory_rate' => 30,
                'temperature' => 38.7,
                'spo2' => 88,
            ],
            'pain_score' => 9,
        ]);

        $this->assertSame(1, $result['level']);
        $this->assertSame('red', $result['color']);
        $this->assertSame('Emergency', $result['priority']);
        $this->assertStringContainsString('emergency', strtolower($result['recommendation']));
    }

    public function test_it_marks_high_fever_as_urgent_yellow(): void
    {
        $result = app(AiTriageService::class)->analyze([
            'chief_complaint' => 'High fever and fatigue',
            'symptoms' => ['high fever', 'dizziness', 'weakness'],
            'vitals' => [
                'blood_pressure' => 102,
                'heart_rate' => 108,
                'respiratory_rate' => 22,
                'temperature' => 39.8,
                'spo2' => 96,
            ],
            'pain_score' => 7,
        ]);

        $this->assertSame(2, $result['level']);
        $this->assertSame('yellow', $result['color']);
        $this->assertSame('Urgent', $result['priority']);
    }
}
