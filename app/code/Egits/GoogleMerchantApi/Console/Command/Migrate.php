<?php
/**
 * Eglobe IT Solutions (P)Ltd.
 *
 * @category    Egits
 * @package     Egits_GoogleMerchantApi
 * @copyright   Copyright (c) 2019 Eglobe IT Solutions. (http://www.eglobeits.com/)
 * @author      Eglobe Magento Team <info@eglobeits.com>
 */
declare(strict_types=1);

namespace Egits\GoogleMerchantApi\Console\Command;

use Egits\GoogleMerchantApi\Api\GoogleDataMigrationInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Migrate for Migration
 * Class Migrate
 */
class Migrate extends Command
{
    /**
     * @var GoogleDataMigrationInterface
     */
    private GoogleDataMigrationInterface $googleOldData;

    /**
     * Migrate constructor.
     * @param GoogleDataMigrationInterface $googleOldData
     * @param string|null $name
     */
    public function __construct(
        GoogleDataMigrationInterface $googleOldData,
        ?string                       $name = null
    ) {
        parent::__construct($name);
        $this->googleOldData = $googleOldData;
    }

    /**
     * @inheritdoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        try {
            $output->writeln("Validating migration process.");
            if (!$this->googleOldData->checkValidForMigration()) {
                $output->writeln("Validation failed and skipping migration process.
                        Please run the migration manually using SQL.");
                return;
            }
            $output->writeln("Validating successful, proceeding with migration.");
            foreach ($this->googleOldData->getAllMigrationTables() as $newTable => $oldTable) {
                $output->writeln("Migrating table $oldTable => $newTable");
                $this->googleOldData->migrateTableData($newTable, $oldTable);
                $output->writeln("Migration successful for $oldTable => $newTable");
            }
        } catch (Exception $exception) {
            $output->writeln("Migration failed due to error : " . $exception->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this->setName("eglobe:gmc:migrate");
        $this->setDescription("Migrate the Google Content API module data to new version database tables");
        parent::configure();
    }
}
