<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

/**
 * Repositório para operações relacionadas a usuários.
 *
 * Encapsula toda a lógica de acesso a dados da entidade User,
 * incluindo operações específicas para autenticação e gerenciamento de senhas.
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    protected function getModel(): string
    {
        return User::class;
    }

    /**
     * {@inheritDoc}
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * {@inheritDoc}
     */
    public function createWithHashedPassword(array $data): User
    {
        return $this->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}
