<?php

use App\Domain\Buildings\Building;
use App\Domain\Elevators\Elevator;
use App\Domain\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\{postJson, actingAs};

it('manager can create building, task, assign to tech, tech updates & logs', function () {
    $tenantId = 1; // seeded
    $manager = User::factory()->create([
        'tenant_id' => $tenantId,
        'email' => 'mgr@example.com',
        'password' => Hash::make('password'),
        'role' => 'OWNER'
    ]);

    actingAs($manager);
    $building = Building::create(['tenant_id'=>$tenantId,'name'=>'B1']);
    $elevator = Elevator::create(['tenant_id'=>$tenantId,'building_id'=>$building->id,'code'=>'E1']);

    $resp = postJson('/api/manager/tasks', ['building_id'=>$building->id,'elevator_id'=>$elevator->id,'title'=>'Fix leak']);
    $resp->assertCreated()->json('id');

    $task = Task::first();
    $tech = User::factory()->create(['tenant_id'=>$tenantId,'role'=>'TECH']);
    postJson("/api/manager/tasks/{$task->id}/assign", ['assigned_to_id'=>$tech->id])->assertOk();

    actingAs($tech);
    postJson("/api/tech/tasks/{$task->id}/worklogs", ['description'=>'Done','duration_min'=>30])->assertCreated();
});
