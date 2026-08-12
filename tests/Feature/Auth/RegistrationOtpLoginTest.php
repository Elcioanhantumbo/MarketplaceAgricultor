<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\VerifyOtp;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use Tests\TestCase;

class RegistrationOtpLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_producer_can_register_and_is_pending_until_otp_verified(): void
    {
        Livewire::test(Register::class)
            ->set('name', 'Produtor Teste')
            ->set('phone', '84 123 4567')
            ->set('role', 'producer')
            ->set('password', 'senha1234')
            ->set('password_confirmation', 'senha1234')
            ->call('register')
            ->assertRedirect(route('verificar-telefone'));

        $user = User::where('phone', '+258841234567')->first();

        $this->assertNotNull($user);
        $this->assertSame('pending', $user->status);
        $this->assertNull($user->phone_verified_at);
        $this->assertNotNull($user->producer, 'Deve criar o registo Producer associado.');
        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_otp_code_is_rejected_and_correct_code_activates_the_account(): void
    {
        $user = User::factory()->create([
            'phone' => '+258841234568',
            'status' => 'pending',
            'phone_verified_at' => null,
        ]);

        app(OtpService::class)->generate($user);
        $otp = $user->otpCodes()->latest()->first();

        Livewire::actingAs($user)
            ->test(VerifyOtp::class)
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        $this->assertNull($user->fresh()->phone_verified_at);

        // Simula o código real gerado (o valor em texto não é recuperável do hash,
        // por isso substituímos por um novo código conhecido para o teste).
        $otp->update(['consumed_at' => now()]);
        $newOtp = app(OtpService::class)->generate($user);
        $plainCode = $this->extractPlainCodeForTesting($user, $newOtp->id);

        Livewire::actingAs($user)
            ->test(VerifyOtp::class)
            ->set('code', $plainCode)
            ->call('verify')
            ->assertRedirect(route('painel'));

        $user->refresh();
        $this->assertSame('active', $user->status);
        $this->assertNotNull($user->phone_verified_at);
    }

    public function test_blocked_user_cannot_login(): void
    {
        User::factory()->create([
            'phone' => '+258841234569',
            'password' => 'senha1234',
            'status' => 'blocked',
            'phone_verified_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('phone', '84 123 4569')
            ->set('password', 'senha1234')
            ->call('login')
            ->assertHasErrors('phone');

        $this->assertGuest();
    }

    public function test_verified_user_can_login_and_reach_painel(): void
    {
        $user = User::factory()->create([
            'phone' => '+258841234570',
            'password' => 'senha1234',
            'status' => 'active',
            'phone_verified_at' => now(),
        ]);

        Livewire::test(Login::class)
            ->set('phone', '84 123 4570')
            ->set('password', 'senha1234')
            ->call('login')
            ->assertRedirect(route('painel'));

        $this->assertAuthenticatedAs($user);

        $this->actingAs($user)->get(route('painel'))->assertOk();
    }

    /**
     * O código OTP é armazenado como hash (não recuperável); para testar a
     * verificação com o código certo, geramos directamente com um valor
     * conhecido em vez de depender do serviço aleatório.
     */
    private function extractPlainCodeForTesting(User $user, int $otpId): string
    {
        $plainCode = '123456';

        $user->otpCodes()->whereKey($otpId)->update([
            'code' => bcrypt($plainCode),
        ]);

        return $plainCode;
    }
}