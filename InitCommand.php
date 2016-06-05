<?php

namespace SenseException\PartialIndex;

use Faker\Generator;
use PDO;
use PDOStatement;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends Command
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var PDO
     */
    private $pdo;

    /**
     * @param string $name
     * @param PDO $pdo
     * @param Generator $faker
     */
    public function __construct($name, PDO $pdo, Generator $faker)
    {
        $this->faker = $faker;
        parent::__construct($name);
        $this->pdo = $pdo;
    }

    protected function configure()
    {
        $this->setDescription('Creates the database, tables, indexes and inserts values.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->pdo->beginTransaction();

            $output->writeln('Creating table: foo_partial');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS foo_partial (id serial, name varchar(255), payment_status varchar(10))');
            $output->writeln('Creating table: foo_normal');
            $this->pdo->exec('CREATE TABLE IF NOT EXISTS foo_normal (id serial, name varchar(255), payment_status varchar(10))');

            $statement1 = $this->pdo->prepare('INSERT INTO foo_partial (name, payment_status) VALUES (:name, :state)');
            $statement2 = $this->pdo->prepare('INSERT INTO foo_normal (name, payment_status) VALUES (:name, :state)');


            $output->writeln('Start inserting a lot of data rows into both tables with completed payment status');
            $this->insertValues(1000000, $statement1, $statement2, $output, 'complete');

            $output->writeln('Start inserting a few data rows into both tables with pending payment status');
            $this->insertValues(20, $statement1, $statement2, $output, 'pending');

            $output->writeln('Create normal and partial index');
            $this->pdo->exec('CREATE INDEX payment_status_normal ON foo_normal (payment_status)');
            $this->pdo->exec('CREATE INDEX payment_status_partial ON foo_partial (payment_status) WHERE payment_status != \'complete\'');

            $this->pdo->commit();
            $output->writeln('<info>Initialization done. You can start now with comparing both tables.</info>');
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }
    }

    /**
     * @param int $rowCount
     * @param PDOStatement $statement1
     * @param PDOStatement $statement2
     * @param OutputInterface $output
     * @param $state
     */
    private function insertValues($rowCount, PDOStatement $statement1, PDOStatement $statement2, OutputInterface $output, $state)
    {
        $progressBar = new ProgressBar($output);
        $progressBar->start($rowCount);

        for ($i = 0; $i < $rowCount; $i++) {
            $name = $this->faker->name;
            $statement1->execute([':name' => $name, ':state' => $state]);
            $statement2->execute([':name' => $name, ':state' => $state]);
            $progressBar->advance();
        }
        $output->writeln('');
    }
}