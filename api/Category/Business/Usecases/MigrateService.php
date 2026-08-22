<?php
namespace Category\Business\Usecases;

use Category\Data\CategoryMigrationRepositoryInterface;

class MigrateService
{
    private CategoryMigrationRepositoryInterface $categoryMigrationRepository;

    public function __construct(CategoryMigrationRepositoryInterface $categoryMigrationRepository)
    {
        $this->categoryMigrationRepository = $categoryMigrationRepository;
    }

    public function execute()
    {
        return $this->categoryMigrationRepository->migrate();
    }
}
