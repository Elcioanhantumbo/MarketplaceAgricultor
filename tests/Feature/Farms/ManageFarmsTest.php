<?php

namespace Tests\Feature\Farms;

use App\Livewire\Farms\Manage;
use App\Models\Buyer;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageFarmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_producer_can_create_a_farm(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Manage::class)
            ->set('name', 'Machamba do Rio')
            ->set('district', 'Nhamatanda')
            ->set('province', 'Sofala')
            ->set('latitude', '-19.2975')
            ->set('longitude', '34.7078')
            ->call('save');

        $this->assertSame(1, $producer->farms()->count());
        $this->assertSame('Machamba do Rio', $producer->farms()->first()->name);
    }

    public function test_producer_can_edit_own_farm(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $user->id]);
        $farm = $producer->farms()->create(['name' => 'Antigo nome']);

        Livewire::actingAs($user)
            ->test(Manage::class)
            ->call('edit', $farm->id)
            ->set('name', 'Novo nome')
            ->call('save');

        $this->assertSame('Novo nome', $farm->fresh()->name);
    }

    public function test_producer_cannot_edit_another_producers_farm(): void
    {
        $owner = User::factory()->create(['role' => 'producer']);
        $ownerProducer = Producer::create(['user_id' => $owner->id]);
        $farm = $ownerProducer->farms()->create(['name' => 'Machamba do Owner']);

        $intruder = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $intruder->id]);

        Livewire::actingAs($intruder)
            ->test(Manage::class)
            ->call('edit', $farm->id)
            ->assertForbidden();
    }

    public function test_producer_can_delete_own_farm(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $user->id]);
        $farm = $producer->farms()->create(['name' => 'A remover']);

        Livewire::actingAs($user)
            ->test(Manage::class)
            ->call('delete', $farm->id);

        $this->assertSame(0, $producer->farms()->count());
    }

    public function test_buyer_cannot_access_farm_management(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Manage::class)
            ->assertForbidden();
    }
}