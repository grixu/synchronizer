<?php

namespace Grixu\Synchronizer\Tests\Helpers;

trait MigrateProductsTrait
{
    protected function migrateProducts()
    {
        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_branches_table.php.stub';
        (new \CreateBranchesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operator_roles_table.php.stub';
        (new \CreateOperatorRolesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operators_table.php.stub';
        (new \CreateOperatorsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_operator_branch_pivot_table.php.stub';
        (new \CreateOperatorBranchPivotTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_brands_table.php.stub';
        (new \CreateBrandsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_product_types_table.php.stub';
        (new \CreateProductTypesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_categories_table.php.stub';
        (new \CreateCategoriesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/create_products_table.php.stub';
        (new \CreateProductsTable())->up();
    }
}
