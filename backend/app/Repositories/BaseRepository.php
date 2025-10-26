<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Classe base abstrata para implementação de repositórios.
 *
 * Implementa os métodos CRUD comuns a todos os repositórios,
 * evitando duplicação de código. Classes filhas devem definir
 * o model que será manipulado através do método getModel().
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * Model Eloquent que será manipulado pelo repositório.
     *
     * @var Model
     */
    protected Model $model;

    /**
     * Construtor do repositório.
     *
     * Instancia o model através do método abstrato getModel(),
     * que deve ser implementado pelas classes filhas.
     */
    public function __construct()
    {
        $this->model = $this->getModel();
    }

    /**
     * Retorna a instância do Model Eloquent.
     *
     * Este método deve ser implementado pelas classes filhas
     * para retornar o model específico do repositório.
     *
     * @return Model
     */
    abstract protected function getModel(): Model;

    /**
     * {@inheritDoc}
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * {@inheritDoc}
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * {@inheritDoc}
     */
    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }
}
