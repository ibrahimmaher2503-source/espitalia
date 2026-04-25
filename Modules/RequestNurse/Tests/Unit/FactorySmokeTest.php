<?php

namespace Modules\RequestNurse\Tests\Unit;

use Modules\RequestNurse\Models\Nurse;
use Modules\RequestNurse\Models\NurseRequest;

class FactorySmokeTest extends BaseTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->runMigrations();
    }

    public function test_nurse_factory_creates_record()
    {
        $nurse = Nurse::factory()->create();

        $this->assertDatabaseHas('nurses', [
            'id' => $nurse->id,
            'nurse_id' => $nurse->nurse_id,
        ]);
    }

    public function test_nurse_request_factory_creates_record()
    {
        $request = NurseRequest::factory()->create();

        $this->assertDatabaseHas('nurse_requests', [
            'id' => $request->id,
            'patient_id' => $request->patient_id,
        ]);
    }
}
