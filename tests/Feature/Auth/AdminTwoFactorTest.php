<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\ConfirmTwoFactor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Secção 22 — 2FA para contas administrativas. Ao contrário da autorização
 * por papel (já coberta por RequiresAdmin/EnsureIsAdmin), esta é uma
 * verificação de sessão feita pelo middleware de rota ('admin.2fa'), por
 * isso os testes aqui fazem pedidos HTTP reais (não Livewire::test, que
 * instancia o componente sem passar pelo middleware).
 */
class AdminTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_is_redirected_to_confirm_two_factor_before_reaching_the_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertRedirect(route('confirmar-acesso'));
    }

    public function test_operator_is_also_required_to_confirm_two_factor(): void
    {
        $operator = User::factory()->create(['role' => 'operator']);

        $this->actingAs($operator)->get(route('admin.dashboard'))
            ->assertRedirect(route('confirmar-acesso'));
    }

    public function test_non_admin_user_is_forbidden_before_ever_reaching_two_factor(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $this->actingAs($buyer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_non_admin_user_cannot_open_the_confirm_two_factor_page(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        Livewire::actingAs($buyer)->test(ConfirmTwoFactor::class)->assertForbidden();
    }

    public function test_admin_can_confirm_with_the_correct_code_and_then_reach_the_intended_admin_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Visitar directamente /admin/utilizadores guarda essa página como "intended".
        $this->actingAs($admin)->get(route('admin.utilizadores'))
            ->assertRedirect(route('confirmar-acesso'));

        // mount() já envia o OTP — substitui-se o código gerado por um valor
        // conhecido (o hash não é recuperável), como no fluxo de OTP de telefone.
        $component = Livewire::actingAs($admin)->test(ConfirmTwoFactor::class);
        $plainCode = '654321';
        $admin->otpCodes()->where('purpose', 'admin_2fa')->latest()->first()->update(['code' => bcrypt($plainCode)]);

        $component->set('code', $plainCode)
            ->call('verify')
            ->assertRedirect(route('admin.utilizadores'));

        // A confirmação fica válida para o resto da sessão — não pede outra vez.
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_wrong_code_does_not_confirm_two_factor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)->test(ConfirmTwoFactor::class)
            ->set('code', '000000')
            ->call('verify')
            ->assertHasErrors('code');

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertRedirect(route('confirmar-acesso'));
    }
}