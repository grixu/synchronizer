<?php

namespace Grixu\Synchronizer;

use Grixu\RelationshipDataTransferObject\RelationshipData;
use Grixu\RelationshipDataTransferObject\RelationshipDataCollection;
use Grixu\Synchronizer\Exceptions\WrongLocalModelException;
use Grixu\Synchronizer\Exceptions\WrongRelationTypeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RelationshipSynchronizer
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function sync(RelationshipDataCollection $relationships): void
    {
        foreach ($relationships as $relationship) {
            $this->syncRelationship($relationship);
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

    private function checkModelClass(string $localModelClass): void
    {
        if (get_class($this->model) !== $localModelClass)
            throw new WrongLocalModelException();
    }

    private function checkRelationType(string $localKey, string $relationType): void
    {
        if (get_class($this->model->$localKey()) !== $relationType)
            throw new WrongRelationTypeException();
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
