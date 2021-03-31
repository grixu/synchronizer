<?php

namespace Grixu\Synchronizer;

use Closure;
use Grixu\RelationshipDataTransferObject\RelationshipData;
use Grixu\RelationshipDataTransferObject\RelationshipDataCollection;
use Grixu\Synchronizer\Attributes\SynchronizeWith;
use Grixu\Synchronizer\Exceptions\WrongLocalModelException;
use Grixu\Synchronizer\Exceptions\WrongRelationTypeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Queue\SerializableClosure;
use ReflectionClass;

class RelationshipSynchronizer
{
    public function __construct(protected Model $model)
    {
    }

    public function sync(RelationshipDataCollection $relationships, SerializableClosure|Closure|null $errorHandler = null): void
    {
        foreach ($relationships as $relationship) {
            try {
                $this->syncRelationship($relationship);
            } catch (WrongLocalModelException | WrongRelationTypeException $e) {
                if (!empty($errorHandler)) {
                    $errorHandler($e);
                }

                continue;
            }
        }
    }

    public function syncRelationship(RelationshipData $relationshipData): void
    {
        if ($this->isForeignKeysEmpty($relationshipData)) {
            return;
        }

        $this->checkModelClass($relationshipData->localClass);

        $localMethod = $relationshipData->localRelationshipName;
        $this->checkRelationType($localMethod, $relationshipData->type);

        $foreignKeys = $this->loadForeignKeys($relationshipData);

        if (!empty($foreignKeys)) {
            switch ($relationshipData->type) {
                case BelongsTo::class:
                    $this->associateBelongsTo($this->model->$localMethod(), $foreignKeys[0]);
                    break;
                case BelongsToMany::class:
                    $this->attachManyToMany($this->model->$localMethod(), $foreignKeys);
            }

            $this->model->save();
        }
    }

    private function isForeignKeysEmpty(RelationshipData $relationshipData): bool
    {
        if (is_null($relationshipData->foreignKey) && empty($relationshipData->foreignKeys)) {
            return true;
        }

        return false;
    }

    private function checkModelClass(string $originalModel): void
    {
        if ($this->model::class === $originalModel) {
            return;
        }

        $reflection = new ReflectionClass($this->model);
        $attributes = $reflection->getAttributes(SynchronizeWith::class);
        $bindModels = [];

        foreach ($attributes as $attribute) {
            /** @var SynchronizeWith $attributeInstance */
            $attributeInstance = $attribute->newInstance();

            $bindModels[] = $attributeInstance->className;
        }

        if (!in_array($originalModel, $bindModels)) {
            throw new WrongLocalModelException();
        }
    }

    private function checkRelationType(string $localKey, string $relationType): void
    {
        if (get_class($this->model->$localKey()) !== $relationType) {
            throw new WrongRelationTypeException();
        }
    }

    private function loadForeignKeys(RelationshipData $relationshipData): array
    {
        $foreignKeys = $relationshipData->foreignKeys;
        if (empty($foreignKeys)) {
            $foreignKeys = [$relationshipData->foreignKey];
        }

        $foreignModel = $relationshipData->foreignClass;
        $databaseForeignKeys = [];

        foreach ($foreignKeys as $foreignKey) {
            /** @var Model $localCopyOfForeignModel */
            $localCopyOfForeignModel = $foreignModel::query()
                ->where($relationshipData->foreignRelatedFieldName, $foreignKey)
                ->first();

            if (!empty($localCopyOfForeignModel)) {
                $databaseForeignKeys[] = $localCopyOfForeignModel->getKey();
            }
        }

        return $databaseForeignKeys;
    }

    private function associateBelongsTo(BelongsTo $relationship, int $foreignKey): void
    {
        $relationship->associate($foreignKey);
    }

    private function attachManyToMany(BelongsToMany $relationship, array $foreignKeys): void
    {
        $relationship->sync($foreignKeys);
    }
}
