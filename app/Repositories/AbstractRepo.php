<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;

abstract class AbstractRepo
{
    protected $model;
    protected $withRelations = [];

    public function getModel()
    {
        return $this->model;
    }

    public function getByID($id)
    {
        $item = $this->model
            ->with($this->withRelations)
            ->find($id);

        return $this->mapItem($item);
    }

    public function getBySlug($slug)
    {
        $item = $this->model
            ->where('slug', $slug)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function getByUserID($user_id)
    {
        $item = $this->model
            ->where('user_id', $user_id)
            ->with($this->withRelations)
            ->first();

        return $this->mapItem($item);
    }

    public function setRelations(array $relations)
    {
        $this->withRelations = $relations;
        return $this;
    }

    public function getAll($filter = [], $paginate = 20, array $sorting = [])
    {
        $query = $this->applyFilter($this->model->with($this->withRelations), $filter);
        $query = $this->applySorting($query, $sorting);

        $items = $query->paginate($paginate);

        return $this->mapItems($items);
    }

    public function getFirst($filter = [], array $sorting = [])
    {
        $query = $this->applyFilter($this->model->with($this->withRelations), $filter);
        $query = $this->applySorting($query, $sorting);

        $item = $query->first();

        return $this->mapItem($item);
    }

    public function count($filter = [])
    {
        $query = $this->applyFilter($this->model->newQuery(), $filter);

        return $query->count();
    }

    public function exists($filter = [])
    {
        $query = $this->applyFilter($this->model->newQuery(), $filter);

        return $query->exists();
    }

    public function create($data)
    {
        $data = $this->beforeCreate($data);

        $item = $this->model->create($data);
        // Refresh to pick up DB-side defaults (useCurrent, enum defaults) and eager-load configured relations.
        return $this->mapItem($item->fresh($this->withRelations));
    }

    public function beforeCreate($data)
    {
        return $data;
    }

    public function update($id, $data)
    {
        $item = $this->model->find($id);

        if (!$item) {
            return null;
        }

        $data = $this->beforeUpdate($data, $item);
        $item->update($data);

        return $this->mapItem($item->fresh($this->withRelations));
    }

    public function beforeUpdate($data, $item = null)
    {
        return $data;
    }

    public function delete($id)
    {
        $item = $this->model->find($id);

        if (!$item) {
            return false;
        }

        $item->delete();
        return true;
    }

    public function prepareItemsUpsert($items)
    {
        if (empty($items)) return [];

        foreach ($items as $key => $item) {
            foreach ($item as $field_key => $field_value) {
                if (is_array($field_value)) {
                    $items[$key][$field_key] = json_encode($field_value);
                }
            }
        }

        return $items;
    }

    public function syncItemsUpsert($items, $uniqueConstraints = [])
    {
        if (empty($items)) return;

        $items = $this->prepareItemsUpsert($items);

        $this->model->upsert(
            $items,
            $uniqueConstraints
        );
    }

    public function mapItems($items)
    {
        if (empty($items)) {
            return null;
        }

        if ($items instanceof Collection) {
            $itemsMapped = $items->transform(function ($item) {
                return $this->mapItem($item);
            });
        } else {
            $itemsMapped = $items->getCollection()->transform(function ($item) {
                return $this->mapItem($item);
            });
        }

        return [
            'items' => $itemsMapped,
            'Model' => $items,
        ];
    }

    public function mapItem($item)
    {
        if (empty($item)) {
            return null;
        }

        return [
            'id' => $item->id,
            'Model' => $item,
        ];
    }

    protected function applyFilter($query, array $filter)
    {
        foreach ($filter as $rawKey => $value) {
            preg_match('/^([a-zA-Z0-9_]+)([><!=]{1,2})?$/', $rawKey, $matches);
            $key = $matches[1] ?? $rawKey;
            $operator = $matches[2] ?? '=';

            if (is_array($value) && isset($value['CONDITION'])) {
                $condition = strtoupper($value['CONDITION']);
                $filtered = array_filter($value, fn($v, $k) => $k !== 'CONDITION', ARRAY_FILTER_USE_BOTH);

                switch ($condition) {
                    case 'BETWEEN':
                        if (count($filtered) === 2) {
                            $query->whereBetween($key, array_values($filtered));
                        }
                        break;
                    case 'IN':
                        $query->whereIn($key, array_values($filtered));
                        break;
                    case 'NOT IN':
                        $query->whereNotIn($key, array_values($filtered));
                        break;
                    case 'NULL':
                        $query->whereNull($key);
                        break;
                    case 'NOT NULL':
                        $query->whereNotNull($key);
                        break;
                }

                continue;
            }

            if (is_string($value) && strpos($value, '%') !== false) {
                $query->where($key, 'LIKE', $value);
            } elseif (is_array($value)) {
                $query->whereIn($key, array_filter($value));
            } else {
                $query->where($key, $operator, $value);
            }
        }

        return $query;
    }

    protected function applySorting($query, array $sorting)
    {
        if (!empty($sorting)) {
            foreach ($sorting as $column => $direction) {
                $query->orderBy($column, is_array($direction) ? $direction[0] : $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        return $query;
    }
}
