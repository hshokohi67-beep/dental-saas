<?php

namespace Database\Seeders;

use App\Domain\Dental\Models\DentalConditionCatalog;
use Illuminate\Database\Seeder;

/**
 * The full set of tooth-chart codes carried over from legacy
 * (`assets/js/admin/dental-chart.js::TOOTH_TX`, business rules §1.3 and
 * §1.5-§1.6). Status colors/priority follow §1.3's derived-status rule:
 * only codes that represent a definitive restorative/prosthetic/surgical
 * outcome get a priority (lower wins); purely diagnostic, radiographic, or
 * consultation codes never win a tooth's displayed color on their own.
 *
 * `booking_eligible`/`duration_minutes`/`buffer_minutes` (roadmap Phase 4)
 * make this same catalog double as the appointment-scheduling service list
 * — a code with no standardized visit length (pure diagnostic findings,
 * "درمان تخصصی") stays booking_eligible=false.
 */
class DentalConditionCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $both = ['permanent', 'primary'];
        $permanentOnly = ['permanent'];
        $primaryOnly = ['primary'];

        $conditions = [
            // Tooth-scoped treatment codes
            ['key' => 'extraction', 'label' => 'کشیدن ساده', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 1, 'status_color' => '#F2F3F4', 'status_border' => '#B0BEC5', 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 5],
            ['key' => 'surgical_ext', 'label' => 'کشیدن جراحی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 1, 'status_color' => '#F2F3F4', 'status_border' => '#B0BEC5', 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'implant', 'label' => 'ایمپلنت', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 2, 'status_color' => '#E8F8F5', 'status_border' => '#4DB6AC', 'booking_eligible' => true, 'duration_minutes' => 60, 'buffer_minutes' => 15],
            ['key' => 'rct', 'label' => 'عصب‌کشی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 3, 'status_color' => '#EDE7F6', 'status_border' => '#9B7FD4', 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'crown', 'label' => 'روکش', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 4, 'status_color' => '#FEF9E7', 'status_border' => '#F0C040', 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'bridge_abutment', 'label' => 'بریج (پایه)', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 5, 'status_color' => '#EBF5FB', 'status_border' => '#78C0E0', 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'veneer', 'label' => 'لامینیت/ونیر', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 6, 'status_color' => '#FEF0FB', 'status_border' => '#D98FD6', 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'inlay_onlay', 'label' => 'انله/آنله', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 7, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2', 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'composite', 'label' => 'ترمیم کامپوزیت', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 8, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2', 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'amalgam', 'label' => 'ترمیم آمالگام', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 9, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2', 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'pulpotomy', 'label' => 'پالپوتومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $primaryOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'pulpectomy', 'label' => 'پالپکتومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $primaryOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 40, 'buffer_minutes' => 5],
            ['key' => 'buildup', 'label' => 'بیلدآپ', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'apicoectomy', 'label' => 'آپیکوستومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 60, 'buffer_minutes' => 15],
            ['key' => 'retainer_fix', 'label' => 'ریتینر فیکس', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],

            // Tooth-scoped diagnostic codes — findings recorded during an existing
            // visit, never booked as their own appointment type.
            ['key' => 'suspect_endo', 'label' => 'مشکوک به اندو', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => false, 'duration_minutes' => null, 'buffer_minutes' => 0],
            ['key' => 'suspect_resto', 'label' => 'مشکوک به ترمیم', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => false, 'duration_minutes' => null, 'buffer_minutes' => 0],
            ['key' => 'resto_control', 'label' => 'کنترل ترمیم', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => false, 'duration_minutes' => null, 'buffer_minutes' => 0],

            // Tooth-scoped radiography codes
            ['key' => 'xray_pa', 'label' => 'عکس PA', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'cbct', 'label' => 'CBCT تک‌دندان', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'xray_occlusal', 'label' => 'رادیوگرافی اکلوزال', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'xray_lat_ceph', 'label' => 'لترال سفالیک', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'xray_tmj', 'label' => 'لترال TMJ', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],

            // Tooth-scoped consultation codes
            ['key' => 'consult_perio', 'label' => 'مشاوره پریو', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_endo', 'label' => 'مشاوره اندو', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_prosth', 'label' => 'مشاوره پروتز', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_resto', 'label' => 'مشاوره ترمیمی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_surgeon', 'label' => 'مشاوره جراح', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_peds', 'label' => 'مشاوره اطفال', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_diag', 'label' => 'مشاوره تشخیصی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
            ['key' => 'consult_general', 'label' => 'مشاوره عمومی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],

            ['key' => 'specialist_tx', 'label' => 'درمان تخصصی', 'category' => 'other', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => false, 'duration_minutes' => null, 'buffer_minutes' => 0],

            // Half-arch (quadrant-scoped) codes — legacy only ever used quadrants 1-4 for these
            ['key' => 'bwx', 'label' => 'بایت‌وینگ', 'category' => 'radiograph', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'scaling_half', 'label' => 'جرم‌گیری نیم‌فک', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'root_planing', 'label' => 'روت‌پلنینگ/کورتاژ', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'flap_surgery', 'label' => 'فلاپ جراحی', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 60, 'buffer_minutes' => 15],
            ['key' => 'consult_perio_h', 'label' => 'مشاوره پریو (نیم‌فک)', 'category' => 'consultation', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],

            // Arch-scoped codes — one full jaw at a time (recorded once per
            // arch via the arch button, e.g. "جرم‌گیری" applied to upper then
            // lower separately), not a single whole-mouth action.
            ['key' => 'panoramic', 'label' => 'پانورامیک', 'category' => 'radiograph', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'scaling_full', 'label' => 'جرم‌گیری', 'category' => 'treatment', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 5],
            ['key' => 'brushing', 'label' => 'بروساژ', 'category' => 'treatment', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'fluoride', 'label' => 'فلوراید تراپی', 'category' => 'treatment', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 15, 'buffer_minutes' => 0],
            ['key' => 'bleaching', 'label' => 'بلیچینگ', 'category' => 'treatment', 'scope' => 'arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 45, 'buffer_minutes' => 10],
            ['key' => 'ortho', 'label' => 'ارتودنسی', 'category' => 'treatment', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'consult_ortho', 'label' => 'مشاوره ارتودنسی', 'category' => 'consultation', 'scope' => 'arch', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],

            // Whole-mouth codes — genuinely a single atomic action across the
            // entire mouth, not repeated per arch.
            ['key' => 'fissure_seal', 'label' => 'فیشورسیلانت', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 30, 'buffer_minutes' => 5],
            ['key' => 'study_model', 'label' => 'مدل مطالعه', 'category' => 'other', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null, 'booking_eligible' => true, 'duration_minutes' => 20, 'buffer_minutes' => 0],
        ];

        foreach ($conditions as $condition) {
            DentalConditionCatalog::query()->updateOrCreate(['key' => $condition['key']], $condition);
        }
    }
}
