<?php

namespace App\Application\Actions;

use App\Domain\Patients\Models\Patient;
use App\Domain\Patients\Models\PatientTimelineEvent;
use App\Domain\Patients\Support\PatientTimelineRecorder;
use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePatient
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Patient
    {
        $tenant = TenantContext::get();
        abort_unless($tenant !== null, 500, 'تنانت جاری مشخص نیست.');

        return DB::transaction(function () use ($data) {
            $patient = Patient::query()->create([
                ...$data,
                'status' => 'active',
                'created_by' => Auth::id(),
            ]);

            PatientTimelineRecorder::record(
                $patient,
                PatientTimelineEvent::TYPE_CREATED,
                'پرونده‌ی بیمار ایجاد شد.',
            );

            return $patient;
        });
    }
}
