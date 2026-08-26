<?php

namespace Tests\Feature;

use App\Models\QrTimer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrTimerTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_access_timer_page()
    {
        $response = $this->withSession([
            'role' => 'member',
            'nik' => '12345',
            'name' => 'Budi',
        ])->get(route('member.timer.index'));

        $response->assertStatus(200);
        $response->assertSee('Menu Timer QR');
    }

    public function test_scan_flow_start_and_finish()
    {
        // 1. First Scan (Start Timer)
        $response1 = $this->withSession([
            'role' => 'member',
            'nik' => '12345',
            'name' => 'Budi',
        ])->postJson(route('member.timer.scan'), [
            'qr_code' => 'QR-PART-001',
        ]);

        $response1->assertStatus(200);
        $response1->assertJson([
            'status' => 'running',
            'action' => 'start',
        ]);

        $this->assertDatabaseHas('qr_timers', [
            'nik' => '12345',
            'qr_code' => 'QR-PART-001',
            'status' => 'running',
        ]);

        $timer = QrTimer::where('nik', '12345')->where('qr_code', 'QR-PART-001')->first();
        $this->assertNotNull($timer);
        $this->assertNull($timer->end_time);

        // 2. Second Scan with same QR (Finish Timer)
        $response2 = $this->withSession([
            'role' => 'member',
            'nik' => '12345',
            'name' => 'Budi',
        ])->postJson(route('member.timer.scan'), [
            'qr_code' => 'QR-PART-001',
        ]);


        $response2->assertStatus(200);
        $response2->assertJson([
            'status' => 'completed',
            'action' => 'stop',
        ]);

        $timer->refresh();
        $this->assertEquals('completed', $timer->status);
        $this->assertNotNull($timer->end_time);
        $this->assertNotNull($timer->duration_seconds);
    }
}

