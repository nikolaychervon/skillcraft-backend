<?php

namespace Tests\Unit\User;

use App\Actions\User\CreateNewUserAction;
use App\Actions\User\Password\SendPasswordResetLinkAction;
use App\Cache\User\Auth\PasswordResetTokensCache;
use App\DTO\User\CreatingUserDTO;
use App\Models\User;
use App\Notifications\User\PasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendPasswordResetLinkActionTest extends TestCase
{
    use RefreshDatabase;

    private SendPasswordResetLinkAction $action;
    private PasswordResetTokensCache $cache;
    private User $user;
    private string $email = 'test@example.com';

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = app(SendPasswordResetLinkAction::class);
        $this->cache = app(PasswordResetTokensCache::class);

        Notification::fake();

        $createUserAction = app(CreateNewUserAction::class);
        $dto = new CreatingUserDTO(
            firstName: 'Иван',
            lastName: 'Петров',
            email: $this->email,
            uniqueNickname: 'reset_link_test',
            password: 'Password123!',
            middleName: null
        );

        $this->user = $createUserAction->run($dto);
        $this->user->markEmailAsVerified();
    }

    public function test_it_sends_reset_link_successfully(): void
    {
        $this->action->run($this->email);

        Notification::assertSentTo(
            $this->user,
            PasswordResetNotification::class
        );

        $token = $this->cache->getTokenByEmail($this->email);
        $this->assertNotNull($token);
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
    }

    public function test_it_does_nothing_when_email_not_found(): void
    {
        Notification::fake();

        $this->action->run('nonexistent@example.com');

        Notification::assertNothingSent();

        $token = $this->cache->getTokenByEmail('nonexistent@example.com');
        $this->assertNull($token);
    }

    public function test_it_generates_unique_token_each_time(): void
    {
        $this->action->run($this->email);
        $firstToken = $this->cache->getTokenByEmail($this->email);

        $this->action->run($this->email);
        $secondToken = $this->cache->getTokenByEmail($this->email);

        $this->assertNotEquals($firstToken, $secondToken);
        $this->assertEquals(64, strlen($firstToken));
        $this->assertEquals(64, strlen($secondToken));
    }

    public function test_it_overwrites_old_token_when_requested_again(): void
    {
        $this->action->run($this->email);
        $oldToken = $this->cache->getTokenByEmail($this->email);

        $this->action->run($this->email);
        $newToken = $this->cache->getTokenByEmail($this->email);

        $this->assertNotEquals($oldToken, $newToken);
        $this->cache->delete($this->email);

        Notification::assertSentToTimes(
            $this->user,
            PasswordResetNotification::class,
            2
        );
    }
}
