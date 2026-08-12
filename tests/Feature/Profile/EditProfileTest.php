<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\Edit;
use App\Models\Buyer;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_producer_can_complete_minimum_profile_and_becomes_rn02_compliant(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        $this->assertFalse($user->hasMinimumProfile());

        Livewire::actingAs($user)
            ->test(Edit::class)
            ->set('name', 'Produtor Actualizado')
            ->set('address', 'Bairro Central')
            ->set('district', 'Dondo')
            ->set('province', 'Sofala')
            ->set('business_name', 'Machambas Dondo Lda')
            ->call('save')
            ->assertSet('saved', true);

        $user->refresh();
        $this->assertTrue($user->hasMinimumProfile());
        $this->assertSame('Produtor Actualizado', $user->name);
        $this->assertSame('Machambas Dondo Lda', $user->producer->business_name);
    }

    public function test_buyer_can_set_buyer_type(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Edit::class)
            ->set('address', 'Av. Poder Popular')
            ->set('buyer_type', 'restaurante')
            ->call('save');

        $this->assertSame('restaurante', $user->buyer->fresh()->buyer_type);
    }

    public function test_address_is_required(): void
    {
        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Edit::class)
            ->set('address', '')
            ->call('save')
            ->assertHasErrors('address');
    }
}