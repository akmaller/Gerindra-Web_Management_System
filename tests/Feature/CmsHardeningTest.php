<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\AdSetting;
use App\Models\ChatbotSetting;
use App\Models\HomepageSetting;
use App\Models\Post;
use App\Models\PostView;
use App\Models\User;
use App\Services\PopularPosts;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        foreach (['admin', 'editor', 'penulis'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'web']);
        }
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function makePost(User $user, array $attributes = []): Post
    {
        return Post::create(array_merge(['user_id' => $user->id, 'title' => 'Berita pengujian', 'content' => '<p>Isi berita.</p>', 'status' => 'published', 'published_at' => now()->subDay()], $attributes));
    }

    public function test_only_admin_can_manage_script_ads(): void
    {
        foreach (['penulis', 'editor'] as $role) {
            $user = $this->user($role);
            $this->assertFalse(Gate::forUser($user)->allows('create', AdSetting::class));
            $this->actingAs($user)->get('/admin/ad-settings')->assertForbidden();
        }
        $this->assertTrue(Gate::forUser($this->user('admin'))->allows('create', AdSetting::class));
    }

    public function test_author_cannot_edit_other_authors_post(): void
    {
        $owner = $this->user('penulis');
        $other = $this->user('penulis');
        $post = $this->makePost($owner);
        $this->assertFalse(Gate::forUser($other)->allows('update', $post));
        $this->assertTrue(Gate::forUser($owner)->allows('update', $post));
        $this->actingAs($other)->get('/admin/posts/'.$post->id.'/edit')->assertNotFound();
    }

    public function test_creating_editor_preserves_selected_role(): void
    {
        $this->actingAs($this->user('admin'));
        Livewire::test(CreateUser::class)->fillForm([
            'name' => 'Editor Test', 'email' => 'editor-test@example.test',
            'password' => 'Only-for-tests-123!',
            'roles' => [Role::findByName('editor')->id],
        ])->call('create')->assertHasNoFormErrors();
        $created = User::where('email', 'editor-test@example.test')->firstOrFail();
        $this->assertTrue($created->hasRole('editor'));
        $this->assertFalse($created->hasRole('penulis'));
    }

    public function test_bulk_publish_and_unpublish_work(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);
        $post = $this->makePost($admin, ['status' => 'draft', 'published_at' => null]);
        Livewire::test(ListPosts::class)->callTableBulkAction('publish', [$post]);
        $this->assertSame('published', $post->fresh()->status);
        Livewire::test(ListPosts::class)->callTableBulkAction('unpublish', [$post]);
        $this->assertSame('draft', $post->fresh()->status);
    }

    public function test_scheduled_posts_become_visible_only_when_due(): void
    {
        $user = $this->user('penulis');
        $due = $this->makePost($user, ['status' => 'scheduled', 'published_at' => now()->subMinute()]);
        $future = $this->makePost($user, ['status' => 'scheduled', 'published_at' => now()->addHour()]);
        $this->assertTrue(Post::published()->whereKey($due->id)->exists());
        $this->assertFalse(Post::published()->whereKey($future->id)->exists());
    }

    public function test_withdrawn_post_disappears_from_cached_popular_list(): void
    {
        $post = $this->makePost($this->user('penulis'));
        PostView::create(['post_id' => $post->id, 'viewed_at' => now()]);
        $this->assertCount(1, PopularPosts::range('today'));
        $post->update(['status' => 'draft']);
        $this->assertCount(0, PopularPosts::range('today'));
    }

    public function test_public_post_html_removes_script_and_event_handlers(): void
    {
        $post = $this->makePost($this->user('penulis'), ['content' => '<p>Konten aman</p><script>alert(1)</script><img src="x" onerror="alert(2)">']);
        $this->get($post->permalink)->assertOk()->assertSee('Konten aman')->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('onerror="alert(2)"', false);
    }

    public function test_slug_remains_stable_and_duplicate_titles_are_allowed(): void
    {
        $user = $this->user('penulis');
        $first = $this->makePost($user);
        $second = $this->makePost($user);
        $this->assertNotSame($first->slug, $second->slug);
        $slug = $first->slug;
        $first->update(['title' => 'Judul baru']);
        $this->assertSame($slug, $first->fresh()->slug);
    }

    public function test_search_is_paginated_and_does_not_include_drafts(): void
    {
        $user = $this->user('penulis');
        for ($i = 0; $i < 14; $i++) {
            $this->makePost($user, ['title' => "Pencarian berita $i"]);
        }
        $this->makePost($user, ['title' => 'Pencarian rahasia', 'status' => 'draft']);
        $this->get('/search?q=Pencarian')->assertOk()->assertViewHas('results', fn ($results) => $results->total() === 14 && $results->count() === 12)->assertDontSee('Pencarian rahasia');
        $this->get('/search?q=Pencarian&page=2')->assertOk()->assertViewHas('results', fn ($results) => $results->count() === 2);
    }

    public function test_chatbot_rejects_oversized_history_before_upstream_call(): void
    {
        ChatbotSetting::current()->update(['module_enabled' => true, 'endpoint' => 'https://example.test/chat']);
        Http::fake();
        $this->postJson('/chatbot/message', ['message' => 'Halo', 'history' => [['role' => 'user', 'content' => str_repeat('x', 3001)]]])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_admin_screens_render_after_dependency_update(): void
    {
        $this->actingAs($this->user('admin'));
        foreach (['posts', 'categories', 'pages', 'menus', 'settings', 'homepage-settings', 'company-profile', 'chatbot-embed', 'users', 'profil-saya', 'ad-settings'] as $screen) {
            $this->get('/admin/'.$screen)->assertOk();
        }
    }

    public function test_anonymous_user_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_account_without_editorial_role_cannot_enter_panel(): void
    {
        $user = User::factory()->create();
        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_homepage_rich_text_is_sanitized_and_hero_is_server_rendered(): void
    {
        $this->makePost($this->user('admin'));
        HomepageSetting::current()->update(['tab_sections' => [['title' => 'Profil', 'content' => '<p>Profil aman</p><script>alert(777)</script>']]]);
        $this->get('/')->assertOk()->assertSee('fetchpriority="high"', false)->assertSee('Profil aman')->assertDontSee('alert(777)', false);
    }

    public function test_seeders_do_not_promote_or_reset_existing_users(): void
    {
        $user = User::factory()->create();
        $password = $user->password;
        $this->seed(DatabaseSeeder::class);
        $this->assertSame($password, $user->fresh()->password);
        $this->assertFalse($user->fresh()->hasRole('admin'));
        $this->assertSame(1, User::count());
    }
    public function test_footer_preserves_social_links_without_repeated_view_queries(): void
    {
        \App\Models\CompanyProfile::create(['company_name' => 'Profil contoh', 'instagram' => 'https://www.instagram.com/example/']);
        $response = $this->get('/')->assertOk();
        $this->assertSame(3, substr_count($response->getContent(), 'aria-label="Instagram (tab baru)"'));
    }

    public function test_indonesian_pagination_labels_are_available(): void
    {
        app()->setLocale('id');
        $this->assertSame('Berikutnya »', __('pagination.next'));
        $this->assertSame('« Sebelumnya', __('pagination.previous'));
    }
}
