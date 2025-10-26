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
     * Resolve o model através do método abstrato getModel(),
     * que deve retornar a classe do model (string FQCN).
     * O Laravel Service Container instancia o model automaticamente.
     */
    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    /**
     * Retorna o nome completo da classe do Model (FQCN).
     *
     * Este método deve ser implementado pelas classes filhas
     * para retornar a classe do model específico do repositório.
     * Exemplo: return Seller::class;
     *
     * @return string
     */
    abstract protected function getModel(): string;

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
