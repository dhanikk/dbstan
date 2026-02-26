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
        $this->info("Running DBStan Analysis...\n");
        $analyzer = new DBStanAnalyzer();
        $issues = $analyzer->analyze();

        if (empty($issues)) {
            $this->info("✅ No major DB design issues found.");
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

        foreach ($flatIssues as $issue) {
            $this->line($issue);
        }

        // Display total count of issues found
        $this->info("\nTotal issues found: " . count($flatIssues));
    }

}