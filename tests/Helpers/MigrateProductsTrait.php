<?php

namespace Grixu\Synchronizer\Tests\Helpers;

trait MigrateProductsTrait
{
    protected function migrateProducts()
    {
        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/operator/2020_09_30_135749_create_branches_table.php';
        (new \CreateBranchesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/operator/2020_09_30_082556_create_operator_roles_table.php';
        (new \CreateOperatorRolesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/operator/2020_09_30_092119_create_operators_table.php';
        (new \CreateOperatorsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/operator/2020_10_01_064502_create_operator_branch_pivot_table.php';
        (new \CreateOperatorBranchPivotTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/product/2020_09_25_081701_create_brands_table.php';
        (new \CreateBrandsTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/product/2020_09_25_081724_create_product_types_table.php';
        (new \CreateProductTypesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/product/2020_09_25_081713_create_categories_table.php';
        (new \CreateCategoriesTable())->up();

        require_once __DIR__.'/../../vendor/grixu/socius-models/migrations/product/2020_09_25_081823_create_products_table.php';
        (new \CreateProductsTable())->up();
    }
}
