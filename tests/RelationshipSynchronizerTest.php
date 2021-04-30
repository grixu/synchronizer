<?php

namespace Grixu\Synchronizer\Tests;

use Grixu\RelationshipDataTransferObject\RelationshipDataCollection;
use Grixu\SociusModels\Operator\Models\Branch;
use Grixu\SociusModels\Operator\Models\Operator;
use Grixu\SociusModels\Product\Models\Brand;
use Grixu\SociusModels\Product\Models\Product;
use Grixu\Synchronizer\Exceptions\WrongLocalModelException;
use Grixu\Synchronizer\Exceptions\WrongRelationTypeException;
use Grixu\Synchronizer\RelationshipSynchronizer;
use Grixu\Synchronizer\Tests\Helpers\FakeExtendedModel;
use Grixu\Synchronizer\Tests\Helpers\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

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

        $this->obj->syncRelationship($this->data->first());

        $this->localModel->load('brand');
        $this->assertNotEmpty($this->localModel->brand_id);
    }

    protected function makeBelongsToCase(): void
    {
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/create_products_table.stub';
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
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xl_id,
                    'foreignKey' => (int) $this->relatedModel->xl_id,
                ]
            ]
        );

        $this->assertEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_sync_relationship_many_to_many()
    {
        $this->makeManyToManyCase();

        $this->obj->syncRelationship($this->data->first());

        $this->localModel->load('branches');
        $this->assertNotEmpty($this->localModel->branches);
    }

    protected function makeManyToManyCase(): void
    {
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        require_once __DIR__.'/../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
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
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsToMany::class,
                    'localKey' => (int) $this->localModel->xl_id,
                    'foreignKeys' => [$this->relatedModel->xl_id],
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
            $this->obj->syncRelationship($this->data->first());
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
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xl_id,
                    'foreignKey' => (int) $this->relatedModel->xl_id,
                ]
            ]
        );
    }

    /** @test */
    public function it_detect_wrong_relation_type()
    {
        $this->makeDisruptedCaseTwo();

        try {
            $this->obj->syncRelationship($this->data->first());
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
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xl_id,
                    'foreignKeys' => [$this->relatedModel->xl_id],
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
        $this->assertNotEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function do_nothing_on_empty_foreign_keys()
    {
        $this->makeEmptyForeignKeysCase();

        $this->obj->syncRelationship($this->data->first());

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
                    'foreignRelatedFieldName' => 'xl_id',
                    'type' => BelongsTo::class,
                    'localKey' => (int) $this->localModel->xl_id,
                    'foreignKeys' => [],
                ]
            ]
        );
    }

    /** @test */
    public function it_accept_error_handler_as_argument()
    {
        Http::fake();

        $this->makeDisruptedCaseOne();
        $this->obj->sync($this->data, function($e) {
            Http::get('http://testable.dev');
        });

        Http::assertSent(function (Request $request) {
            return $request->url() == 'http://testable.dev';
        });
    }

    /** @test */
    public function it_reflect_given_class_to_check_is_have_attribute()
    {
        $this->makeBelongsToCase();
        $fake = new FakeExtendedModel();
        $fake->fill($this->localModel->toArray());
        $fake->save();
        $this->localModel = $fake;
        $this->obj = new RelationshipSynchronizer($this->localModel);

        $this->assertEmpty($this->localModel->brand_id);

        $this->obj->syncRelationship($this->data->first());

        $this->localModel->load('brand');
        $this->assertNotEmpty($this->localModel->brand_id);
    }

    /** @test */
    public function it_accept_when_local_model_is_extended_original_one()
    {
        $this->makeBelongsToCase();
        $extendedClass = new class extends Product {};
        $this->localModel = $extendedClass;
        $this->obj = new RelationshipSynchronizer($extendedClass);

        $this->assertEmpty($this->localModel->brand_id);
        try {
            $this->obj->syncRelationship($this->data->first());
            $this->assertTrue(true);
        } catch (\Illuminate\Database\QueryException) {
            $this->assertTrue(true);
        } catch (\Exception) {
            $this->assertFalse(true);
        }
    }

    /** @test */
    public function it_fails_when_model_is_not_extend_or_having_an_attribute()
    {
        $this->makeBelongsToCase();
        $extendedClass = new class extends Model{};
        $this->localModel = $extendedClass;
        $this->obj = new RelationshipSynchronizer($extendedClass);

        try {
            $this->obj->syncRelationship($this->data->first());
            $this->assertTrue(false);
        } catch (WrongLocalModelException) {
            $this->assertTrue(true);
        } catch (\Exception) {
            $this->assertFalse(false);
        }
    }
}
