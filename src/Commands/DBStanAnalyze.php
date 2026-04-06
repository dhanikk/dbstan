<?php

namespace Itpathsolutions\DBStan\Commands;

use Illuminate\Console\Command;

use Itpathsolutions\DBStan\DBStanAnalyzer;

class DBStanAnalyze extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dbstan:analyze';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze database structure for design issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $analyzer = new DBStanAnalyzer();
        $info = $analyzer->getDatabaseInfo();

        $this->line('');
        $this->info('🔍 DBStan Analysis Context');
        $this->line(str_repeat('-', 40));

        $this->line("Driver      : {$info['driver']}");
        $this->line("Database    : {$info['database']}");
        $this->line("Host        : {$info['host']}");
        $this->line("Port        : {$info['port']}");
        $this->line("Tables      : {$info['tables']}");
        $this->line("Environment : {$info['environment']}");

        $this->line(str_repeat('-', 40));
        $this->line('');

        $preflightError = $analyzer->getPreflightError();

        if ($preflightError !== null) {
            $this->newLine();
            $this->error($preflightError);
            $this->line('DBStan analysis was skipped until the above issue is fixed.');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('🔍 Starting DBStan database structure analysis...');

        $steps = 12;
        $bar = $this->output->createProgressBar($steps);
        $bar->setFormat('verbose');

        $bar->start();
        $bar->setMessage('Collecting table metadata...', 'status');

        $bar->advance(1); sleep(1); $bar->setMessage('Analyzing table structures...', 'status');
        $bar->advance(1); sleep(1); $bar->setMessage('Checking column types & nullability...', 'status');
        $bar->advance(1); sleep(1); $bar->setMessage('Collecting indexes & constraints...', 'status');

        // Do the actual heavy analysis
        $issues = $analyzer->analyze();

        for ($i = 4; $i <= 10; $i++) {
            $bar->advance(1);
            usleep(400_000);
            $bar->setMessage("Processing issue category $i/10...", 'status');
        }

        $bar->advance(2);
        $bar->setMessage('Finalizing report...', 'status');
        usleep(600_000);

        $bar->finish();
        $this->newLine(2);

        if (empty($issues)) {
            $this->info(' No major database design issues found!');
            return;
        }

        $flatIssues = [];
        foreach ($issues as $category) {
            foreach ($category as $subCategory) {
                foreach ($subCategory as $issue) {
                    $flatIssues[] = $issue;
                }
            }
        }

        $count = count($flatIssues);
        $this->error("  Found $count design issue" . ($count === 1 ? '' : 's') . "!");
        $this->newLine();

        foreach ($flatIssues as $i => $issue) {
            $this->line(sprintf("  %3d. %s", $i + 1, $issue));
        }

        $this->newLine();
        $this->info("Analysis completed in {$bar->getProgressPercent()}% of estimated time.");
    }
}