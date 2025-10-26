<?php

namespace App\Repositories\Contracts;

use App\Models\User;

/**
 * Interface para o repositório de usuários.
 *
 * Define métodos específicos para operações relacionadas a usuários,
 * especialmente para autenticação e gerenciamento de credenciais.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Busca um usuário por email.
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Cria um usuário com password hasheado.
     *
     * @param array $data Deve conter 'name', 'email' e 'password' (texto plano)
     * @return User
     */
    public function createWithHashedPassword(array $data): User;

    /**
     * Verifica se um email já está cadastrado.
     *
     * @param string $email
     * @return bool
     */
    public function emailExists(string $email): bool;
}
