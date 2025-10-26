<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new UserRepository();
    }

    /**
     * Testa se findByEmail retorna o usuário correto.
     */
    public function test_find_by_email_returns_correct_user(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $found = $this->repository->findByEmail('test@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
        $this->assertEquals('test@example.com', $found->email);
    }

    /**
     * Testa se findByEmail retorna null para email não existente.
     */
    public function test_find_by_email_returns_null_for_non_existing_email(): void
    {
        $found = $this->repository->findByEmail('nonexistent@example.com');

        $this->assertNull($found);
    }

    /**
     * Testa se createWithHashedPassword cria usuário com senha hasheada.
     */
    public function test_create_with_hashed_password_hashes_password(): void
    {
        $user = $this->repository->createWithHashedPassword([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plaintext_password',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('test@example.com', $user->email);

        // Verifica se a senha foi hasheada
        $this->assertNotEquals('plaintext_password', $user->password);
        $this->assertTrue(Hash::check('plaintext_password', $user->password));

        // Verifica se foi salvo no banco
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
    }

    /**
     * Testa se emailExists retorna true para email existente.
     */
    public function test_email_exists_returns_true_for_existing_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $exists = $this->repository->emailExists('existing@example.com');

        $this->assertTrue($exists);
    }

    /**
     * Testa se emailExists retorna false para email não existente.
     */
    public function test_email_exists_returns_false_for_non_existing_email(): void
    {
        $exists = $this->repository->emailExists('nonexistent@example.com');

        $this->assertFalse($exists);
    }

    /**
     * Testa o comportamento de emailExists com diferentes cases.
     *
     * Nota: SQLite (usado em testes) é case-sensitive, mas MySQL em produção
     * é case-insensitive por padrão. A validação de unicidade de email
     * deve ser tratada no nível de aplicação (FormRequest).
     */
    public function test_email_exists_case_handling(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        // Em SQLite (testes), é case-sensitive
        // Em MySQL (produção), seria case-insensitive
        $exists = $this->repository->emailExists('Test@Example.com');

        // Documenta o comportamento do SQLite (case-sensitive)
        // Em produção com MySQL, retornaria true
        $this->assertFalse($exists);
    }

    /**
     * Testa os métodos CRUD básicos herdados do BaseRepository.
     */
    public function test_base_repository_crud_methods(): void
    {
        // Create
        $user = $this->repository->create([
            'name' => 'CRUD Test User',
            'email' => 'crud@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('CRUD Test User', $user->name);

        // Find
        $found = $this->repository->find($user->id);
        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);

        // FindOrFail
        $foundOrFail = $this->repository->findOrFail($user->id);
        $this->assertEquals($user->id, $foundOrFail->id);

        // Update
        $updated = $this->repository->update($user->id, ['name' => 'Updated CRUD User']);
        $this->assertEquals('Updated CRUD User', $updated->name);

        // All
        $all = $this->repository->all();
        $this->assertGreaterThanOrEqual(1, $all->count());

        // Delete
        $deleted = $this->repository->delete($user->id);
        $this->assertTrue($deleted);
        $this->assertNull($this->repository->find($user->id));
    }

    /**
     * Testa se createWithHashedPassword não expõe senha em texto plano.
     */
    public function test_create_with_hashed_password_does_not_expose_plain_password(): void
    {
        $plainPassword = 'super_secret_password_123';

        $user = $this->repository->createWithHashedPassword([
            'name' => 'Security Test',
            'email' => 'security@example.com',
            'password' => $plainPassword,
        ]);

        // Verifica que a senha não está em texto plano
        $this->assertStringNotContainsString($plainPassword, $user->password);

        // Verifica que a senha foi hasheada com bcrypt (60 caracteres)
        $this->assertEquals(60, strlen($user->password));
        $this->assertStringStartsWith('$2y$', $user->password);
    }
}
