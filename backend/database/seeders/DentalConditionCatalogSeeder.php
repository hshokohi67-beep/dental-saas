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
            ['key' => 'extraction', 'label' => 'کشیدن ساده', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 1, 'status_color' => '#F2F3F4', 'status_border' => '#B0BEC5'],
            ['key' => 'surgical_ext', 'label' => 'کشیدن جراحی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 1, 'status_color' => '#F2F3F4', 'status_border' => '#B0BEC5'],
            ['key' => 'implant', 'label' => 'ایمپلنت', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 2, 'status_color' => '#E8F8F5', 'status_border' => '#4DB6AC'],
            ['key' => 'rct', 'label' => 'عصب‌کشی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 3, 'status_color' => '#EDE7F6', 'status_border' => '#9B7FD4'],
            ['key' => 'crown', 'label' => 'روکش', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 4, 'status_color' => '#FEF9E7', 'status_border' => '#F0C040'],
            ['key' => 'bridge_abutment', 'label' => 'بریج (پایه)', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 5, 'status_color' => '#EBF5FB', 'status_border' => '#78C0E0'],
            ['key' => 'veneer', 'label' => 'لامینیت/ونیر', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 6, 'status_color' => '#FEF0FB', 'status_border' => '#D98FD6'],
            ['key' => 'inlay_onlay', 'label' => 'انله/آنله', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => 7, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2'],
            ['key' => 'composite', 'label' => 'ترمیم کامپوزیت', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 8, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2'],
            ['key' => 'amalgam', 'label' => 'ترمیم آمالگام', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => 9, 'status_color' => '#E8F4FC', 'status_border' => '#5DADE2'],
            ['key' => 'pulpotomy', 'label' => 'پالپوتومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $primaryOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'pulpectomy', 'label' => 'پالپکتومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $primaryOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'buildup', 'label' => 'بیلدآپ', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'apicoectomy', 'label' => 'آپیکوستومی', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'retainer_fix', 'label' => 'ریتینر فیکس', 'category' => 'treatment', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            // Tooth-scoped diagnostic codes
            ['key' => 'suspect_endo', 'label' => 'مشکوک به اندو', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'suspect_resto', 'label' => 'مشکوک به ترمیم', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'resto_control', 'label' => 'کنترل ترمیم', 'category' => 'diagnostic', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            // Tooth-scoped radiography codes
            ['key' => 'xray_pa', 'label' => 'عکس PA', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'cbct', 'label' => 'CBCT تک‌دندان', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'xray_occlusal', 'label' => 'رادیوگرافی اکلوزال', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'xray_lat_ceph', 'label' => 'لترال سفالیک', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'xray_tmj', 'label' => 'لترال TMJ', 'category' => 'radiograph', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            // Tooth-scoped consultation codes
            ['key' => 'consult_perio', 'label' => 'مشاوره پریو', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_endo', 'label' => 'مشاوره اندو', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_prosth', 'label' => 'مشاوره پروتز', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_resto', 'label' => 'مشاوره ترمیمی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_surgeon', 'label' => 'مشاوره جراح', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_peds', 'label' => 'مشاوره اطفال', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_diag', 'label' => 'مشاوره تشخیصی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_general', 'label' => 'مشاوره عمومی', 'category' => 'consultation', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            ['key' => 'specialist_tx', 'label' => 'درمان تخصصی', 'category' => 'other', 'scope' => 'tooth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            // Half-arch (quadrant-scoped) codes — legacy only ever used quadrants 1-4 for these
            ['key' => 'bwx', 'label' => 'بایت‌وینگ', 'category' => 'radiograph', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'scaling_half', 'label' => 'جرم‌گیری نیم‌فک', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'root_planing', 'label' => 'روت‌پلنینگ/کورتاژ', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'flap_surgery', 'label' => 'فلاپ جراحی', 'category' => 'treatment', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_perio_h', 'label' => 'مشاوره پریو (نیم‌فک)', 'category' => 'consultation', 'scope' => 'half_arch', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],

            // Whole-mouth codes
            ['key' => 'panoramic', 'label' => 'پانورامیک', 'category' => 'radiograph', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'scaling_full', 'label' => 'جرم‌گیری کل دهان', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'brushing', 'label' => 'بروساژ', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'fissure_seal', 'label' => 'فیشورسیلانت', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'fluoride', 'label' => 'فلوراید تراپی', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'bleaching', 'label' => 'بلیچینگ', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $permanentOnly, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'ortho', 'label' => 'ارتودنسی', 'category' => 'treatment', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'consult_ortho', 'label' => 'مشاوره ارتودنسی', 'category' => 'consultation', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
            ['key' => 'study_model', 'label' => 'مدل مطالعه', 'category' => 'other', 'scope' => 'whole_mouth', 'dentitions' => $both, 'status_priority' => null, 'status_color' => null, 'status_border' => null],
        ];

        foreach ($conditions as $condition) {
            DentalConditionCatalog::query()->updateOrCreate(['key' => $condition['key']], $condition);
        }
    }
}
