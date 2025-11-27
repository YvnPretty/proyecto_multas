// src/LibraryFineCalculator.php
<?php
declare(strict_types=1);
namespace App;
final class LibraryFineCalculator
{
    private const BASE_FINE_PER_BOOK = 5.0;
    private const DAILY_PENALTY_PER_BOOK = 2.0;
    private const SAME_DAY_DISCOUNT = 0.20;
    public function applySameDayDiscount(float $total, bool $sameDay): float
    {
        if ($sameDay === true && $total > 0) {
            return $total * (1 - self::SAME_DAY_DISCOUNT);
        }
        return $total;
    }
    public function calculateDetailed(int $books, int $days, bool $sameDay): array
    {
        $base = self::BASE_FINE_PER_BOOK * $books;
        $penalty = self::DAILY_PENALTY_PER_BOOK * $days * $books;
        $totalBefore = $base + $penalty;
        $totalAfter = $this->applySameDayDiscount($totalBefore, $sameDay);
        return [
            'base' => $base,
            'penalty' => $penalty,
            'totalBeforeDiscount' => $totalBefore,
            'totalAfterDiscount' => $totalAfter,
        ];
    }
}
