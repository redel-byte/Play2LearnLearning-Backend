<?php
declare(strict_types=1);

namespace App\Traits;

trait HasFormattedAttributes
{
    public function getFormattedDateAttribute(string $attribute): string
    {
        $date = $this->getAttribute($attribute);
        
        if (!$date) {
            return 'N/A';
        }

        return $date->format('M d, Y');
    }

    public function getFormattedDateTimeAttribute(string $attribute): string
    {
        $date = $this->getAttribute($attribute);
        
        if (!$date) {
            return 'N/A';
        }

        return $date->format('M d, Y h:i A');
    }

    public function getFormattedTimeAttribute(string $attribute): string
    {
        $date = $this->getAttribute($attribute);
        
        if (!$date) {
            return 'N/A';
        }

        return $date->format('h:i A');
    }

    public function getFormattedDurationAttribute(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 's';
        }

        if ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        $parts = [];
        if ($hours > 0) $parts[] = "{$hours}h";
        if ($minutes > 0) $parts[] = "{$minutes}m";
        if ($remainingSeconds > 0 && count($parts) < 2) $parts[] = "{$remainingSeconds}s";

        return implode(' ', $parts);
    }

    public function getFormattedPercentageAttribute(float $percentage): string
    {
        return number_format($percentage, 1) . '%';
    }

    public function getFormattedScoreAttribute(int $score, int $maxScore): string
    {
        $percentage = $maxScore > 0 ? ($score / $maxScore) * 100 : 0;
        return "{$score}/{$maxScore} (" . number_format($percentage, 1) . '%)';
    }

    public function getGradeAttribute(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };
    }

    public function getGradeColorAttribute(float $percentage): string
    {
        return match (true) {
            $percentage >= 90 => 'green',
            $percentage >= 80 => 'blue',
            $percentage >= 70 => 'yellow',
            $percentage >= 60 => 'orange',
            default => 'red',
        };
    }

    public function getStatusBadgeAttribute(string $status): string
    {
        return match (strtolower($status)) {
            'active', 'completed', 'passed' => 'success',
            'inactive', 'failed' => 'danger',
            'pending', 'in_progress' => 'warning',
            default => 'secondary',
        };
    }

    public function getTruncatedTextAttribute(string $text, int $length = 50): string
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length) . '...';
    }
}
