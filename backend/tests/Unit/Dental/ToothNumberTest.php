<?php

namespace Tests\Unit\Dental;

use App\Domain\Dental\Support\ToothNumber;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The tooth numbering mapping is roadmap Phase 3's explicit acceptance
 * criterion: "an automated test for the numbering mapping (permanent +
 * primary, all four quadrants) exists." Business rules §1.1-§1.2 are the
 * source of truth this asserts against.
 */
class ToothNumberTest extends TestCase
{
    public function test_permanent_teeth_cover_all_four_quadrants_with_32_teeth(): void
    {
        $teeth = ToothNumber::permanentTeeth();

        $this->assertCount(32, $teeth);

        $fdiNumbers = array_map(fn (ToothNumber $tooth) => $tooth->fdi, $teeth);
        sort($fdiNumbers);

        $expected = [
            ...range(11, 18), ...range(21, 28), ...range(31, 38), ...range(41, 48),
        ];
        sort($expected);

        $this->assertSame($expected, $fdiNumbers);
    }

    public function test_primary_teeth_cover_all_four_quadrants_with_20_teeth(): void
    {
        $teeth = ToothNumber::primaryTeeth();

        $this->assertCount(20, $teeth);

        $fdiNumbers = array_map(fn (ToothNumber $tooth) => $tooth->fdi, $teeth);
        sort($fdiNumbers);

        $expected = [
            ...range(51, 55), ...range(61, 65), ...range(71, 75), ...range(81, 85),
        ];
        sort($expected);

        $this->assertSame($expected, $fdiNumbers);
    }

    public function test_all_returns_52_teeth_for_the_mixed_dentition_chart(): void
    {
        $this->assertCount(52, ToothNumber::all());
    }

    #[DataProvider('dentitionProvider')]
    public function test_dentition_is_derived_from_the_quadrant(int $fdi, string $expectedDentition): void
    {
        $this->assertSame($expectedDentition, ToothNumber::fromFdi($fdi)->dentition());
    }

    public static function dentitionProvider(): array
    {
        return [
            'Q1 upper-right permanent' => [11, 'permanent'],
            'Q2 upper-left permanent' => [24, 'permanent'],
            'Q3 lower-left permanent' => [35, 'permanent'],
            'Q4 lower-right permanent' => [48, 'permanent'],
            'Q5 upper-right primary' => [51, 'primary'],
            'Q6 upper-left primary' => [63, 'primary'],
            'Q7 lower-left primary' => [72, 'primary'],
            'Q8 lower-right primary' => [85, 'primary'],
        ];
    }

    #[DataProvider('archAndSideProvider')]
    public function test_arch_and_patient_side_match_the_quadrant_definition(
        int $quadrant,
        string $expectedArch,
        string $expectedPatientSide,
    ): void {
        $tooth = ToothNumber::fromQuadrantAndPosition($quadrant, 1);

        $this->assertSame($expectedArch, $tooth->arch());
        $this->assertSame($expectedPatientSide, $tooth->patientSide());
    }

    public static function archAndSideProvider(): array
    {
        return [
            'Q1 upper right (permanent)' => [1, 'upper', 'right'],
            'Q2 upper left (permanent)' => [2, 'upper', 'left'],
            'Q3 lower left (permanent)' => [3, 'lower', 'left'],
            'Q4 lower right (permanent)' => [4, 'lower', 'right'],
            'Q5 upper right (primary)' => [5, 'upper', 'right'],
            'Q6 upper left (primary)' => [6, 'upper', 'left'],
            'Q7 lower left (primary)' => [7, 'lower', 'left'],
            'Q8 lower right (primary)' => [8, 'lower', 'right'],
        ];
    }

    /**
     * Business rules §1.2: the chart is drawn from the dentist's view facing
     * the patient — the patient's right side (quadrants 1, 4, 5, 8) is drawn
     * on the LEFT half of the screen, and the patient's left side
     * (quadrants 2, 3, 6, 7) on the RIGHT half. This is the single most
     * error-prone part of any odontogram implementation, so it is asserted
     * for every quadrant explicitly rather than just derived generically.
     */
    #[DataProvider('screenSideProvider')]
    public function test_screen_side_is_mirrored_relative_to_the_patients_own_side(int $quadrant, string $expectedScreenSide): void
    {
        $tooth = ToothNumber::fromQuadrantAndPosition($quadrant, 1);

        $this->assertSame($expectedScreenSide, $tooth->screenSide());
    }

    public static function screenSideProvider(): array
    {
        return [
            'Q1 (patient right, permanent) -> screen left' => [1, 'left'],
            'Q2 (patient left, permanent) -> screen right' => [2, 'right'],
            'Q3 (patient left, permanent) -> screen right' => [3, 'right'],
            'Q4 (patient right, permanent) -> screen left' => [4, 'left'],
            'Q5 (patient right, primary) -> screen left' => [5, 'left'],
            'Q6 (patient left, primary) -> screen right' => [6, 'right'],
            'Q7 (patient left, primary) -> screen right' => [7, 'right'],
            'Q8 (patient right, primary) -> screen left' => [8, 'left'],
        ];
    }

    public function test_permanent_teeth_are_displayed_by_their_position_within_the_half_jaw_not_the_fdi_number(): void
    {
        $this->assertSame('1', ToothNumber::fromFdi(11)->displayLabel());
        $this->assertSame('8', ToothNumber::fromFdi(48)->displayLabel());
        $this->assertSame('5', ToothNumber::fromFdi(25)->displayLabel());
    }

    /**
     * Business rules §1.1: primary teeth are only ever shown to users as a
     * letter (A-E), even though the FDI number is what's stored.
     */
    #[DataProvider('primaryLabelProvider')]
    public function test_primary_teeth_are_displayed_as_letters_a_to_e(int $fdi, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, ToothNumber::fromFdi($fdi)->displayLabel());
    }

    public static function primaryLabelProvider(): array
    {
        return [
            [51, 'A'], [52, 'B'], [53, 'C'], [54, 'D'], [55, 'E'],
            [81, 'A'], [85, 'E'],
        ];
    }

    public function test_an_fdi_number_outside_any_valid_quadrant_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToothNumber::fromFdi(99);
    }

    public function test_a_primary_position_beyond_five_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToothNumber::fromFdi(56);
    }

    public function test_a_permanent_position_beyond_eight_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ToothNumber::fromFdi(19);
    }
}
