<?php

namespace Grixu\Synchronizer\Tests\Helpers;

trait MigrateProductsTrait
{
    protected function migrateProducts()
    {
        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_branches_table.stub';
        (new \CreateBranchesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operator_roles_table.stub';
        (new \CreateOperatorRolesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operators_table.stub';
        (new \CreateOperatorsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.stub';
        (new \CreateOperatorBranchPivotTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_brands_table.stub';
        (new \CreateBrandsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_product_types_table.stub';
        (new \CreateProductTypesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_categories_table.stub';
        (new \CreateCategoriesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_products_table.stub';
        (new \CreateProductsTable())->up();
    }
}
