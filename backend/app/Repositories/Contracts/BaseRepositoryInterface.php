<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface base para todos os repositórios.
 *
 * Define os métodos CRUD padrão que todos os repositórios devem implementar.
 * Seguindo o padrão Repository, esta interface abstrai a camada de acesso a dados,
 * permitindo trocar implementações sem afetar a lógica de negócio.
 */
interface BaseRepositoryInterface
{
    /**
     * Retorna todos os registros.
     *
     * @return Collection
     */
    public function all(): Collection;

    /**
     * Busca um registro por ID.
     *
     * @param int $id
     * @return Model|null
     */
    public function find(int $id): ?Model;

    /**
     * Busca um registro por ID ou lança exceção se não encontrado.
     *
     * @param int $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id): Model;

    /**
     * Cria um novo registro.
     *
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model;

    /**
     * Atualiza um registro existente.
     *
     * @param int $id
     * @param array $data
     * @return Model
     */
    public function update(int $id, array $data): Model;

    /**
     * Deleta um registro.
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool;
}
