<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\RelationshipDataTransferObject\RelationshipDataCollection;
use Grixu\SociusModels\Description\Models\ProductDescription;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Exceptions\WrongLocalModelException;
use Grixu\Synchronizer\Exceptions\WrongRelationTypeException;
use Grixu\Synchronizer\RelationshipSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RelationshipSynchronizerTest extends TestCase
{
    protected Model $localModel;
    protected Model $relatedModel;
    protected RelationshipSynchronizer $obj;
    protected RelationshipDataCollection $data;

    /** @test */
    public function it_sync_relationship_belongs_to()
    {
        $this->makeBelongsToCase();

        $this->obj->syncRelationship($this->data->current());

        $this->localModel->load('brand');
        $this->assertNotEmpty($this->localModel->brandId);
    }

    protected function makeBelongsToCase(): void
    {
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/product/2020_09_25_081701_create_brands_table.php';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/product/2020_09_25_081823_create_products_table.php';
        (new \CreateBrandsTable())->up();
        (new \CreateProductsTable())->up();

        $this->localModel = Product::factory()->create();
        $this->relatedModel = Brand::factory()->create();
        $this->obj = new RelationshipSynchronizer($this->localModel);
        $this->data = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Product::class,
                    'foreignClass' => Brand::class,
                    'localRelationshipName' => 'brand',
                    'foreignRelatedFieldName' => 'xlId',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xlId,
                    'foreignKey' => (int) $this->relatedModel->xlId,
                ]
            ]
        );

        $this->assertEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_sync_relationship_many_to_many()
    {
        $this->makeManyToManyCase();

        $this->obj->syncRelationship($this->data->current());

        $this->localModel->load('branches');
        $this->assertNotEmpty($this->localModel->branches);
    }

    protected function makeManyToManyCase(): void
    {
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/operator/2020_09_30_092119_create_operators_table.php';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/operator/2020_09_30_135749_create_branches_table.php';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/operator/2020_10_01_064502_create_operator_branch_pivot_table.php';
        (new \CreateOperatorsTable())->up();
        (new \CreateBranchesTable())->up();
        (new \CreateOperatorBranchPivotTable())->up();

        $this->localModel = Operator::factory()->create();
        $this->relatedModel = Branch::factory()->create();
        $this->obj = new RelationshipSynchronizer($this->localModel);
        $this->data = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Operator::class,
                    'foreignClass' => Branch::class,
                    'localRelationshipName' => 'branches',
                    'foreignRelatedFieldName' => 'xlId',
                    'type' => BelongsToMany::class,
                    'localKey' => (int) $this->localModel->xlId,
                    'foreignKeys' => [$this->relatedModel->xlId],
                ]
            ]
        );

        $this->assertEmpty($this->localModel->descriptions);
    }

    /** @test */
    public function it_detects_wrong_model()
    {
        $this->makeDisruptedCaseOne();

        try {
            $this->obj->syncRelationship($this->data->current());
            $this->assertTrue(false);
        } catch (WrongLocalModelException $exception) {
            $this->assertTrue(true);
        }
    }

    protected function makeDisruptedCaseOne()
    {
        $this->makeBelongsToCase();
        $this->data = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Brand::class,
                    'foreignClass' => Brand::class,
                    'localRelationshipName' => 'brand',
                    'foreignRelatedFieldName' => 'xlId',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xlId,
                    'foreignKey' => (int) $this->relatedModel->xlId,
                ]
            ]
        );
    }

    /** @test */
    public function it_detect_wrong_relation_type()
    {
        $this->makeDisruptedCaseTwo();

        try {
            $this->obj->syncRelationship($this->data->current());
            $this->assertTrue(false);
        } catch (WrongRelationTypeException $exception) {
            $this->assertTrue(true);
        }
    }

    protected function makeDisruptedCaseTwo()
    {
        $this->makeManyToManyCase();
        $this->data = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Operator::class,
                    'foreignClass' => Branch::class,
                    'localRelationshipName' => 'branches',
                    'foreignRelatedFieldName' => 'xlId',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xlId,
                    'foreignKeys' => [$this->relatedModel->xlId],
                ]
            ]
        );
    }

    /** @test */
    public function it_allows_to_sync_collection()
    {
        $this->makeBelongsToCase();

        $this->obj->sync($this->data);

        $this->localModel->load('brand');
        $this->assertNotEmpty($this->localModel->brandId);
    }

    /** @test */
    public function do_nothing_on_empty_foreign_keys()
    {
        $this->makeEmptyForeignKeysCase();

        $this->obj->syncRelationship($this->data->current());

        $this->localModel->load('branches');
        $this->assertEmpty($this->localModel->branches);
    }

    protected function makeEmptyForeignKeysCase()
    {
        $this->makeManyToManyCase();
        $this->data = RelationshipDataCollection::create(
            [
                [
                    'localClass' => Operator::class,
                    'foreignClass' => Branch::class,
                    'localRelationshipName' => 'branches',
                    'foreignRelatedFieldName' => 'xlId',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xlId,
                    'foreignKeys' => [],
                ]
            ]
        );
    }
}
