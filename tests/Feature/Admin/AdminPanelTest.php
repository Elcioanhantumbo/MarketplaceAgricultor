<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Categories as AdminCategories;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Disputes as AdminDisputes;
use App\Livewire\Admin\Users as AdminUsers;
use App\Livewire\Admin\Verifications as AdminVerifications;
use App\Livewire\Orders\Show as ShowOrder;
use App\Models\AuditLog;
use App\Models\Buyer;
use App\Models\Category;
use App\Models\Complaint;
use App\Models\Producer;
use App\Models\ProductListing;
use App\Models\User;
use App\Services\OrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function listing(float $quantity = 100): ProductListing
    {
        $producerUser = User::factory()->create(['role' => 'producer']);
        $producer = Producer::create(['user_id' => $producerUser->id]);
        $category = Category::firstOrCreate(['slug' => 'cereais'], ['name' => 'Cereais']);
        $product = $category->products()->firstOrCreate(['slug' => 'milho'], ['name' => 'Milho', 'default_unit' => 'kg']);

        return $producer->productListings()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'unit' => 'kg',
            'price' => 10,
            'available_from' => now()->subDay(),
            'available_until' => now()->addDays(10),
            'status' => 'disponivel',
        ]);
    }

    private function buyer(): User
    {
        $user = User::factory()->create(['role' => 'buyer']);
        Buyer::create(['user_id' => $user->id]);

        return $user;
    }

    public function test_non_admin_cannot_access_any_admin_page(): void
    {
        $buyer = $this->buyer();

        Livewire::actingAs($buyer)->test(AdminDashboard::class)->assertForbidden();
        Livewire::actingAs($buyer)->test(AdminUsers::class)->assertForbidden();
        Livewire::actingAs($buyer)->test(AdminCategories::class)->assertForbidden();
    }

    public function test_admin_can_view_dashboard_kpis(): void
    {
        Livewire::actingAs($this->admin())->test(AdminDashboard::class)->assertOk();
    }

    public function test_admin_can_block_and_unblock_a_user_and_it_is_audited(): void
    {
        $admin = $this->admin();
        $buyer = $this->buyer();

        Livewire::actingAs($admin)->test(AdminUsers::class)->call('toggleBlocked', $buyer->id);

        $this->assertSame('blocked', $buyer->fresh()->status);
        $this->assertSame(1, AuditLog::where('action', 'user.blocked')->count());

        Livewire::actingAs($admin)->test(AdminUsers::class)->call('toggleBlocked', $buyer->id);
        $this->assertSame('active', $buyer->fresh()->status);
    }

    public function test_admin_cannot_block_their_own_account(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)->test(AdminUsers::class)->call('toggleBlocked', $admin->id)->assertStatus(400);

        $this->assertSame('active', $admin->fresh()->status);
    }

    public function test_admin_can_verify_a_complete_profile_and_it_is_audited(): void
    {
        $admin = $this->admin();
        $producerUser = User::factory()->create(['role' => 'producer']);
        Producer::create(['user_id' => $producerUser->id]);
        $producerUser->profile()->create(['address' => 'Bairro Central']);

        $this->assertSame(1, \App\Models\Profile::whereNull('verified_at')->where('address', '!=', '')->count());

        Livewire::actingAs($admin)
            ->test(AdminVerifications::class)
            ->call('verify', $producerUser->profile->id);

        $this->assertNotNull($producerUser->profile->fresh()->verified_at);
        $this->assertSame(1, AuditLog::where('action', 'profile.verified')->count());
    }

    public function test_admin_can_add_category_and_product(): void
    {
        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(AdminCategories::class)
            ->set('category_name', 'Tubérculos')
            ->call('addCategory');

        $this->assertDatabaseHas('categories', ['name' => 'Tubérculos']);
        $this->assertSame(1, AuditLog::where('action', 'category.created')->count());
    }

    public function test_buyer_can_report_complaint_after_delivery_and_admin_can_resolve_it(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');
        $producer = $order->producer->user;
        $workflow->accept($order, $producer);
        $workflow->advance($order, $producer, 'em_preparacao');
        $workflow->advance($order, $producer, 'pronto');
        $workflow->advance($order, $producer, 'em_transporte');
        $workflow->advance($order, $producer, 'entregue');

        Livewire::actingAs($buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('complaint_description', 'O produto chegou em más condições.')
            ->call('reportComplaint')
            ->assertHasNoErrors();

        $complaint = Complaint::where('order_id', $order->id)->first();
        $this->assertNotNull($complaint);
        $this->assertSame('aberta', $complaint->status);

        $admin = $this->admin();
        Livewire::actingAs($admin)
            ->test(AdminDisputes::class)
            ->call('startResolving', $complaint->id)
            ->set('resolution_status', 'procedente')
            ->set('resolution', 'Reembolso acordado com o produtor.')
            ->call('resolve');

        $complaint->refresh();
        $this->assertSame('procedente', $complaint->status);
        $this->assertNotNull($complaint->resolved_at);
        $this->assertSame($admin->id, $complaint->resolved_by);
        $this->assertSame(1, AuditLog::where('action', 'complaint.resolved')->count());
    }

    public function test_cannot_report_complaint_before_delivery(): void
    {
        $workflow = app(OrderWorkflowService::class);
        $listing = $this->listing();
        $buyer = $this->buyer();
        $order = $workflow->create($buyer, $listing, 4, 'comprador_levanta');

        Livewire::actingAs($buyer)
            ->test(ShowOrder::class, ['order' => $order])
            ->set('complaint_description', 'Ainda nem foi aceite.')
            ->call('reportComplaint');

        $this->assertSame(0, Complaint::count());
    }
}