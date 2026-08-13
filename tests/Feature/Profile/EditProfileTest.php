<?php

namespace Tests\Feature\Profile;

use App\Livewire\Profile\Edit;
use App\Models\Buyer;
use App\Models\Producer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    public function test_producer_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Edit::class)
            ->set('avatar', UploadedFile::fake()->image('avatar.jpg', 800, 600))
            ->set('address', 'Bairro Central')
            ->call('save')
            ->assertHasNoErrors();

        // Livewire::actingAs() partilha a mesma instância PHP de $user com
        // Auth::user() dentro do componente; mount() acede a $user->profile
        // (null, ainda não existia) e o Eloquent fica com essa relação em
        // cache no objecto partilhado. Consulta-se directamente para evitar
        // ler o valor antigo em cache.
        $this->assertDatabaseHas('profiles', ['user_id' => $user->id]);
        $path = \App\Models\Profile::where('user_id', $user->id)->value('avatar_path');
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        Livewire::actingAs($user)
            ->test(Edit::class)
            ->set('avatar', UploadedFile::fake()->create('documento.pdf', 100))
            ->assertHasErrors('avatar');
    }

    public function test_uploading_a_new_avatar_removes_the_previous_file(): void
    {
        Storage::fake('public');

        $user = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $user->id]);

        $component = Livewire::actingAs($user)->test(Edit::class);
        $component->set('avatar', UploadedFile::fake()->image('primeiro.jpg'));
        $firstPath = $component->get('avatar_path');

        $component->set('avatar', UploadedFile::fake()->image('segundo.jpg'));
        $secondPath = $component->get('avatar_path');

        $this->assertNotSame($firstPath, $secondPath);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($secondPath);
    }
}