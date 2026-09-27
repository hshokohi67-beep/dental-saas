<?php

namespace App\Domain\Dental\Support;

use InvalidArgumentException;

/**
 * FDI (ISO 3950) two-digit tooth numbering — verified against the legacy
 * implementation (`assets/js/admin/dental-chart.js`) and documented in
 * business rules §1.1-§1.2. This is the single, tested source of truth for
 * the numbering scheme; nothing else in the codebase should hand-compute a
 * quadrant, arch, side, or display label.
 *
 * Quadrants: 1=upper-right permanent, 2=upper-left permanent,
 * 3=lower-left permanent, 4=lower-right permanent, 5=upper-right primary,
 * 6=upper-left primary, 7=lower-left primary, 8=lower-right primary.
 * FDI number = quadrant * 10 + position (position 1 = central, closest to
 * the midline; permanent goes up to 8, primary only up to 5).
 *
 * Rendering convention (§1.2): the chart is drawn from the dentist's view
 * facing the patient (the clinical/mirrored convention), so the patient's
 * anatomical right side (quadrants 1, 4, 5, 8) is drawn on the LEFT half of
 * the screen, and the patient's left side (quadrants 2, 3, 6, 7) on the
 * RIGHT half — the opposite of "patient's own mirror" perspective.
 */
final class ToothNumber
{
    private const UPPER_QUADRANTS = [1, 2, 5, 6];

    private const RIGHT_QUADRANTS = [1, 4, 5, 8];

    private const PERMANENT_QUADRANTS = [1, 2, 3, 4];

    private const PRIMARY_QUADRANTS = [5, 6, 7, 8];

    public readonly int $fdi;

    public readonly int $quadrant;

    public readonly int $position;

    private function __construct(int $quadrant, int $position)
    {
        $this->quadrant = $quadrant;
        $this->position = $position;
        $this->fdi = $quadrant * 10 + $position;
    }

    public static function fromFdi(int $fdi): self
    {
        $quadrant = intdiv($fdi, 10);
        $position = $fdi % 10;

        if ($quadrant < 1 || $quadrant > 8 || $position < 1) {
            throw new InvalidArgumentException("«{$fdi}» یک شماره‌ی دندان معتبر به استاندارد FDI نیست.");
        }

        $maxPosition = in_array($quadrant, self::PERMANENT_QUADRANTS, true) ? 8 : 5;

        if ($position > $maxPosition) {
            throw new InvalidArgumentException("«{$fdi}» یک شماره‌ی دندان معتبر به استاندارد FDI نیست.");
        }

        return new self($quadrant, $position);
    }

    public static function fromQuadrantAndPosition(int $quadrant, int $position): self
    {
        return self::fromFdi($quadrant * 10 + $position);
    }

    public function isPermanent(): bool
    {
        return in_array($this->quadrant, self::PERMANENT_QUADRANTS, true);
    }

    public function isPrimary(): bool
    {
        return ! $this->isPermanent();
    }

    public function dentition(): string
    {
        return $this->isPermanent() ? 'permanent' : 'primary';
    }

    public function arch(): string
    {
        return in_array($this->quadrant, self::UPPER_QUADRANTS, true) ? 'upper' : 'lower';
    }

    /**
     * Incisors and canines (position 1-3) have an incisal edge instead of an
     * occlusal surface — the catalog's own "anterior" tooth_filter uses the
     * same 1-3 cutoff (business rules §6.2).
     */
    public function isAnterior(): bool
    {
        return $this->position <= 3;
    }

    /**
     * The patient's own anatomical side — not the rendering side.
     */
    public function patientSide(): string
    {
        return in_array($this->quadrant, self::RIGHT_QUADRANTS, true) ? 'right' : 'left';
    }

    /**
     * Which half of the *screen* this tooth is drawn on under the clinical
     * (mirrored) chart convention — always the opposite of patientSide().
     */
    public function screenSide(): string
    {
        return $this->patientSide() === 'right' ? 'left' : 'right';
    }

    /**
     * The on-chart label is always just the tooth's position within its own
     * half-jaw (1-8 for permanent, A-E for primary) — never the raw
     * two-digit FDI number, which is a storage/API detail (`fdi`). Which
     * quadrant a "5" belongs to is conveyed by where it sits on the chart
     * (grouped under its half-jaw's own label/button), matching how real
     * clinic charting software displays teeth.
     */
    public function displayLabel(): string
    {
        return $this->isPermanent() ? (string) $this->position : chr(64 + $this->position);
    }

    /**
     * @return list<self> the 32 permanent teeth, quadrants 1-4.
     */
    public static function permanentTeeth(): array
    {
        return self::teethForQuadrants(self::PERMANENT_QUADRANTS, 8);
    }

    /**
     * @return list<self> the 20 primary teeth, quadrants 5-8.
     */
    public static function primaryTeeth(): array
    {
        return self::teethForQuadrants(self::PRIMARY_QUADRANTS, 5);
    }

    /**
     * @return list<self> all 52 teeth (used for mixed/peds charts).
     */
    public static function all(): array
    {
        return [...self::permanentTeeth(), ...self::primaryTeeth()];
    }

    /**
     * @param  list<int>  $quadrants
     * @return list<self>
     */
    private static function teethForQuadrants(array $quadrants, int $maxPosition): array
    {
        $teeth = [];

        foreach ($quadrants as $quadrant) {
            for ($position = 1; $position <= $maxPosition; $position++) {
                $teeth[] = new self($quadrant, $position);
            }
        }

        return $teeth;
    }
}
